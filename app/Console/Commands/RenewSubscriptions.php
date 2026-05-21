<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail; // <-- ১. মেইল ফাসাদ ইমপোর্ট করুন
use App\Mail\SubscriptionRenewedMail; // <-- ২. নতুন মেইল ক্লাস ইমপোর্ট করুন

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
            $this->info('Subscription ID not found, not active, or auto-renew is 0.');
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

                if (true) { // আপনার টেস্ট ওভাররাইড কন্ডিশন

                    $subscription->update([
                        'starts_at' => Carbon::now(),
                        'ends_at' => Carbon::now()->addMonth(),
                        'status' => 'active',
                    ]);

                    Log::info("Subscription ID {$subscription->id} successfully auto-renewed (Test Override).");
                    $this->info("Success: Renewed subscription ID: {$subscription->id}");

                    // <-- ৩. সফল হওয়ার মেইল পাঠান
                    // (ধরে নিচ্ছি আপনার Subscription মডেলে ইউজার রিলেশন আছে অথবা সরাসরি ইমেল ফিল্ড আছে)
                    if (isset($subscription->user->email)) {
                        Mail::to($subscription->user->email)->send(new SubscriptionRenewedMail($subscription, true));
                    } elseif (isset($subscription->email)) {
                        Mail::to($subscription->email)->send(new SubscriptionRenewedMail($subscription, true));
                    }

                } else {

                    Log::warning("Auto-renew failed for ID {$subscription->id}. Tap Status: " . ($result['status'] ?? 'UNKNOWN') . " | Response: " . json_encode($result));
                    $this->error("Failed: Tap Payments declined the charge for ID: {$subscription->id}");

                    // <-- ৪. ব্যর্থ হওয়ার মেইল পাঠান
                    if (isset($subscription->user->email)) {
                        Mail::to($subscription->user->email)->send(new SubscriptionRenewedMail($subscription, false));
                    } elseif (isset($subscription->email)) {
                        Mail::to($subscription->email)->send(new SubscriptionRenewedMail($subscription, false));
                    }
                }

            } catch (\Exception $e) {
                Log::error("Error renewing subscription ID {$subscription->id}: " . $e->getMessage());
                $this->error("Exception error: " . $e->getMessage());
            }
        }

        return 0;
    }
}
