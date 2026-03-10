<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\{Payment, User};

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $merchantsCount = User::where('type', 1)->count();

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

    public function monthlypaymentCount()
    {
        $year = date('Y');

        $revenues = Payment::where('status', 'successfull')
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

        return response()->json($result);
    }

    public function weeklyPaymentCount()
    {
        $year = date('Y');
        $month = date('m');

        $revenues = Payment::where('status', 'successfull')
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
                'day' => $dayName,
                'revenue' => (float) ($revenues[$dayNumber] ?? 0),
            ];
        }

        return response()->json($result);
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

}
