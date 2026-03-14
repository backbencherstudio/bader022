<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Booking;
use App\Models\Payment;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    public function generate($bookingId)
    {
        $userId = auth()->id();

        $booking = Booking::with([
            'service:id,service_name,duration,price',
            'staff:id,name',
            'merchant:id,name,email,phone',
            'merchantStore:id,user_id,store_name,business_address,business_logo',
            'merchantPayment'
        ])
            ->where('id', $bookingId)
            ->where('booking_by', $userId)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        $payment = $booking->merchantPayment;

        $invoice = [

            'invoice_info' => [
                'invoice_no' => 'INV-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT),
                'booking_id' => 'BOK' . str_pad($booking->id, 5, '0', STR_PAD_LEFT),
                'invoice_date' => $booking->created_at->format('M d, Y'),
            ],

            'merchant_info' => [
                'business_logo' => $booking->merchantStore->business_logo ?? null,
                'business_name' => $booking->merchantStore->store_name ?? '',
                'merchant_name' => $booking->merchant->name ?? '',
                'email' => $booking->merchant->email ?? '',
                'phone' => $booking->merchant->phone ?? '',
                'address' => $booking->merchantStore->business_address ?? '',
            ],

            'customer_info' => [
                'name' => $booking->customer_name,
                'email' => $booking->email,
                'phone' => $booking->phone,
            ],

            'booking_details' => [
                'service' => $booking->service->service_name,
                'staff' => $booking->staff->name ?? 'Any Staff',
                'duration' => $booking->service->duration . ' min',
                'booking_time' => Carbon::parse($booking->date_time)->format('M d, Y h:i A'),
            ],

            'payment_details' => [
                'payment_method' => ucfirst($payment->payment_method ?? ''),
                'transaction_id' => $payment->transaction_id ?? '',
                'status' => ucfirst($payment->payment_status ?? ''),
                'paid_at' => $payment->paid_at
                    ? Carbon::parse($payment->paid_at)->format('M d, Y h:i A')
                    : '',
            ],

            'summary' => [
                'service_price' => $booking->service->price,
                'tax' => 0,
                'discount' => 0,
                'total_amount' => $booking->service->price,
                'currency' => 'SAR'
            ]
        ];

        $pdf = Pdf::loadView('invoice', compact('invoice'))
            ->setPaper('A4', 'portrait');

        return $pdf->download('invoice-' . $booking->id . '.pdf');
    }

    public function adminInvoice($id)
    {
        $payment = Payment::with([
            'user.merchantSetting',
            'subscription.plan',
        ])->find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        $invoice = [

            'invoice_info' => [
                'invoice_no' => 'INV-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
                'subscription_id' => 'SUB-' . str_pad($payment->subscription_id, 5, '0', STR_PAD_LEFT),
                'invoice_date' => now()->format('M d, Y'),
            ],

            'merchant_info' => [
                'business_logo' => optional($payment->user->merchantSetting)->business_logo ?? null,
                'business_name' => optional($payment->user->merchantSetting)->store_name ?? '',
                'business_address' => optional($payment->user->merchantSetting)->business_address ?? '',
                'merchant_name' => $payment->user->name ?? '',
                'email' => $payment->user->email ?? '',
                'phone' => $payment->user->phone ?? '',
                'address' => optional($payment->user->merchantSetting)->business_address ?? '',
            ],

            'subscription_details' => [
                'package' => optional($payment->subscription->plan)->name ?? '',
                'duration' => $payment->subscription->plan->day . ' days',
                'start_date' => Carbon::parse($payment->subscription->starts_at)->format('M d, Y'),
                'end_date' => Carbon::parse($payment->subscription->ends_at)->format('M d, Y'),
                'status' => $payment->subscription->status,
            ],

            'payment_details' => [
                'payment_method' => ucfirst($payment->payment_method ?? ''),
                'transaction_id' => $payment->transaction_id ?? '',
                'status' => ucfirst($payment->status ?? ''),
                'paid_at' => $payment->created_at
                    ? $payment->created_at->format('M d, Y h:i A')
                    : '',
            ],

            'summary' => [
                'service_price' => $payment->amount,
                'tax' => 0,
                'discount' => 0,
                'total_amount' => $payment->amount,
                'currency' => 'SAR'
            ]
        ];

        $pdf = Pdf::loadView('admin_invoice', compact('invoice'))
            ->setPaper('A4', 'portrait')
            ->setOptions(['isRemoteEnabled' => true]);

        return $pdf->download('invoice-' . $payment->transaction_id . '.pdf');
    }
}
