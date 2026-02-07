<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\Mail;

class PaymentHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with([
            'user.storeSetting',
            'subscription.plan'
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plan_name')) {
            $query->whereHas('subscription.plan', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->plan_name . '%');
            });
        }

        if ($request->filled('store_name')) {
            $query->whereHas('user.storeSetting', function ($q) use ($request) {
                $q->where('store_name', 'like', '%' . $request->store_name . '%');
            });
        }


        $payments = $query->orderBy('created_at', 'desc')->paginate(12);

        $mapped = $payments->getCollection()->transform(function ($payment) {
            return [
                'id' => $payment->id,
                'tx_id' => $payment->transaction_id,
                'merchant_name' => $payment->user->name ?? null,
                'business_logo' => $payment->user->storeSetting->business_logo ?? null,
                'store_name' => $payment->user->storeSetting->store_name ?? null,
                'package_name' => $payment->subscription->plan->name ?? null,
                'date' => $payment->created_at->format('Y-m-d'),
                'amount' => $payment->amount,
                'payment_method' => $this->paymentMethodText($payment->payment_method),
                'status' => ucfirst($payment->status),
            ];
        });

        $payments->setCollection($mapped);

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    private function paymentMethodText($method)
    {
        return match ($method) {
            0 => 'Credit Card',
            1 => 'Paypal',
            2 => 'Pay at Store',
            3 => 'Cash',
            default => 'Unknown',
        };
    }

    public function show($id)
    {
        $payment = Payment::with([
            'user.storeSetting',
            'subscription.plan'
        ])->find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        $data = [
            'payment_status' => ucfirst($payment->status),
            'description' => "Monthly subscription payment for " . ($payment->subscription->plan->name ?? 'Plan'),

            'transaction' => [
                'transaction_id' => $payment->transaction_id,
                'amount' => $payment->amount,
                'date_time' => $payment->created_at->format('Y-m-d h:i A'),
                'payment_method' => $this->paymentMethodText($payment->payment_method),
            ],

            'customer' => [
                'merchant_name' => $payment->user->name ?? null,
                'store_name' => $payment->user->storeSetting->store_name ?? null,
                'email' => $payment->user->email ?? null,
                'phone' => $payment->user->phone ?? null,
                'package' => $payment->subscription->plan->name ?? null,
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:successfull,failed,pending'
        ]);

        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        $payment->status = $request->status;
        $payment->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment status updated successfully',
            'data' => [
                'id' => $payment->id,
                'status' => $payment->status
            ]
        ]);
    }

    public function sendEmail($id)
    {
        $payment = Payment::with(['user.storeSetting', 'subscription.plan'])->find($id);

        if (!$payment || !$payment->user) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        if (empty($payment->user->email)) {
            return response()->json([
                'success' => false,
                'message' => 'Customer email not found'
            ], 422);
        }

        $data = [
            'status'         => ucfirst($payment->status),
            'transaction_id' => $payment->transaction_id,
            'amount'         => $payment->amount,
            'date_time'      => $payment->created_at->format('Y-m-d h:i A'),
            'payment_method' => $this->paymentMethodText($payment->payment_method),

            'merchant_name'  => $payment->user->name,
            'store_name'  => $payment->user->storeSetting->store_name ?? 'N/A',
            'email'          => $payment->user->email,
            'phone'          => $payment->user->phone ?? 'N/A',
            'package'        => $payment->subscription->plan->name ?? 'N/A',
        ];

        $emailBody = "
        <h2>Payment {$data['status']}</h2>
        <p>Monthly subscription payment for <strong>{$data['package']}</strong></p>

        <hr>

        <h3>Transaction Information</h3>
        <p><strong>Transaction ID:</strong> {$data['transaction_id']}</p>
        <p><strong>Amount:</strong> {$data['amount']}</p>
        <p><strong>Date & Time:</strong> {$data['date_time']}</p>
        <p><strong>Payment Method:</strong> {$data['payment_method']}</p>

        <hr>

        <h3>Customer Information</h3>
        <p><strong>Merchant Name:</strong> {$data['merchant_name']}</p>
        <p><strong>Business Name:</strong> {$data['store_name']}</p>
        <p><strong>Email:</strong> {$data['email']}</p>
        <p><strong>Phone:</strong> {$data['phone']}</p>
        <p><strong>Package:</strong> {$data['package']}</p>
    ";

        try {
            Mail::send([], [], function ($message) use ($data, $emailBody) {
                $message->to($data['email'])
                    ->subject('Payment Receipt')
                    ->setBody($emailBody, 'text/html');
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Receipt email sent successfully'
        ]);
    }
}
