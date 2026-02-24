<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PaymentHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with([
            'user.merchantSetting',
            'subscription.plan',
        ])->orderBy('created_at', 'desc');

        if ($request->search) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('transaction_id', 'like', "%{$search}%")
                    ->orWhere('payment_method', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")

                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })

                    ->orWhereHas('user.merchantSetting', function ($q3) use ($search) {
                        $q3->where('store_name', 'like', "%{$search}%");
                    })

                    ->orWhereHas('subscription.plan', function ($q4) use ($search) {
                        $q4->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $payments = $query->get();

        $mapped = $payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'tx_id' => $payment->transaction_id,
                'merchant_name' => $payment->user->name ?? null,
                'business_logo' => optional($payment->user->merchantSetting)->business_logo
                    ? asset('storage/' . optional($payment->user->merchantSetting)->business_logo)
                    : null,
                'store_name' => optional($payment->user->merchantSetting)->store_name,
                'package_name' => optional($payment->subscription->plan)->name,
                'date' => $payment->created_at->format('Y-m-d'),
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'status' => ucfirst($payment->status),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $mapped,
        ]);
    }

    public function show($id)
    {
        $payment = Payment::with([
            'user.merchantSetting',
            'subscription.plan',
        ])->find($id);

        if (! $payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        $mapped = [
            'id' => $payment->id,
            'tx_id' => $payment->transaction_id,

            'merchant_name' => $payment->user->name ?? null,
            'merchant_email' => $payment->user->email ?? null,
            'merchant_phone' => $payment->user->phone ?? null,

            'business_logo' => optional($payment->user->merchantSetting)->business_logo
                ? asset('storage/' . optional($payment->user->merchantSetting)->business_logo)
                : null,

            'store_name' => optional($payment->user->merchantSetting)->store_name,

            'package_name' => optional($payment->subscription->plan)->name,

            'date' => $payment->created_at->format('Y-m-d'),

            'amount' => $payment->amount,

            'payment_method' => $payment->payment_method,

            'status' => ucfirst($payment->status),
        ];

        return response()->json([
            'success' => true,
            'data' => $mapped,
        ]);
    }

    public function update(Request $request, $id)
    {
        $payment = Payment::find($id);

        if (! $payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,successfull,failed',
        ]);

        $payment->status = $validated['status'];
        $payment->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment status updated successfully',
            'data' => [
                'id' => $payment->id,
                'status' => ucfirst($payment->status),
            ],
        ]);
    }

    //     public function sendEmail($id)
    //     {
    //         $payment = Payment::with(['user.storeSetting', 'subscription.plan'])->find($id);

    //         if (! $payment || ! $payment->user) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Payment not found',
    //             ], 404);
    //         }

    //         if (empty($payment->user->email)) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Customer email not found',
    //             ], 422);
    //         }

    //         $data = [
    //             'status' => ucfirst($payment->status),
    //             'transaction_id' => $payment->transaction_id,
    //             'amount' => $payment->amount,
    //             'date_time' => $payment->created_at->format('Y-m-d h:i A'),
    //             'payment_method' => $this->paymentMethodText($payment->payment_method),

    //             'merchant_name' => $payment->user->name,
    //             'store_name' => $payment->user->storeSetting->store_name ?? 'N/A',
    //             'email' => $payment->user->email,
    //             'phone' => $payment->user->phone ?? 'N/A',
    //             'package' => $payment->subscription->plan->name ?? 'N/A',
    //         ];

    //         $emailBody = "
    //         <h2>Payment {$data['status']}</h2>
    //         <p>Monthly subscription payment for <strong>{$data['package']}</strong></p>

    //         <hr>

    //         <h3>Transaction Information</h3>
    //         <p><strong>Transaction ID:</strong> {$data['transaction_id']}</p>
    //         <p><strong>Amount:</strong> {$data['amount']}</p>
    //         <p><strong>Date & Time:</strong> {$data['date_time']}</p>
    //         <p><strong>Payment Method:</strong> {$data['payment_method']}</p>

    //         <hr>

    //         <h3>Customer Information</h3>
    //         <p><strong>Merchant Name:</strong> {$data['merchant_name']}</p>
    //         <p><strong>Business Name:</strong> {$data['store_name']}</p>
    //         <p><strong>Email:</strong> {$data['email']}</p>
    //         <p><strong>Phone:</strong> {$data['phone']}</p>
    //         <p><strong>Package:</strong> {$data['package']}</p>
    //     ";

    //         try {
    //             Mail::send([], [], function ($message) use ($data, $emailBody) {
    //                 $message->to($data['email'])
    //                     ->subject('Payment Receipt')
    //                     ->setBody($emailBody, 'text/html');
    //             });
    //         } catch (\Exception $e) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Failed to send email',
    //             ], 500);
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Receipt email sent successfully',
    //         ]);
    //     }
}
