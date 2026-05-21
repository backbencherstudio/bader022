<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RenewSubscriptions extends Command
{
    protected $signature = 'subscription:auto-renew';
    protected $description = 'Renew specific active subscription for testing with test override';

    public function handle()
    {

        $expiredSubscriptions = Subscription::
            where('status', 'active')
            ->where('auto_renew', 1)
            ->get();

        if ($expiredSubscriptions->isEmpty()) {
            $this->info('Subscription ID 263 not found, not active, or auto-renew is 0.');
            return 0;
        }

        foreach ($expiredSubscriptions as $subscription) {
            try {
                $this->info("Processing Subscription ID: {$subscription->id}...");


                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . config('services.tap.secret_key'),
                    'Accept' => 'application/json',
                ])->post('https://api.tap.company/v3/charges', [
                    'amount' => 10,
                    'currency' => 'KWD',
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


                if (true) {

                    $subscription->update([
                        'starts_at' => Carbon::now(),
                        'ends_at' => Carbon::now()->addMonth(),
                        'status' => 'active',
                    ]);

                    Log::info("Subscription ID {$subscription->id} successfully auto-renewed (Test Override).");
                    $this->info("Success: Renewed subscription ID: {$subscription->id}");

                } else {

                    Log::warning("Auto-renew failed for ID {$subscription->id}. Tap Status: " . ($result['status'] ?? 'UNKNOWN') . " | Response: " . json_encode($result));
                    $this->error("Failed: Tap Payments declined the charge for ID: {$subscription->id}");
                }

            } catch (\Exception $e) {
                Log::error("Error renewing subscription ID {$subscription->id}: " . $e->getMessage());
                $this->error("Exception error: " . $e->getMessage());
            }
        }

        return 0;
    }
}
