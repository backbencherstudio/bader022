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

    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string|max:255',
    //         'role' => 'required|in:staff,admin',
    //         'service_id' => 'required|array',
    //         'service_id.*' => 'exists:services,id',
    //         'image' => 'nullable',
    //         'status' => 'nullable|boolean',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'errors' => $validator->errors(),
    //         ], 422);
    //     }

    //     // check services belong to user
    //     $validServices = auth()->user()
    //         ->services()
    //         ->whereIn('id', $request->service_id)
    //         ->pluck('id')
    //         ->toArray();

    //     if (count($validServices) !== count($request->service_id)) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Some services do not belong to you',
    //         ], 400);
    //     }

    //     // upload image
    //     $imagePath = null;
    //     if ($request->hasFile('image')) {
    //         $image = $request->file('image');
    //         $imageName = time().'_'.$image->getClientOriginalName();
    //         $image->move(public_path('staffs'), $imageName);
    //         $imagePath = 'staffs/'.$imageName;
    //     }

    //     // create staff
    //     $staff = Staff::create([
    //         'user_id' => auth()->id(),
    //         'name' => $request->name,
    //         'role' => $request->role,
    //         'image' => $imagePath,
    //         'status' => $request->status ?? 1,
    //     ]);

    //     // attach multiple services
    //     $staff->services()->attach($request->service_id);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Staff created successfully',
    //         'data' => $staff->load('services'),
    //     ], 201);
    // }

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

    public function update(Request $request, $id)
    {
        $staff = Staff::where('id', $id)->where('user_id', auth()->id())->first();

        if (! $staff) {
            return response()->json([
                'success' => false,
                'message' => 'Staff not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'role' => 'sometimes|required|string|in:staff,admin',
            'service_id' => 'sometimes|required|string',
            'image' => 'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->has('service_id')) {
            $userService = auth()->user()->services()->where('id', $request->service_id)->first();

            if (! $userService) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected service does not belong to the authenticated user.',
                ], 404);
            }
        }

        if ($request->hasFile('image')) {
            if ($staff->image && file_exists(public_path($staff->image))) {
                unlink(public_path($staff->image));
            }

            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('staffs'), $imageName);

            $staff->image = 'staffs/' . $imageName;
        }

        $staff->fill($request->only([
            'name',
            'role',
            'service_id',
            'status',
        ]));

        $staff->save();

        return response()->json([
            'success' => true,
            'message' => 'Staff updated successfully',
            'data' => $staff,
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
}
