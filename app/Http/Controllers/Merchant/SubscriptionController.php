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
    public function store(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
            'payment_method' => 'required|in:Stripe,Paypal,tap',
            'auto_renew' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $plan = Plan::findOrFail($request->plan_id);
        $autoTransactionId = 'TRX-'.strtoupper(Str::random(10));

        if ($request->payment_method === 'tap') {
            try {

                $tapResponse = Http::withHeaders([
                    'Authorization' => 'Bearer '.config('services.tap.secret_key'),
                    'Content-Type' => 'application/json',
                    'accept' => 'application/json',
                ])->post(config('services.tap.base_url').'/charges', [
                    'amount' => $plan->price,
                    'currency' => 'SAR',
                    'threeDSecure' => true,
                    'save_card' => false,
                    'description' => 'Subscription for Plan: '.$plan->name,
                    'customer' => [
                        'first_name' => $user->name,
                        'email' => $user->email,
                    ],
                    'source' => ['id' => 'src_all'],
                    'redirect' => [
                        'url' => route('admin.process.callback'),
                    ],
                    'reference' => [
                        'transaction' => $autoTransactionId,
                    ],
                ]);

                $tapData = $tapResponse->json();

                if ($tapResponse->successful() && isset($tapData['transaction']['url'])) {

                    DB::beginTransaction();

                    $subscription = Subscription::create([
                        'user_id' => $user->id,
                        'plan_id' => $plan->id,
                        'starts_at' => Carbon::now(),
                        'ends_at' => Carbon::now()->addMonth(),
                        'status' => 'pending',
                        'auto_renew' => $request->auto_renew ?? 0,
                    ]);

                    Payment::create([
                        'user_id' => $user->id,
                        'subscription_id' => $subscription->id,
                        'amount' => $plan->price,
                        'currency' => 'SAR',
                        'payment_method' => 'tap',
                        'transaction_id' => $tapData['id'],
                        'status' => 'due',
                    ]);

                    DB::commit();

                    return response()->json([
                        'status' => true,
                        'message' => 'Redirect to Tap payment page',
                        'tap_url' => $tapData['transaction']['url'],
                        'charge_id' => $tapData['id'],
                    ]);

                } else {

                    return response()->json([
                        'status' => false,
                        'message' => 'Tap Payment Initialization Failed',
                        'error' => $tapData['errors'][0]['description'] ?? 'Unknown Error from Tap',
                    ], 400);
                }

            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'Internal Server Error',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        return response()->json(['status' => false, 'message' => 'Method not implemented'], 501);
    }

    public function tapCallback(Request $request)
    {

        $tapId = $request->query('tap_id');

        if (! $tapId) {
            return response()->json(['status' => false, 'message' => 'Invalid Callback: tap_id missing'], 400);
        }

        try {

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.config('services.tap.secret_key'),
                'accept' => 'application/json',
            ])->get(config('services.tap.base_url').'/charges/'.$tapId);

            $tapData = $response->json();

            $payment = Payment::where('transaction_id', $tapId)->first();

            if (! $payment) {
                return response()->json(['status' => false, 'message' => 'Payment record not found'], 404);
            }

            if ($response->successful() && $tapData['status'] === 'CAPTURED') {

                DB::beginTransaction();

                $payment->update(['status' => 'paid']);

                if ($payment->subscription) {
                    $payment->subscription->update(['status' => 'active']);
                }

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Payment Successful',
                    'data' => $tapData,
                ]);

            } else {

                $payment->update(['status' => 'failed']);

                return response()->json([
                    'status' => false,
                    'message' => 'Payment Failed',
                    'error' => $tapData['status'] ?? 'Declined',
                ], 400);
            }

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
