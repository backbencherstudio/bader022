<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Payment;

class RenewSubscriptions extends Command
{
    protected $signature = 'subscription:renew';
    protected $description = 'Auto renew subscriptions using payments table only';

    public function handle()
    {
        Log::info('Subscription auto-renew started');

        $subscriptions = Subscription::where('status', 'active')
            ->where('auto_renew', 1)
            ->where('ends_at', '<=', now())
            ->get();

        if ($subscriptions->isEmpty()) {
            Log::info('No subscriptions to renew');
            return Command::SUCCESS;
        }

        $tapSetting = DB::table('settings')->latest()->first();

        if (!$tapSetting) {
            Log::error('TAP settings not found');
            return Command::FAILURE;
        }

        foreach ($subscriptions as $subscription) {

            $user = User::find($subscription->user_id);

            if (!$user) {
                Log::error("User not found for subscription {$subscription->id}");
                continue;
            }

            // Get last successful payment
            $lastPayment = Payment::where('subscription_id', $subscription->id)
                ->where('status', 'paid')
                ->latest()
                ->first();

            $amount = $lastPayment?->amount ?? 100;

            try {

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $tapSetting->tap_secret_key,
                ])->post('https://api.tap.company/v2/charges', [
                    'amount' => $amount,
                    'currency' => 'SAR',
                    'customer' => [
                        'id' => $user->tap_customer_id ?? null,
                    ],
                    'source' => [
                        'id' => $user->tap_card_token ?? null,
                    ],
                ]);

                $data = $response->json();

                // SUCCESS
                if (($data['status'] ?? null) === 'CAPTURED') {

                    DB::transaction(function () use ($subscription, $user, $amount, $data) {

                        $subscription->update([
                            'starts_at' => now(),
                            'ends_at' => now()->addYear(),
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

                    Log::info("Renewed subscription {$subscription->id}");

                } else {

                    Log::warning("Payment failed for subscription {$subscription->id}");


                }

            } catch (\Exception $e) {
                Log::error("Renew error {$subscription->id}: " . $e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
