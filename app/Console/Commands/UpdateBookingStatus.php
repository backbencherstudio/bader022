<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Booking;

class UpdateBookingStatus extends Command
{
   
    protected $signature = 'booking:update-status';


    protected $description = 'Update the status of bookings that are past their date_time to complete';


    public function handle()
    {

        $now = Carbon::now('Asia/Riyadh');
        $this->info("Current System Time (Riyadh): " . $now->toDateTimeString());


        $query = Booking::whereIn('status', ['confirm', 'rescheduled'])
                        ->where('date_time', '<', $now);

        $count = $query->count();

        if ($count === 0) {
            $this->warn('No eligible bookings found to update.');
            return;
        }

        $this->info("Found {$count} bookings to update.");


        $query->chunk(100, function ($bookings) {
            foreach ($bookings as $booking) {
                $booking->update([
                    'status' => 'complete'
                ]);
                $this->line("ID: {$booking->id} -> Status changed to complete.");
            }
        });

        $this->info('All eligible booking statuses updated successfully!');
    }
}
