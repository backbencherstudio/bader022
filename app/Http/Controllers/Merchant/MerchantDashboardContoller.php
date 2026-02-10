<?php

namespace App\Http\Controllers\Merchant;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MerchantPayment;
use App\Models\Booking;


class MerchantDashboardContoller extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->type != 2) {
            return response()->json([
                'message' => 'Unauthorized access',
            ], 403);
        }

        $merchantId = $user->id;

        return response()->json([

            'revenue' => MerchantPayment::where('merchant_id', $merchantId)
                ->where('status', 'successfull')
                ->sum('amount'),

            'total_bookings' => Booking::where('merchant_id', $merchantId)
                ->count(),

            'appointments' => Appointment::where('merchant_id', $merchantId)
                ->where('status', 'confirmed')
                ->count(),

            'total_customers' => Booking::where('merchant_id', $merchantId)
                ->distinct('customer_id')
                ->count('customer_id'),
        ]);
    }
}
