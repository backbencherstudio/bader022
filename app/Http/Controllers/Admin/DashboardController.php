<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $merchantsCount = User::where('type', 2)->count();

        $revenue = Payment::where('status', 'paid')->sum('amount');

        $planSalesRaw = Payment::where('payments.status', 'paid')
            ->join('subscriptions', 'payments.subscription_id', '=', 'subscriptions.id')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->whereIn('plans.name', ['Basic', 'Premium', 'Enterprise'])
            ->select(
                'plans.name as plan_name',
                DB::raw('COUNT(payments.id) as total_sold')
            )
            ->groupBy('plans.name')
            ->pluck('total_sold', 'plan_name');

        $planSales = [
            'Basic' => (int) ($planSalesRaw['Basic'] ?? 0),
            'Premium' => (int) ($planSalesRaw['Premium'] ?? 0),
            'Enterprise' => (int) ($planSalesRaw['Enterprise'] ?? 0),
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'revenue' => (float) $revenue,
                'merchants_count' => (int) $merchantsCount,
                'plan_sales' => $planSales,
            ],
        ]);
    }

    // public function monthlypaymentCount()
    // {
    //     $year = date('Y');

    //     $revenues = Payment::where('status', 'paid')
    //         ->whereYear('created_at', $year)
    //         ->select(
    //             DB::raw('MONTH(created_at) as month'),
    //             DB::raw('SUM(amount) as total_revenue')
    //         )
    //         ->groupBy('month')
    //         ->pluck('total_revenue', 'month');

    //     $months = [
    //         1 => 'Jan',
    //         2 => 'Feb',
    //         3 => 'Mar',
    //         4 => 'Apr',
    //         5 => 'May',
    //         6 => 'Jun',
    //         7 => 'Jul',
    //         8 => 'Aug',
    //         9 => 'Sep',
    //         10 => 'Oct',
    //         11 => 'Nov',
    //         12 => 'Dec',
    //     ];

    //     $result = [];

    //     foreach ($months as $monthNumber => $monthName) {
    //         $result[] = [
    //             'month' => $monthName,
    //             'revenue' => (float) ($revenues[$monthNumber] ?? 0),
    //         ];
    //     }

    //     return response()->json($result);
    // }

    public function monthlypaymentCount(Request $request)
    {
        // 1. Get the year from the request, default to the current year if not provided
        $year = $request->input('year', date('Y'));

        // 2. Fetch revenues for the specified year
        $revenues = Payment::where('status', 'paid')
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
                'month' => $monthName,
                'revenue' => (float) ($revenues[$monthNumber] ?? 0),
            ];
        }

        // 3. Optional: Include the target year in the response meta-data if needed
        return response()->json([
            'year' => $year,
            'data' => $result
        ]);
    }



    // public function weeklyPaymentCount()
    // {
    //     $year = date('Y');
    //     $month = date('m');

    //     $revenues = Payment::where('status', 'paid')
    //         ->whereYear('created_at', $year)
    //         ->whereMonth('created_at', $month)
    //         ->select(
    //             DB::raw('DAYOFWEEK(created_at) as weekday'),
    //             DB::raw('SUM(amount) as total_revenue')
    //         )
    //         ->groupBy('weekday')
    //         ->pluck('total_revenue', 'weekday');

    //     $weekDays = [
    //         1 => 'Saturday',
    //         2 => 'Sunday',
    //         3 => 'Monday',
    //         4 => 'Tuesday',
    //         5 => 'Wednesday',
    //         6 => 'Thursday',
    //         7 => 'Friday',
    //     ];

    //     $result = [];

    //     foreach ($weekDays as $dayNumber => $dayName) {
    //         $result[] = [
    //             'day' => $dayName,
    //             'revenue' => (float) ($revenues[$dayNumber] ?? 0),
    //         ];
    //     }

    //     return response()->json($result);
    // }

    public function weeklyPaymentCount(Request $request)
    {
        // 1. Get year and month from request, fallback to current values if missing
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));

        // 2. Fetch revenues for the specified year and month
        $revenues = Payment::where('status', 'paid')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->select(
                DB::raw('DAYOFWEEK(created_at) as weekday'),
                DB::raw('SUM(amount) as total_revenue')
            )
            ->groupBy('weekday')
            ->pluck('total_revenue', 'weekday');

        // Note: In MySQL, DAYOFWEEK() returns 1 = Sunday, 2 = Monday, ..., 7 = Saturday.
        // If your DB configuration maps 1 to Saturday, keep your array as is.
        // Standard MySQL mapping is provided below just in case:
        $weekDays = [
            1 => 'Sunday',
            2 => 'Monday',
            3 => 'Tuesday',
            4 => 'Wednesday',
            5 => 'Thursday',
            6 => 'Friday',
            7 => 'Saturday',
        ];

        $result = [];

        foreach ($weekDays as $dayNumber => $dayName) {
            $result[] = [
                'day' => $dayName,
                'revenue' => (float) ($revenues[$dayNumber] ?? 0),
            ];
        }

        // 3. Return response with context metadata
        return response()->json([
            'year' => $year,
            'month' => $month,
            'data' => $result
        ]);
    }

    //     public function paymentCounts()
    // {
    //     $year = date('Y');
    //     $month = date('m');

    //     // Monthly Revenue
    //     $monthlyRevenues = Payment::where('status', 'successfull')
    //         ->whereYear('created_at', $year)
    //         ->select(
    //             DB::raw('MONTH(created_at) as month'),
    //             DB::raw('SUM(amount) as total_revenue')
    //         )
    //         ->groupBy('month')
    //         ->pluck('total_revenue', 'month');

    //     $months = [
    //         1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
    //         5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
    //         9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
    //     ];

    //     $monthlyData = [];
    //     foreach ($months as $monthNumber => $monthName) {
    //         $monthlyData[] = [
    //             'month' => $monthName,
    //             'revenue' => (float) ($monthlyRevenues[$monthNumber] ?? 0),
    //         ];
    //     }

    //     // Weekly Revenue (current month)
    //     $weeklyRevenues = Payment::where('status', 'successfull')
    //         ->whereYear('created_at', $year)
    //         ->whereMonth('created_at', $month)
    //         ->select(
    //             DB::raw('DAYOFWEEK(created_at) as weekday'),
    //             DB::raw('SUM(amount) as total_revenue')
    //         )
    //         ->groupBy('weekday')
    //         ->pluck('total_revenue', 'weekday');

    //     $weekDays = [
    //         1 => 'Saturday',
    //         2 => 'Sunday',
    //         3 => 'Monday',
    //         4 => 'Tuesday',
    //         5 => 'Wednesday',
    //         6 => 'Thursday',
    //         7 => 'Friday',
    //     ];

    //     $weeklyData = [];
    //     foreach ($weekDays as $dayNumber => $dayName) {
    //         $weeklyData[] = [
    //             'day' => $dayName,
    //             'revenue' => (float) ($weeklyRevenues[$dayNumber] ?? 0),
    //         ];
    //     }

    //     return response()->json([
    //         'monthly' => $monthlyData,
    //         'weekly' => $weeklyData,
    //     ]);
    // }

    public function businessTypeAnalytics()
    {
        $totalMerchants = User::where('type', 2)->count();

        $categories = User::where('type', 2)
            ->select('business_category', DB::raw('COUNT(*) as total'))
            ->groupBy('business_category')
            ->get();

        return response()->json([
            'total_merchants' => $totalMerchants,
            'categories' => $categories,
        ]);
    }



    public function adminNotifications()
    {
        $authUser = auth()->user();
        $notifications = collect();

        // TYPE 1 = Admin
        if ($authUser->type == 1) {

            $users = User::where('type', 2)
                ->whereDate('created_at', today())
                ->latest()
                ->take(10)
                ->get(['id', 'name', 'created_at'])
                ->map(function ($user) {
                    return [
                        'message' => $user->name . ' your subscription is confirmed',
                        'date' => $user->created_at->format('d M Y h:i A'),
                    ];
                });

            $notifications = $notifications->merge($users);
        }

        // TYPE 2 = Merchant / Customer view
        if ($authUser->type == 2) {

            $bookings = Booking::where('user_id', $authUser->id)
                ->whereDate('created_at', today())
                ->latest()
                ->take(10)
                ->get(['id', 'customer_name', 'created_at'])
                ->map(function ($booking) {
                    return [
                        'message' => $booking->customer_name . ' your service is confirmed',
                        'date' => $booking->created_at->format('d M Y h:i A'),
                    ];
                });

            $notifications = $notifications->merge($bookings);
        }

        // TYPE 0 = Customer / simple message
        if ($authUser->type == 0) {

            $bookings = Booking::where('booking_by', $authUser->id)
                ->whereDate('created_at', today())
                ->latest()
                ->take(10)
                ->get(['id', 'customer_name', 'created_at'])
                ->map(function ($booking) {
                    return [
                        'message' => 'Your service is confirmed',
                        'date' => $booking->created_at->format('d M Y h:i A'),
                    ];
                });

            $notifications = $notifications->merge($bookings);
        }

        return response()->json([
            'data' => $notifications->values(),
        ]);
    }


}
