<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use Carbon\Carbon;

class UserDashboardController extends Controller
{
    public function Upcoming(Request $request)
    {
        $userId = $request->user()->id;

        $booking = Booking::with([
            'service:id,service_name,duration,price,user_id',
            'service.merchant:id,name,phone,business_category'
        ])
            ->where('booking_by', $userId)
            ->whereIn('status', ['confirm', 'pending'])
            ->where('date_time', '>=', now())
            ->orderBy('date_time', 'asc')
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'No upcoming appointment found.'
            ]);
        }

        $result = [
            'booking_id'       => $booking->id,
            'status'           => ucfirst($booking->status),
            'amount'           => $booking->service->price ?? null,
            'booking_date' => Carbon::parse($booking->date_time)->format('Y-m-d'),
            'booking_time' => Carbon::parse($booking->date_time)->format('H:i'),
            'service_name'     => $booking->service->service_name ?? null,
            'service_duration' => $booking->service->duration ?? null,
            'service_price'    => $booking->service->price ?? null,
            'merchant_name'    => $booking->service->merchant->name ?? null,
            'merchant_phone'   => $booking->service->merchant->phone ?? null,
            'merchant_category' => $booking->service->merchant->business_category ?? null,
        ];

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }


    public function Activity(Request $request)
    {
        $userId = $request->user()->id;

        $bookings = Booking::with([
            'service:id,service_name,duration,price,user_id',
            'service.merchant:id,name,phone,business_category'
        ])
            ->where('booking_by', $userId)
            ->orderBy('date_time', 'desc')
            ->take(5)
            ->get();

        $result = $bookings->map(function ($booking) {
            return [
                'booking_id'       => $booking->id,
                'status'           => ucfirst($booking->status),
                'amount'           => $booking->service->price ?? null,
                'booking_date' => Carbon::parse($booking->date_time)->format('Y-m-d'),
                'booking_time' => Carbon::parse($booking->date_time)->format('H:i'),
                'service_name'     => $booking->service->service_name ?? null,
                'merchant_name'    => $booking->service->merchant->name ?? null,
                'merchant_phone'   => $booking->service->merchant->phone ?? null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }


    public function History(Request $request)
    {
        $userId = $request->user()->id;

        $bookings = Booking::with([
            'service:id,service_name,duration,price,user_id',
            'service.merchant:id,name,phone,business_category'
        ])
            ->where('booking_by', $userId)
            ->orderBy('date_time', 'desc')
            ->paginate(10);

        $result = $bookings->getCollection()->map(function ($booking) {
            return [
                'booking_id'       => $booking->id,
                'status'           => ucfirst($booking->status),
                'amount'           => $booking->service->price ?? null,
                'booking_date' => Carbon::parse($booking->date_time)->format('Y-m-d'),
                'booking_time' => Carbon::parse($booking->date_time)->format('H:i'),
                'service_name'     => $booking->service->service_name ?? null,
                'merchant_name'    => $booking->service->merchant->name ?? null,
                'merchant_phone'   => $booking->service->merchant->phone ?? null,
                'merchant_category' => $booking->service->merchant->business_category ?? null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result,
            'pagination' => [
                'total' => $bookings->total(),
                'per_page' => $bookings->perPage(),
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage()
            ]
        ]);
    }
}
