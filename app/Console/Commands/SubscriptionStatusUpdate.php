<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use Carbon\Carbon;

class SubscriptionStatusUpdate extends Command
{
    protected $signature = 'subscription:update-status';
    protected $description = 'Update expired subscriptions';

    public function handle()
    {

        $today = Carbon::now('Asia/Riyadh')->setTimezone(config('app.timezone'));

        $this->info('Current System Time (Riyadh Target): ' . Carbon::now('Asia/Riyadh')->toDateTimeString());

        $count = Subscription::where('status', 'active')
                            ->where('ends_at', '<', $today)
                            ->count();

        if ($count === 0) {
            $this->warn('No active subscriptions found to expire.');
            return;
        }

        $this->info("Found {$count} subscriptions to expire.");

        Subscription::where('status', 'active')
            ->where('ends_at', '<', $today)
            ->chunkById(100, function ($subscriptions) {
                foreach ($subscriptions as $subscription) {
                    $subscription->update([
                        'status' => 'expired'
                    ]);
                    $this->line("Subscription ID: {$subscription->id} -> Status changed to expired.");
                }
            });

        $this->info('Subscription status updated successfully');
    }


}
