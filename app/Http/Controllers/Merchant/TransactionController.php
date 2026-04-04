<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\MerchantPayment;

class TransactionController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $payments = MerchantPayment::with(['user', 'booking.service'])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        if ($payments->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No transactions found for this user',
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
