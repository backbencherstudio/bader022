<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = Staff::orderBy('id', 'desc');

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' .$request->name . '%');
        }

        $staffs = $query->get();

        return response()->json([
            'success' => true,
            'data' => $staffs
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'role' => 'required|in:staff,admin',
            'service_id' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('staffs'), $imageName);

            $imagePath = 'staffs/' . $imageName;
        }

        $staff = Staff::create([
            'name' => $request->name,
            'role' => $request->role,
            'service_id' => $request->service_id,
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
        $staff = Staff::find($id);

        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Staff not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $staff
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $staff = Staff::find($id);

        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Staff not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'role' => 'sometimes|required|string|in:staff,admin',
            'service_id' => 'sometimes|required|string',
            'image'      => 'sometimes|required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
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
            'status'
        ]));

        $staff->save();

        return response()->json([
            'success' => true,
            'message' => 'Staff updated successfully',
            'data' => $staff
        ], 200);
    }

    public function destroy($id)
    {
        $staff = Staff::find($id);

        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Staff not found'
            ], 404);
        }

        if ($staff->image && file_exists(public_path($staff->image))) {
            unlink(public_path($staff->image));
        }

        $staff->delete();

        return response()->json([
            'success' => true,
            'message' => 'Staff deleted successfully'
        ], 200);
    }
}
