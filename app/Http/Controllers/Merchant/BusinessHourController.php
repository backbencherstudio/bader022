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
        $user_id = auth()->id();
        $businessHours = BusinessHour::where('user_id', $user_id)->get();

        return response()->json([
            'success' => true,
            'data' => $businessHours
        ], 200);
    }

    public function store(Request $request)
    {
        $user_id = auth()->id();
        $validator = Validator::make($request->all(), [
            'day' => 'required|string',
            'open_time' => 'nullable|date_format:H:i',
            'close_time' => 'nullable|date_format:H:i|after_or_equal:open_time',
            'is_closed' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $existingBusinessHour = BusinessHour::where('user_id', $user_id)
            ->where('day', $request->day)
            ->exists();

        if ($existingBusinessHour) {
            return response()->json([
                'success' => false,
                'message' => 'You already have business hours set for this day.'
            ], 400);
        }

        $businessHours = BusinessHour::create([
            'user_id' => $user_id,
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
        $user_id = auth()->id();

        $businessHour = BusinessHour::where('id', $id)
            ->where('user_id', $user_id)->first();

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

        $businessHour = BusinessHour::where('id', $id)
        ->where('user_id', auth()->id())->first();

        if (!$businessHour) {
            return response()->json([
                'success' => false,
                'message' => 'Business hour not found'
            ], 404);
        }

        if ($request->has('day') && $request->day != $businessHour->day) {
            $existingBusinessHour = BusinessHour::where('user_id', auth()->id())
                ->where('day', $request->day)
                ->exists();

            if ($existingBusinessHour) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have business hours set for this day.'
                ], 400);
            }
        }

        $validator = Validator::make($request->all(), [
            'day' => 'sometimes|required|string',
            'open_time' => 'nullable|date_format:H:i',
            'close_time' => 'nullable|date_format:H:i|after_or_equal:open_time',
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
        $user_id = auth()->id();
        $businessHour = BusinessHour::where('id',$id)
        ->where('user_id', $user_id)->first();

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
