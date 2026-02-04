<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use Illuminate\Support\Facades\Validator;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $plans = Plan::all();

        return response()->json([
            'success' => true,
            'data' => $plans
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|in:Basic,Premium,Enterprise',
            'title' => 'nullable|string',
            'price' => 'required|numeric',
            'currency' => 'nullable|string',
            'package' => 'required|in:Free,Monthly,Annual',
            'day' => 'required|integer',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'status' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $plan = Plan::create([
            'name' => $request->name,
            'title' => $request->title,
            'price' => $request->price,
            'currency' => $request->currency ?? 'SAR',
            'package' => $request->package,
            'day' => $request->day,
            'features' => $request->features,
            'status' => $request->status ?? 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plan created successfully',
            'data' => $plan
        ], 201);
    }

    public function show($id)
    {
        $plan = Plan::find($id);

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plan not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $plan
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $plan = Plan::find($id);

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plan not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|in:Basic,Premium,Enterprise',
            'title' => 'nullable|string',
            'price' => 'sometimes|required|numeric',
            'currency' => 'nullable|string',
            'package' => 'sometimes|required|in:Free,Monthly,Annual',
            'day' => 'sometimes|required|integer',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'status' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $plan->fill($request->only([
            'name',
            'title',
            'price',
            'currency',
            'package',
            'day',
            'features',
            'status'
        ]));

        $plan->save();

        return response()->json([
            'success' => true,
            'message' => 'Plan updated successfully',
            'data' => $plan
        ], 200);
    }

    public function destroy($id)
    {
        $plan = Plan::find($id);

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plan not found'
            ], 404);
        }

        $plan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Plan deleted successfully'
        ], 200);
    }
}
