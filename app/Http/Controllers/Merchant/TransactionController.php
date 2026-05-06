<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\MerchantPayment;
use Illuminate\Http\Request;
class TransactionController extends Controller
{
    // public function index()
    // {
    //     $userId = auth()->id();

    //     $payments = MerchantPayment::with(['user', 'booking.service'])
    //         ->where('user_id', $userId)
    //         ->latest()
    //         ->get();

    //     if ($payments->isEmpty()) {
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'No transactions found for this user',
    //             'data' => [],
    //         ], 200);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Transactions fetched successfully',
    //         'data' => $payments,
    //     ], 200);
    // }

    public function index(Request $request)
    {
        $userId = auth()->id();

        // ১. হেডার থেকে X-Branch-Id নেওয়া
        $branchId = $request->header('X-Branch-Id');

        // ২. হেডার না থাকলে এরর রিটার্ন
        if (empty($branchId)) {
            return response()->json([
                'success' => false,
                'message' => 'Branch ID is required in the headers (X-Branch-Id) to see transactions.',
            ], 422);
        }

        // ৩. নির্দিষ্ট ব্রাঞ্চ অনুযায়ী পেমেন্ট লিস্ট নিয়ে আসা
        $payments = MerchantPayment::with(['user', 'booking.service'])
            ->where('user_id', $userId)
            ->where('branch_id', $branchId) // ব্রাঞ্চ ফিল্টার
            ->latest()
            ->get();

        if ($payments->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No transactions found for this branch',
                'data' => [],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Transactions fetched successfully',
            'data' => $payments,
        ], 200);
    }


    public function show($id)
    {
        $userId = auth()->id();

        $payment = MerchantPayment::with(['user', 'booking'])
            ->where('user_id', $userId)
            ->where('id', $id)
            ->first();

        if (! $payment) {
            return response()->json([
                'success' => true,
                'message' => 'Transaction not found for this user',
                'data' => null,
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Transaction fetched successfully',
            'data' => $payment,
        ], 200);
    }
}
