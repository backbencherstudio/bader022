<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
    public function index(Request $request)
{
    $query = Staff::where('user_id', auth()->id())->orderBy('id', 'desc');

    if ($request->filled('name')) {
        $query->where('name', 'like', '%' . $request->name . '%');
    }

    $staffs = $query->get();

    $staffs->map(function ($staff) {
        if (is_array($staff->service_id) && !empty($staff->service_id)) {
            $staff->service_names = \App\Models\Service::whereIn('id', $staff->service_id)
                ->pluck('service_name')
                ->toArray();
        } else {
            $staff->service_names = [];
        }
        return $staff;
    });

    return response()->json([
        'success' => true,
        'data' => $staffs,
    ], 200);
}

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'role' => 'required|in:staff,admin',
            'service_id' => 'required|array',
            'service_id.*' => 'exists:services,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $userServices = auth()->user()
            ->services()
            ->whereIn('id', $request->service_id)
            ->pluck('id')
            ->toArray();

        if (count($userServices) !== count($request->service_id)) {
            return response()->json([
                'success' => false,
                'message' => 'One or more selected services do not belong to the authenticated user.'
            ], 400);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('staffs'), $imageName);

            $imagePath = 'staffs/' . $imageName;
        }

        $staff = Staff::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'service_id' => $request->service_id,
            'role' => $request->role,
            'image' => $imagePath,
            'status' => $request->status ?? 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Staff created successfully',
            'data' => $staff
        ], 201);
    }


    public function show($id)
    {
        $staff = Staff::where('id', $id)->where('user_id', auth()->id())->with('service')->first();

        if (! $staff) {
            return response()->json([
                'success' => false,
                'message' => 'Staff not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $staff,
        ], 200);
    }

    // public function update(Request $request, $id)
    // {
    //     $staff = Staff::where('id', $id)->where('user_id', auth()->id())->first();

    //     if (! $staff) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Staff not found',
    //         ], 404);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'name' => 'sometimes|required|string|max:255',
    //         'role' => 'sometimes|required|string|in:staff,admin',
    //         'service_id' => 'sometimes|required|string',
    //         'image' => 'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    //         'status' => 'nullable|boolean',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'errors' => $validator->errors(),
    //         ], 422);
    //     }

    //     if ($request->has('service_id')) {
    //         $userService = auth()->user()->services()->where('id', $request->service_id)->first();

    //         if (! $userService) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'The selected service does not belong to the authenticated user.',
    //             ], 404);
    //         }
    //     }

    //     if ($request->hasFile('image')) {
    //         if ($staff->image && file_exists(public_path($staff->image))) {
    //             unlink(public_path($staff->image));
    //         }

    //         $image = $request->file('image');
    //         $imageName = time() . '_' . $image->getClientOriginalName();
    //         $image->move(public_path('staffs'), $imageName);

    //         $staff->image = 'staffs/' . $imageName;
    //     }

    //     $staff->fill($request->only([
    //         'name',
    //         'role',
    //         'service_id',
    //         'status',
    //     ]));

    //     $staff->save();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Staff updated successfully',
    //         'data' => $staff,
    //     ], 200);
    // }

    public function update(Request $request, $id)
    {

    $staff = Staff::where('user_id', auth()->id())->find($id);

    if (!$staff) {
        return response()->json([
            'success' => false,
            'message' => 'Staff not found or unauthorized.'
        ], 404);
    }


    $validator = Validator::make($request->all(), [
        'name' => 'sometimes|required|string|max:255',
        'role' => 'sometimes|required|in:staff,admin',
        'service_id' => 'sometimes|required|array',
        'service_id.*' => 'exists:services,id',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'status' => 'nullable|boolean',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }


    if ($request->has('service_id')) {
        $userServices = auth()->user()
            ->services()
            ->whereIn('id', $request->service_id)
            ->pluck('id')
            ->toArray();

        if (count($userServices) !== count($request->service_id)) {
            return response()->json([
                'success' => false,
                'message' => 'One or more selected services do not belong to you.'
            ], 400);
        }
    }


    if ($request->hasFile('image')) {

        if ($staff->image && file_exists(public_path($staff->image))) {
            @unlink(public_path($staff->image));
        }

        $image = $request->file('image');
        $imageName = time() . '_' . $image->getClientOriginalName();
        $image->move(public_path('staffs'), $imageName);
        $staff->image = 'staffs/' . $imageName;
    }


    $staff->update([
        'name' => $request->name ?? $staff->name,
        'role' => $request->role ?? $staff->role,
        'service_id' => $request->service_id ?? $staff->service_id,
        'status' => $request->status ?? $staff->status,
        'image' => $staff->image,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Staff updated successfully',
        'data' => $staff
    ], 200);
}

    public function destroy($id)
    {
        $staff = Staff::where('id', $id)->where('user_id', auth()->id())->first();

        if (! $staff) {
            return response()->json([
                'success' => false,
                'message' => 'Staff not found',
            ], 404);
        }

        if ($staff->image && file_exists(public_path($staff->image))) {
            unlink(public_path($staff->image));
        }

        $staff->delete();

        return response()->json([
            'success' => true,
            'message' => 'Staff deleted successfully',
        ], 200);
    }


    // public function staffIndex($website_domain)
    // {
    //     $staffs = Staff::with('user') // User data shaho load korbe
    //         ->whereHas('user', function ($query) use ($website_domain) {
    //             $query->where('website_domain', $website_domain);
    //         })
    //         ->get();

    //     return response()->json([
    //         'success' => true,
    //         'data' => $staffs,
    //     ], 200);
    // }

    public function staffIndex($website_domain)
{
    $staffs = Staff::whereHas('user', function ($query) use ($website_domain) {
            $query->where('website_domain', $website_domain);
        })
        ->get();

    // Map kore shudhu proyojoniyo data nawa
    $formattedStaffs = $staffs->map(function ($staff) {
        return [
            'id'   => $staff->id,
            'name' => $staff->name, // Staff table-e jodi 'name' thake
            // 'user_name' => $staff->user->name ?? null, // Jodi user table theke name nite chan
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $formattedStaffs,
    ], 200);
}
}
