<?php

namespace App\Http\Controllers\Merchant;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\{Branch, Service, User};

class ServicesController extends Controller
{
    public function index(Request $request)
    {
        $mainBranch = Branch::where('user_id', auth()->id())
            ->where('is_main', 1)
            ->first();

        if (!$mainBranch) {
            return response()->json([
                'success' => false,
                'message' => 'Main branch not found'
            ], 404);
        }

        $query = Service::where('user_id', auth()->id())
            ->where('branch_id', $mainBranch->id);

        if ($request->filled('service_name')) {
            $query->where('service_name', 'like', '%' . $request->service_name . '%');
        }

        $services = $query->get();

        return response()->json([
            'success' => true,
            'data' => $services
        ]);
    }


    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'service_name' => 'required|string|max:255',
            'duration' => 'required|string',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }


        $selectedBranchId = $request->header('X-Branch-Id') ?? $request->branch_id;

        if (!$selectedBranchId) {
            return response()->json([
                'success' => false,
                'message' => 'No branch selected for this device.'
            ], 400);
        }


        $branchExists = \App\Models\Branch::where('user_id', auth()->id())
            ->where('id', $selectedBranchId)
            ->exists();

        if (!$branchExists) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid branch selection.'
            ], 403);
        }


        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
            $destination = public_path('services');

            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }

            $image->move($destination, $imageName);
            $imagePath = 'services/' . $imageName;
        }


        $service = \App\Models\Service::create([
            'user_id' => auth()->id(),
            'branch_id' => $selectedBranchId,
            'service_name' => $request->service_name,
            'duration' => $request->duration,
            'price' => $request->price,
            'description' => $request->description,
            'image' => $imagePath,
            'status' => $request->status ?? 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service created successfully for the selected branch.',
            'data' => $service
        ], 201);
    }


    public function show($id)
    {
        $mainBranch = Branch::where('user_id', auth()->id())
            ->where('is_main', 1)
            ->first();

        if (!$mainBranch) {
            return response()->json([
                'success' => false,
                'message' => 'Main branch not found'
            ], 404);
        }
        $service = Service::where('id', $id)->where('user_id', auth()->id())
            ->where('branch_id', $mainBranch->id)->first();

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $service
        ], 200);
    }


    public function update(Request $request, $id)
    {
        $mainBranch = Branch::where('user_id', auth()->id())
            ->where('is_main', 1)
            ->first();

        if (!$mainBranch) {
            return response()->json([
                'success' => false,
                'message' => 'Main branch not found'
            ], 404);
        }

        $service = Service::where('id', $id)->where('user_id', auth()->id())
            ->where('branch_id', $mainBranch->id)->first();

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found'
            ], 404);
        }

        $validator = validator::make($request->all(), [
            'service_name' => 'sometimes|required|string|max:255',
            'duration' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->hasfile('image')) {
            if ($service->image && file_exists(public_path($service->image))) {
                unlink(public_path($service->image));
            }

            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('services'), $imageName);

            $service->image = 'services/' . $imageName;
        }

        $service->fill($request->only([
            'service_name',
            'duration',
            'price',
            'description',
            'status'
        ]));

        $service->save();

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully',
            'data' => $service
        ], 200);
    }
    

    public function destroy($id)
    {
        $mainBranch = Branch::where('user_id', auth()->id())
            ->where('is_main', 1)
            ->first();

        if (!$mainBranch) {
            return response()->json([
                'success' => false,
                'message' => 'Main branch not found'
            ], 404);
        }
        $service = Service::where('id', $id)->where('user_id', auth()->id())
            ->where('branch_id', $mainBranch->id)->first();

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found'
            ], 404);
        }

        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service deleted successfully'
        ], 200);
    }

    public function userindex(Request $request)
    {

        $query = Service::query();

        if ($request->filled('service_name')) {
            $query->where('service_name', 'like', '%' . $request->service_name . '%');
        }

        $services = $query->get();

        $mapped = $services->map(function ($service) {
            return [
                'id' => $service->id,
                'image' => $service->image ?? null,
                'duration' => $service->duration,
                'price' => $service->price,
                'name' => $service->service_name,
                'description' => $service->description,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $mapped
        ]);
    }
}
