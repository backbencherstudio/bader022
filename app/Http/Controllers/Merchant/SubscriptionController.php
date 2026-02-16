<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    // public function store(Request $request)
    // {
    //     $user = Auth::user();

    //     if (! $user) {
    //         return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'plan_id' => 'required|exists:plans,id',
    //         'amount' => 'required|numeric|min:0',
    //         'payment_method' => 'required|in:Stripe,Paypal,tap',
    //         'auto_renew' => 'nullable|boolean',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
    //     }

    //     DB::beginTransaction();

    //     try {

    //         $autoTransactionId = 'TRX-'.strtoupper(Str::random(10));

    //         $subscription = Subscription::create([
    //             'user_id' => $user->id,
    //             'plan_id' => $request->plan_id,
    //             'starts_at' => Carbon::now(),
    //             'ends_at' => Carbon::now()->addMonth(),
    //             'status' => 'active',
    //             'auto_renew' => 0,
    //         ]);

    //         $payment = Payment::create([
    //             'user_id' => $user->id,
    //             'subscription_id' => $subscription->id,
    //             'amount' => $request->amount,
    //             'currency' => 'SAR',
    //             'payment_method' => $request->payment_method,
    //             'transaction_id' => $autoTransactionId,
    //             'status' => 'pending',
    //         ]);

    //         DB::commit();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Subscription created successfully',
    //             'data' => [
    //                 'subscription' => $subscription,
    //                 'payment' => $payment,
    //                 'transaction_id' => $autoTransactionId,
    //             ],
    //         ], 201);

    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Something went wrong',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // public function store(Request $request)
    // {
    //     $user = Auth::user();

    //     if (! $user) {
    //         return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'plan_id' => 'required|exists:plans,id',
    //         'payment_method' => 'required|in:Stripe,Paypal,tap',
    //         'auto_renew' => 'nullable|boolean',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
    //     }

    //     DB::beginTransaction();

    //     try {

    //         // 🔹 Get Plan
    //         $plan = Plan::findOrFail($request->plan_id);

    //         $autoTransactionId = 'TRX-'.strtoupper(Str::random(10));

    //         $subscription = Subscription::create([
    //             'user_id' => $user->id,
    //             'plan_id' => $plan->id,
    //             'starts_at' => Carbon::now(),
    //             'ends_at' => Carbon::now()->addMonth(),
    //             'status' => 'active', // better pending before payment success
    //             'auto_renew' => $request->auto_renew ?? 0,
    //         ]);

    //         $payment = Payment::create([
    //             'user_id' => $user->id,
    //             'subscription_id' => $subscription->id,
    //             'amount' => $plan->price, // ✅ Auto plan price
    //             'currency' => 'SAR',
    //             'payment_method' => $request->payment_method,
    //             'transaction_id' => $autoTransactionId,
    //             'status' => 'pending',
    //         ]);

    //         DB::commit();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Subscription created successfully',
    //             'data' => [
    //                 'subscription' => $subscription,
    //                 'payment' => $payment,
    //                 'transaction_id' => $autoTransactionId,
    //                 'amount' => $plan->price,
    //             ],
    //         ], 201);

    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Something went wrong',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // public function store(Request $request)
    // {
    //     $user = Auth::user();

    //     if (! $user) {
    //         return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'plan_id' => 'required|exists:plans,id',
    //         'payment_method' => 'required|in:Stripe,Paypal,tap',
    //         'auto_renew' => 'nullable|boolean',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
    //     }

    //     DB::beginTransaction();

    //     try {

    //         $plan = Plan::findOrFail($request->plan_id);

    //         $autoTransactionId = 'TRX-'.strtoupper(Str::random(10));

    //         $subscription = Subscription::create([
    //             'user_id' => $user->id,
    //             'plan_id' => $plan->id,
    //             'starts_at' => Carbon::now(),
    //             'ends_at' => Carbon::now()->addMonth(),
    //             'status' => 'active',
    //             'auto_renew' => $request->auto_renew ?? 0,
    //         ]);

    //         $payment = Payment::create([
    //             'user_id' => $user->id,
    //             'subscription_id' => $subscription->id,
    //             'amount' => $plan->price,
    //             'currency' => 'SAR',
    //             'payment_method' => $request->payment_method,
    //             'transaction_id' => $autoTransactionId,
    //             'status' => 'pending',
    //         ]);

    //         DB::commit();

    //         // 🔹 If Tap is selected, create a payment session
    //         if ($request->payment_method === 'tap') {
    //             $tapResponse = Http::withHeaders([
    //                 'Authorization' => 'Bearer '.env('TAP_SECRET_KEY'),
    //                 'Content-Type' => 'application/json',
    //             ])->post(env('TAP_BASE_URL').'charges', [
    //                 'amount' => $plan->price * 100, // Tap expects minor currency unit
    //                 'currency' => 'SAR',
    //                 'threeDSecure' => true,
    //                 'description' => 'Subscription for Plan ID: '.$plan->id,
    //                 'statement_descriptor' => 'YourCompany',
    //                 'reference' => [
    //                     'transaction' => $autoTransactionId,
    //                     'order' => $subscription->id,
    //                 ],
    //                 'customer' => [
    //                     'first_name' => $user->name,
    //                     'email' => $user->email,
    //                 ],
    //                 'source' => ['type' => 'tap'],
    //                 'redirect' => [
    //                     // 'url' => route('tap.callback'), // Create a callback route
    //                 ],
    //             ]);

    //             $tapData = $tapResponse->json();

    //             return response()->json([
    //                 'status' => true,
    //                 'message' => 'Subscription created, redirect to Tap payment',
    //                 'data' => [
    //                     'tap_url' => $tapData['url'] ?? null,
    //                     'transaction_id' => $autoTransactionId,
    //                     'subscription_id' => $subscription->id,
    //                     'amount' => $plan->price,
    //                 ],
    //             ]);
    //         }

    //         // 🔹 For other payment methods, return normal response
    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Subscription created successfully',
    //             'data' => [
    //                 'subscription' => $subscription,
    //                 'payment' => $payment,
    //                 'transaction_id' => $autoTransactionId,
    //                 'amount' => $plan->price,
    //             ],
    //         ], 201);

    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Something went wrong',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // public function store(Request $request)
    // {
    //     $user = Auth::user();
    //     if (! $user) {
    //         return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'plan_id' => 'required|exists:plans,id',
    //         'payment_method' => 'required|in:Stripe,Paypal,tap',
    //         'auto_renew' => 'nullable|boolean',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
    //     }

    //     DB::beginTransaction();

    //     try {
    //         $plan = Plan::findOrFail($request->plan_id);

    //         $autoTransactionId = 'TRX-'.strtoupper(Str::random(10));

    //         $subscription = Subscription::create([
    //             'user_id' => $user->id,
    //             'plan_id' => $plan->id,
    //             'starts_at' => Carbon::now(),
    //             'ends_at' => Carbon::now()->addMonth(),
    //             'status' => 'active',
    //             'auto_renew' => $request->auto_renew ?? 0,
    //         ]);

    //         $payment = Payment::create([
    //             'user_id' => $user->id,
    //             'subscription_id' => $subscription->id,
    //             'amount' => $plan->price,
    //             'currency' => 'SAR',
    //             'payment_method' => $request->payment_method,
    //             'transaction_id' => $autoTransactionId,
    //             'status' => 'pending',
    //         ]);

    //         DB::commit();

    //         if ($request->payment_method === 'tap') {
    //             $tapResponse = Http::withHeaders([
    //                 'Authorization' => 'Bearer '.env('TAP_SECRET_KEY'),
    //                 'Content-Type' => 'application/json',
    //             ])->post(env('TAP_BASE_URL').'charges', [
    //                 'amount' => $plan->price * 100,
    //                 'currency' => 'SAR',
    //                 'threeDSecure' => true,
    //                 'description' => 'Subscription for Plan ID: '.$plan->id,
    //                 'statement_descriptor' => 'YourCompany',
    //                 'reference' => [
    //                     'transaction' => $autoTransactionId,
    //                     'order' => $subscription->id,
    //                 ],
    //                 'customer' => [
    //                     'first_name' => $user->name,
    //                     'email' => $user->email,
    //                 ],
    //                 'source' => ['type' => 'tap'],
    //                 'redirect' => [
    //                     'url' => route('tap.callback'),
    //                 ],
    //             ]);

    //             $tapData = $tapResponse->json();

    //             return response()->json([
    //                 'status' => true,
    //                 'message' => 'Redirect to Tap payment page',
    //                 'tap_url' => $tapData['transaction']['url'] ?? null,
    //                 'transaction_id' => $autoTransactionId,
    //                 'subscription_id' => $subscription->id,
    //                 'amount' => $plan->price,
    //             ]);
    //         }

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Subscription created successfully',
    //             'data' => [
    //                 'subscription' => $subscription,
    //                 'payment' => $payment,
    //                 'transaction_id' => $autoTransactionId,
    //                 'amount' => $plan->price,
    //             ],
    //         ], 201);

    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Something went wrong',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
            'payment_method' => 'required|in:Stripe,Paypal,tap',
            'auto_renew' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $plan = Plan::findOrFail($request->plan_id);

            // Generate unique transaction ID
            $autoTransactionId = 'TRX-'.strtoupper(Str::random(10));

            // Create subscription record
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'starts_at' => Carbon::now(),
                'ends_at' => Carbon::now()->addMonth(),
                'status' => 'active',
                'auto_renew' => $request->auto_renew ?? 0,
            ]);

            // Create payment record
            $payment = Payment::create([
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'amount' => $plan->price,
                'currency' => 'SAR',
                'payment_method' => $request->payment_method,
                'transaction_id' => $autoTransactionId,
                'status' => 'pending',
            ]);

            DB::commit();

            // If Tap payment selected
            if ($request->payment_method === 'tap') {

                $tapResponse = Http::withHeaders([
                    'Authorization' => 'Bearer '.env('TAP_SECRET_KEY'),
                    'Content-Type' => 'application/json',
                ])->post(env('TAP_BASE_URL').'charges', [
                    'amount' => $plan->price * 100, // smallest currency unit
                    'currency' => 'SAR',
                    'threeDSecure' => true,
                    'description' => 'Subscription for Plan ID: '.$plan->id,
                    'statement_descriptor' => 'YourCompany',
                    'reference' => [
                        'transaction' => $autoTransactionId,
                        'order' => $subscription->id,
                    ],
                    'customer' => [
                        'first_name' => $user->name,
                        'email' => $user->email,
                    ],
                    'source' => ['type' => 'card'], // ✅ correct
                    'metadata' => [
                        'user_id' => $user->id,
                        'plan_id' => $plan->id,
                    ],
                    'redirect' => [
                        'url' => route('tap.callback'), 
                    ],
                ]);

                $tapData = $tapResponse->json();

                // Debug: check response structure
                // \Log::info($tapData);

                return response()->json([
                    'status' => true,
                    'message' => 'Redirect to Tap payment page',
                    'tap_url' => $tapData['transaction']['url'] ?? null, // ✅ URL will appear here
                    'transaction_id' => $autoTransactionId,
                    'subscription_id' => $subscription->id,
                    'amount' => $plan->price,
                ]);
            }

            // Other payment methods
            return response()->json([
                'status' => true,
                'message' => 'Subscription created successfully',
                'data' => [
                    'subscription' => $subscription,
                    'payment' => $payment,
                    'transaction_id' => $autoTransactionId,
                    'amount' => $plan->price,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function callback(Request $request)
    {
        $tapPaymentId = $request->input('id');

        $response = Http::withToken(env('TAP_SECRET_KEY'))
            ->get(env('TAP_BASE_URL')."/charges/{$tapPaymentId}");

        $tapData = $response->json();

        if ($tapData['status'] === 'CAPTURED') {
            $subscription = new Subscription;
            $subscription->user_id = Auth::id();
            $subscription->plan_id = $tapData['metadata']['plan_id'] ?? null;
            $subscription->amount = $tapData['amount'] / 100;
            $subscription->payment_status = 'paid';
            $subscription->tap_payment_id = $tapPaymentId;
            $subscription->save();

            return response()->json([
                'status' => true,
                'message' => 'Payment successful',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Payment failed or cancelled',
            ]);
        }
    }
}
