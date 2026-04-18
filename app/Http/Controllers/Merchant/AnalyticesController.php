<?php

namespace App\Http\Controllers\Merchant;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\{Booking, MerchantPayment};
use Carbon\Carbon;

class AnalyticesController extends Controller
{
    // public function analytice()
    // {
    //     $user = auth()->user();

    //     if ($user->type != 2) {
    //         return response()->json(['message' => 'Unauthorized access'], 403);
    //     }

    //     $merchantId = $user->id;
    //     $startOfMonth = Carbon::now()->startOfMonth();
    //     $endOfMonth = Carbon::now()->endOfMonth();

    //     $newCustomersCount = Booking::where('user_id', $merchantId)
    //         ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
    //         ->whereNotIn('email', function ($query) use ($merchantId, $startOfMonth) {
    //             $query->select('email')
    //                 ->from('bookings')
    //                 ->where('user_id', $merchantId)
    //                 ->where('created_at', '<', $startOfMonth);
    //         })
    //         ->distinct('email')
    //         ->count('email');

    //     $returningCustomersCount = Booking::where('user_id', $merchantId)
    //         ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
    //         ->whereIn('email', function ($query) use ($merchantId, $startOfMonth) {
    //             $query->select('email')
    //                 ->from('bookings')
    //                 ->where('user_id', $merchantId)
    //                 ->where('created_at', '<', $startOfMonth);
    //         })
    //         ->distinct('email')
    //         ->count('email');

    //     return response()->json([
    //         'revenue' => (int) MerchantPayment::where('user_id', $merchantId)
    //             ->where('payment_status', 'paid')
    //             ->whereHas('booking', function ($query) {
    //                 $query->where('status', 'complete');
    //             })
    //             ->sum('amount'),

    //         'total_bookings' => Booking::where('user_id', $merchantId)->count(),

    //         'new_customers' => $newCustomersCount,
    //         'returning_customers' => $returningCustomersCount,
    //     ]);
    // }
    public function analytice()
    {
        $user = auth()->user();

        if ($user->type != 2) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }
        $merchantId = $user->id;
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $customersThisMonth = Booking::where('user_id', $merchantId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->select('email', DB::raw('COUNT(*) as total_this_month'))
            ->groupBy('email')
            ->get();

        $newCustomersCount = 0;
        $returningCustomersCount = 0;

        foreach ($customersThisMonth as $customer) {
            $existsBefore = Booking::where('user_id', $merchantId)
                ->where('email', $customer->email)
                ->where('created_at', '<', $startOfMonth)
                ->exists();
            if (!$existsBefore) {
                $newCustomersCount++;
            }
            if ($existsBefore || $customer->total_this_month > 1) {
                $returningCustomersCount++;
            }
        }

        return response()->json([
            'revenue' => (int) MerchantPayment::where('user_id', $merchantId)
                ->where('payment_status', 'paid')
                ->whereHas('booking', function ($query) {
                    $query->where('status', 'complete');
                })
                ->sum('amount'),

            'total_bookings' => Booking::where('user_id', $merchantId)->count(),

            'new_customers' => $newCustomersCount,
            'returning_customers' => $returningCustomersCount,
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

    // public function newreturn()
    // {
    //     $user = auth()->user();

    //     if ($user->type != 2) {
    //         return response()->json(['message' => 'Unauthorized access'], 403);
    //     }

    //     $merchantId = $user->id;
    //     $startOfMonth = Carbon::now()->startOfMonth();
    //     $endOfMonth = Carbon::now()->endOfMonth();

    //     $customers = Booking::where('user_id', $merchantId)
    //         ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
    //         ->select('email', DB::raw('COUNT(*) as total_orders'))
    //         ->groupBy('email')
    //         ->get();

    //     $newCustomersCount = $customers->count();
    //     $returningCustomersCount = $customers->where('total_orders', '>')->count();

    //     return response()->json([
    //         'new_customers' => $newCustomersCount,
    //         'returning_customers' => $returningCustomersCount,
    //     ]);
    // }

    public function newreturn()
    {
        $user = auth()->user();

        if ($user->type != 2) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $merchantId = $user->id;
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        $validStatuses = ['confirm', 'complete'];

        $emailsThisMonth = Booking::where('user_id', $merchantId)
            ->whereIn('status', $validStatuses)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->distinct()
            ->pluck('email');

        $newCustomersCount = 0;
        $returningCustomersCount = 0;

        foreach ($emailsThisMonth as $email) {
            $existsBefore = Booking::where('user_id', $merchantId)
                ->where('email', $email)
                ->whereIn('status', $validStatuses)
                ->where('created_at', '<', $startOfMonth)
                ->exists();

            $bookingsThisMonthCount = Booking::where('user_id', $merchantId)
                ->where('email', $email)
                ->whereIn('status', $validStatuses)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();

            if (!$existsBefore) {
                $newCustomersCount++;
            }

            if ($existsBefore || $bookingsThisMonthCount > 1) {
                $returningCustomersCount++;
            }
        }

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
            ->where('merchant_payments.payment_status', 'paid')
            ->where('bookings.status', 'complete')
            ->selectRaw('SUM(merchant_payments.amount) as total_revenue')
            ->groupBy('bookings.staff_id', 'bookings.service_id')
            ->get();

        $formattedData = $performances->map(function ($item) {
            return [
                'staff_name' => $item->staff ? $item->staff->name : 'Unknown Staff',

                'service' => $item->service ? ($item->service->name ?? $item->service->service_name ?? 'N/A') : 'N/A',
                'revenue_generated' => (int) $item->total_revenue,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $formattedData,
        ]);
    }
}
