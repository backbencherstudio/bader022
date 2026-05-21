<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubscriptionRenewedMail;

class RenewSubscriptions extends Command
{
    protected $signature = 'subscription:auto-renew';
    protected $description = 'Production ready auto-renew subscriptions via Tap Payments';

    public function handle()
    {

        $expiredSubscriptions = Subscription::with('plan')
            ->where('status', 'active')
            ->where('auto_renew', 1)
            ->where('ends_at', '<=', Carbon::now())
            ->get();

        if ($expiredSubscriptions->isEmpty()) {
            $this->info('No expired active subscriptions found for auto-renew.');
            return 0;
        }

        foreach ($expiredSubscriptions as $subscription) {
            try {
                $this->info("Processing Subscription ID: {$subscription->id}...");


                $chargeAmount = $subscription->amount ?? $subscription->plan->amount ?? $subscription->plan->price ?? 0;
                $chargeCurrency = $subscription->currency ?? $subscription->plan->currency ?? 'SAR';

                if (!$chargeAmount || $chargeAmount <= 0) {
                    Log::warning("Skipping Auto-Renew for Subscription ID: {$subscription->id} due to zero amount.");
                    $this->error("Skipping Subscription ID: {$subscription->id} due to invalid amount.");
                    continue;
                }

                // Tap Payments API Call
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . config('services.tap.secret_key'),
                    'Accept' => 'application/json',
                ])->post('https://api.tap.company/v3/charges', [
                    'amount'   => $chargeAmount,
                    'currency' => $chargeCurrency,
                    'customer' => [
                        'id' => $subscription->tap_customer_id,
                    ],
                    'source' => [
                        'id' => 'src_all'
                    ],
                    'metadata' => [
                        'subscription_id' => $subscription->id
                    ]
                ]);

                $result = $response->json();

                if ($response->successful() && isset($result['status']) && $result['status'] === 'CAPTURED') {


                    $endsAt = Carbon::now();
                    if ($subscription->plan_id == 3) {
                        $endsAt = $endsAt->addYear();
                    } else {
                        $endsAt = $endsAt->addMonth();
                    }


                    $subscription->update([
                        'starts_at' => Carbon::now(),
                        'ends_at'   => $endsAt,
                        'status'    => 'active',
                    ]);

                    $finalAmount   = $result['amount'] ?? $chargeAmount;
                    $finalCurrency = $result['currency'] ?? $chargeCurrency;
                    $transactionId = $result['id'];


                    Payment::create([
                        'user_id'         => $subscription->user_id,
                        'subscription_id' => $subscription->id,
                        'amount'          => $finalAmount,
                        'currency'        => $finalCurrency,
                        'payment_method'  => 'tap',
                        'transaction_id'  => $transactionId,
                        'status'          => 'paid',
                    ]);

                    Log::info("Subscription ID {$subscription->id} successfully auto-renewed. Transaction ID: {$transactionId}");
                    $this->info("Success: Renewed subscription ID: {$subscription->id}");


                    $this->sendNotificationEmail($subscription, true);

                } else {

                    $errorMessage = $result['errors'][0]['description'] ?? $result['status'] ?? 'UNKNOWN_ERROR';
                    Log::warning("Auto-renew failed for Subscription ID {$subscription->id}. Reason: " . $errorMessage);
                    $this->error("Failed: Tap Payments declined the charge for ID: {$subscription->id}");


                    $this->sendNotificationEmail($subscription, false);
                }

            } catch (\Exception $e) {
                Log::error("Critical error in auto-renew for Subscription ID {$subscription->id}: " . $e->getMessage());
                $this->error("Exception error: " . $e->getMessage());
            }
        }

        return 0;
        // return 0;
    }

    private function sendNotificationEmail($subscription, $isSuccess)
    {
        $email = $subscription->user->email ?? $subscription->email ?? null;
        if ($email) {
            Mail::to($email)->send(new SubscriptionRenewedMail($subscription, $isSuccess));
        }
    }
}
