<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\MerchantPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

            'revenue' => (int) MerchantPayment::where('user_id', $merchantId)
                ->where('payment_status', 'paid')
                ->whereHas('booking', function ($query) {
                    $query->where('status', 'complete');
                })
                ->sum('amount'),

            'total_bookings' => Booking::where('user_id', $merchantId)
                ->count(),

            'appointments' => Booking::where('user_id', $merchantId)
                ->whereIn('status', ['confirm', 'rescheduled'])
                ->count(),

            'Total_Customers' => Booking::where('user_id', $merchantId)
                ->where('status', 'complete')
                ->count(),

        ]);
    }


    public function monthlypaymentrevenue()
    {
        $user = auth()->user();
        $merchantId = $user->id;
        $year = date('Y');

        $revenues = MerchantPayment::where('payment_status', 'paid')
            ->where('merchant_payments.user_id', $merchantId)
            ->whereHas('booking', function ($query) {
                $query->where('status', 'complete');
            })
            ->whereYear('created_at', $year)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount) as total_revenue')
            )
            ->groupBy('month')
            ->pluck('total_revenue', 'month');

        $months = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Aug',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dec',
        ];

        $result = [];

        foreach ($months as $monthNumber => $monthName) {
            $result[] = [
                'name' => $monthName,
                'revenue' => (float) ($revenues[$monthNumber] ?? 0),
            ];
        }

        return response()->json($result);
    }

    public function weeklyPaymentrevenue()
    {
        $user = auth()->user();
        $merchantId = $user->id;
        $year = date('Y');
        $month = date('m');

        $revenues = MerchantPayment::where('payment_status', 'paid')
            ->where('user_id', $merchantId)
            ->whereHas('booking', function ($query) {
                $query->where('status', 'complete');
            })
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->select(
                DB::raw('DAYOFWEEK(created_at) as weekday'),
                DB::raw('SUM(amount) as total_revenue')
            )
            ->groupBy('weekday')
            ->pluck('total_revenue', 'weekday');

        $weekDays = [
            7 => 'Saturday',
            1 => 'Sunday',
            2 => 'Monday',
            3 => 'Tuesday',
            4 => 'Wednesday',
            5 => 'Thursday',
            6 => 'Friday',
        ];

        $result = [];

        foreach ($weekDays as $dayNumber => $dayName) {
            $result[] = [
                'name' => $dayName,
                'revenue' => (float) ($revenues[$dayNumber] ?? 0),
            ];
        }

        return response()->json($result);
    }

    public function todayAppointment()
    {
        $user = auth()->user();
        $merchantId = $user->id;

        $today = Carbon::today()->toDateString();

        $bookings = Booking::with(['user', 'staff', 'service'])
            ->where('user_id', $merchantId)
            ->whereIn('status', ['confirm', 'rescheduled'])
            ->whereDate('date_time', $today)
            ->latest()
            ->get();

        $bookings->transform(function ($booking) {
            $booking->date_time = Carbon::parse($booking->date_time)
                ->format('M  d Y, h:i A');
            return $booking;
        });

        if ($bookings->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No bookings created today',
                'data' => [],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Bookings created today',
            'data' => $bookings,
        ], 200);
    }
}
