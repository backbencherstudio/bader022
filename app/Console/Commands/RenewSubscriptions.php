<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Models\Subscription;
use App\Models\User;
use App\Models\TapPayment;
use App\Models\Payment;
use App\Mail\SubscriptionExpiredMail;

class RenewSubscriptions extends Command
{
    protected $signature = 'subscription:renew';
    protected $description = 'Automatically renew active subscriptions that are expiring today and have auto-renew enabled.';

    public function handle()
    {
        Log::info('Subscription auto-renewal cron started...');

        $expiringSubscriptions = Subscription::where('status', 'active')
            ->where('auto_renew', 1)
            ->whereDate('ends_at', '<=', now()->toDateString())
            ->get();

        if ($expiringSubscriptions->isEmpty()) {
            Log::info('No subscriptions found for renewal today.');
            return Command::SUCCESS;
        }

        $tapSetting = DB::table('settings')->latest()->first();

        foreach ($expiringSubscriptions as $subscription) {

            $user = User::find($subscription->user_id);

            if (!$user) {
                Log::error("Subscription ID {$subscription->id}: user not found.");
                continue;
            }

            $tapPayment = TapPayment::where('user_id', $user->id)->latest()->first();

            $lastPayment = Payment::where('subscription_id', $subscription->id)->latest()->first();

            $amount = $lastPayment ? (float) $lastPayment->amount : 100.00;


            if (
                !$tapPayment ||
                !$tapPayment->tap_customer_id ||
                !$tapPayment->tap_card_token
            ) {
                Log::warning("User ID {$user->id} missing TAP credentials. Expiring subscription.");

                $subscription->update(['status' => 'expired']);

                try {
                    Mail::to($user->email)->send(new SubscriptionExpiredMail($user));
                } catch (\Exception $e) {
                    Log::error("Email failed for User ID {$user->id}: " . $e->getMessage());
                }

                continue;
            }

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $tapSetting->tap_secret_key,
                ])->post('https://api.tap.company/v2/charges', [
                    'amount' => $amount,
                    'currency' => 'SAR',
                    'customer' => [
                        'id' => $tapPayment->tap_customer_id,
                    ],
                    'source' => [
                        'id' => $tapPayment->tap_card_token,
                    ],
                    'redirect' => [
                        'url' => 'https://bokli.io/payment-status',
                    ],
                ]);

                $data = $response->json();

                if (($data['status'] ?? null) === 'CAPTURED') {

                    DB::transaction(function () use ($subscription, $user, $amount, $data) {

                        $newEndDate = $subscription->plan_id == 2
                            ? now()->addMonth()
                            : now()->addYear();

                        $subscription->update([
                            'starts_at' => now(),
                            'ends_at' => $newEndDate,
                            'status' => 'active',
                        ]);

                        Payment::create([
                            'user_id' => $user->id,
                            'subscription_id' => $subscription->id,
                            'amount' => $amount,
                            'currency' => 'SAR',
                            'payment_method' => 'tap',
                            'transaction_id' => $data['id'] ?? null,
                            'status' => 'paid',
                        ]);
                    });

                    Log::info("Subscription {$subscription->id} renewed for User {$user->id}");

                } else {

                    $subscription->update(['status' => 'expired']);

                    try {
                        Mail::to($user->email)->send(new SubscriptionExpiredMail($user));
                    } catch (\Exception $e) {
                        Log::error("Email failed: " . $e->getMessage());
                    }

                    Log::warning("Payment failed for Subscription {$subscription->id}");
                }

            } catch (\Exception $e) {
                Log::error("Renewal error Subscription {$subscription->id}: " . $e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
