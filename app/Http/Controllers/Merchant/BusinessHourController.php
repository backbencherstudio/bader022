<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BusinessHour;
use Illuminate\Support\Facades\Validator;


class BusinessHourController extends Controller
{
    public function index()
    {
        $businessHours = BusinessHour::all();

        return response()->json([
            'success' => true,
            'date' => $businessHours
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'day' => 'required|string',
            'open_time' => 'nullable|date_format:H:i',
            'close_time' => 'nullable|date_format:H:i',
            'is_closed' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $businessHours = BusinessHour::create([
            'user_id' => $request->user_id,
            'day' => $request->day,
            'open_time' => $request->open_time,
            'close_time' => $request->close_time,
            'is_closed' => $request->is_closed ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Business hour created successfully',
            'data' => $businessHours
        ], 201);
    }

    public function show($id)
    {
        $businessHour = BusinessHour::find($id);

        if (!$businessHour) {
            return response()->json([
                'success' => false,
                'message' => 'Business hour not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $businessHour
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $businessHour = BusinessHour::find($id);

        if (!$businessHour) {
            return response()->json([
                'success' => false,
                'message' => 'Business hour not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'day' => 'sometimes|required|string',
            'open_time' => 'nullable|date_format:H:i',
            'close_time' => 'nullable|date_format:H:i',
            'is_closed' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $businessHour->update($request->only([
            'day',
            'open_time',
            'close_time',
            'is_closed'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Business hour updated successfully',
            'data' => $businessHour
        ], 200);
    }

    public function destroy($id)
    {
        $businessHour = BusinessHour::find($id);

        if (!$businessHour) {
            return response()->json([
                'success' => false,
                'message' => 'Business hour not found'
            ], 404);
        }

        $businessHour->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Business hour deleted successfully'
        ], 200);
    }
}
