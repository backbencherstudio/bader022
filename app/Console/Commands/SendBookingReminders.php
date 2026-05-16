<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Booking;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class SendBookingReminders extends Command
{
    protected $signature = 'booking:reminders';
    protected $description = 'Send 24h and 1h booking reminders via Email';

    public function handle()
    {

        $now = Carbon::now('Asia/Riyadh')->setTimezone(config('app.timezone'));

        $this->info("System Time (Aligned with DB): " . $now->toDateTimeString());

        $this->processReminder($now, 24, '24 hours');
        $this->processReminder($now, 1, '1 hour');

        $this->info("All reminders processed successfully.");
    }

    private function processReminder($now, $hours, $label)
    {
        $start = $now->copy()->addHours($hours)->subMinutes(10);
        $end   = $now->copy()->addHours($hours)->addMinutes(10);

        $this->info("Checking {$label} reminders...");
        $this->info("Start: " . $start->toDateTimeString());
        $this->info("End: " . $end->toDateTimeString());

        $bookings = Booking::with(['user', 'merchantPayment'])
            ->whereIn('status', ['confirm', 'rescheduled'])
            ->whereBetween('date_time', [$start, $end])
            ->whereHas('merchantPayment', function ($q) {
                $q->where('payment_status', 'paid');
            })
            ->get();

        $this->info("Found bookings: " . $bookings->count());

        foreach ($bookings as $booking) {

            $cacheKey = "booking_reminder_{$booking->id}_{$label}";
            if (Cache::has($cacheKey)) {
                $this->line("Booking ID: {$booking->id} already received {$label} reminder. Skipping...");
                continue;
            }
            $email = optional($booking->user)->email;

            if (!$email) {
                $this->error("No email found for Booking ID: {$booking->id}");
                continue;
            }

            try {
                Mail::send('emails.booking_reminder', [
                    'booking' => $booking,
                    'type' => $label
                ], function ($message) use ($email, $label) {
                    $message->to($email)
                        ->subject("Booking Reminder - {$label}");
                });

                Cache::put($cacheKey, true, now()->addMinutes(1440));

                $this->info("Sent {$label} reminder for Booking ID: {$booking->id}");
            } catch (\Exception $e) {
                $this->error("Email failed (ID {$booking->id}): " . $e->getMessage());
            }
        }
    }


}
