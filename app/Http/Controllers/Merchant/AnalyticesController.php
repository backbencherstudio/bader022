<?php

namespace App\Http\Controllers\Merchant;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\{Booking, MerchantPayment};
use Carbon\Carbon;

class AnalyticesController extends Controller
{
    public function analytice()
    {
        $user = auth()->user();

        if ($user->type != 2) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $merchantId = $user->id;
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $newCustomersCount = Booking::where('user_id', $merchantId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereNotIn('email', function ($query) use ($merchantId, $startOfMonth) {
                $query->select('email')
                    ->from('bookings')
                    ->where('user_id', $merchantId)
                    ->where('created_at', '<', $startOfMonth);
            })
            ->distinct('email')
            ->count('email');

        $returningCustomersCount = Booking::where('user_id', $merchantId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereIn('email', function ($query) use ($merchantId, $startOfMonth) {
                $query->select('email')
                    ->from('bookings')
                    ->where('user_id', $merchantId)
                    ->where('created_at', '<', $startOfMonth);
            })
            ->distinct('email')
            ->count('email');

        return response()->json([
            'revenue' => MerchantPayment::where('user_id', $merchantId)
                ->where('payment_status', 'paid')
                ->sum('amount'),

            'total_bookings' => Booking::where('user_id', $merchantId)->count(),

            'new_customers' => $newCustomersCount,
            'returning_customers' => $returningCustomersCount,
        ]);
    }

    public function monthlypaymentrevenue()
    {
        $year = date('Y');

        $revenues = MerchantPayment::where('payment_status', 'paid')
            ->whereYear('created_at', $year)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount) as total_revenue')
            )
            ->groupBy('month')
            ->pluck('total_revenue', 'month');

        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
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
        $year = date('Y');
        $month = date('m');

        $revenues = MerchantPayment::where('payment_status', 'paid')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->select(
                DB::raw('DAYOFWEEK(created_at) as weekday'),
                DB::raw('SUM(amount) as total_revenue')
            )
            ->groupBy('weekday')
            ->pluck('total_revenue', 'weekday');

        $weekDays = [
            1 => 'Saturday',
            2 => 'Sunday',
            3 => 'Monday',
            4 => 'Tuesday',
            5 => 'Wednesday',
            6 => 'Thursday',
            7 => 'Friday',
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

    public function newreturn()
    {
        $user = auth()->user();

        if ($user->type != 2) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $merchantId = $user->id;
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $newCustomersCount = Booking::where('user_id', $merchantId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereNotIn('email', function ($query) use ($merchantId, $startOfMonth) {
                $query->select('email')
                    ->from('bookings')
                    ->where('user_id', $merchantId)
                    ->where('created_at', '<', $startOfMonth);
            })
            ->distinct('email')
            ->count('email');

        $returningCustomersCount = Booking::where('user_id', $merchantId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereIn('email', function ($query) use ($merchantId, $startOfMonth) {
                $query->select('email')
                    ->from('bookings')
                    ->where('user_id', $merchantId)
                    ->where('created_at', '<', $startOfMonth);
            })
            ->distinct('email')
            ->count('email');

        return response()->json([
            'new_customers' => $newCustomersCount,
            'returning_customers' => $returningCustomersCount,
        ]);
    }

    public function staffPerformance()
    {
        $user = auth()->user();
        $merchantId = $user->id;
        $performances = Booking::where('bookings.user_id', $merchantId)
            ->join('merchant_payments', 'bookings.id', '=', 'merchant_payments.booking_id')
            ->with(['staff:id,name', 'service'])
            ->select('bookings.staff_id', 'bookings.service_id')
            ->selectRaw('SUM(case when merchant_payments.payment_status = "paid" then merchant_payments.amount else 0 end) as total_revenue')
            ->groupBy('bookings.staff_id', 'bookings.service_id')
            ->get();

        $formattedData = $performances->map(function ($item) {
            return [
                'staff_name' => $item->staff ? $item->staff->name : 'Unknown Staff',

                'service' => $item->service ? ($item->service->name ?? $item->service->service_name ?? 'N/A') : 'N/A',
                'revenue_generated' => $item->total_revenue,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $formattedData,
        ]);
    }
}
