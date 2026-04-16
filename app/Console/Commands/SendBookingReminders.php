<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Booking;
use Illuminate\Support\Facades\Mail;

class SendBookingReminders extends Command
{
    protected $signature = 'booking:reminders';
    protected $description = 'Send 24h and 1h booking reminders via Email';

    public function handle()
    {
        // ✅ FIXED TIMEZONE
        $now = Carbon::now('Asia/Riyadh');

        $this->info("System Time (Riyadh): " . $now->toDateTimeString());

        // 24 hours reminder
        $this->processReminder($now, 24, '24 hours');

        // 1 hour reminder
        $this->processReminder($now, 1, '1 hour');

        $this->info("All reminders processed successfully.");
    }

    private function processReminder($now, $hours, $label)
    {
        // Target time window
        $start = $now->copy()->addHours($hours)->subMinutes(10);
        $end   = $now->copy()->addHours($hours)->addMinutes(10);

        $this->info("Checking {$label} reminders...");
        $this->info("Start: " . $start->toDateTimeString());
        $this->info("End: " . $end->toDateTimeString());

        $bookings = Booking::with(['user', 'merchantPayment'])
            ->whereIn('status', ['confirm', 'reschedule'])
            ->whereBetween('date_time', [$start, $end])
            ->whereHas('merchantPayment', function ($q) {
                $q->where('payment_status', 'paid');
            })
            ->get();

        $this->info("Found bookings: " . $bookings->count());

        foreach ($bookings as $booking) {

            $email = optional($booking->user)->email;

            if (!$email) {
                $this->error("No email found for Booking ID: {$booking->id}");
                continue;
            }

            try {
                Mail::send('emails.booking_reminder', [
                    'booking' => $booking,
                    'type' => $label
                ], function ($message) use ($email, $label, $booking) {
                    $message->to($email)
                        ->subject("Booking Reminder - {$label}");
                });

                $this->info("Sent {$label} reminder for Booking ID: {$booking->id}");

            } catch (\Exception $e) {
                $this->error("Email failed (ID {$booking->id}): " . $e->getMessage());
            }
        }
    }
}
