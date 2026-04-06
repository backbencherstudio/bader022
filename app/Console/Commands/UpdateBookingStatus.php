<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Booking;

class UpdateBookingStatus extends Command
{

    protected $signature = 'booking:update-status';


    protected $description = 'Update the status of bookings that are past their date_time';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {

        $bookings = Booking::whereIn('status', ['confirm', 'rescheduled'])
            ->where('date_time', '<', Carbon::now())
            ->get();

        foreach ($bookings as $booking) {
            $booking->status = 'complete';
            $booking->save();
            $this->info("Booking ID {$booking->id} status updated to 'complete'");
        }

        $this->info('Booking statuses updated successfully!');
    }
}
