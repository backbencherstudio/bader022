<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Booking;
use App\Models\MerchantPayment;
use App\Models\Staff;
use App\Models\Service;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'staff_id' => 'required|exists:staffs,id',
            'service_id' => 'required|exists:services,id',
            'customer_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:15',
            'date_time' => 'required|date_format:Y-m-d H:i:s',
            'special_note' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }


        $staff = Staff::where('id', $request->staff_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'This staff is not associated with your account.',
            ], 403);
        }

        $service = Service::where('id', $request->service_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'This service is not associated with your account.',
            ], 403);
        }

        $data = [
            'user_id' => $user->id,
            'staff_id' => $request->staff_id,
            'service_id' => $request->service_id,
            'customer_name' => $request->customer_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'date_time' => $request->date_time,
            'status' => 'pending',
            'special_note' => $request->special_note,
            'booking_by' => $user->type == 2 ? 'merchant' : 'user',
        ];

        DB::beginTransaction();
        try {
            $booking = Booking::create($data);

            if ($request->has('payment_method')) {
                MerchantPayment::create([
                    'booking_id' => $booking->id,
                    'user_id' => $user->id,
                    'payment_method' => $request->payment_method,
                    'amount' => $request->amount,
                    'payment_status' => 'pending',
                    'transaction_id' => $request->transaction_id ?? null,
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => $booking,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }
}
