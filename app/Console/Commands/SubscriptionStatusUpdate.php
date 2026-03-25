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
        $today = Carbon::now('Asia/Riyadh');

        Subscription::where('status', 'active')
            ->where('ends_at', '<', $today)
            ->update([
                'status' => 'expired'
            ]);

        $this->info('Subscription status updated successfully');
    }


}
