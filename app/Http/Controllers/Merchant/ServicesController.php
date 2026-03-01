<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServicesController extends Controller
{
    public function index(Request $request)
    {

        $query = Service::where('user_id', auth()->id());

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
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $imagePath = null;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('services/'), $imageName);

            $imagePath = 'services/' . $imageName;
        }

        $service = Service::create([
            'user_id' => auth()->id(),
            'service_name' => $request->service_name,
            'duration' => $request->duration,
            'price' => $request->price,
            'description' => $request->description,
            'image' => $imagePath,
            'status' => $request->status ?? 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service created successfully',
            'data' => $service
        ], 201);
    }

    public function show($id)
    {
        $service = Service::where('id', $id)->where('user_id', auth()->id())->first();

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
        $service = Service::where('id', $id)->where('user_id', auth()->id())->first();

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
            'image' => 'sometimes|required|image|mimes:jpg,jpeg,png,webp|max:2048',
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
        $service = Service::where('id', $id)->where('user_id', auth()->id())->first();

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
                'image' => $service->image,
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
