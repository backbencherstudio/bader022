<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $messageText;

    public function __construct($booking, $messageText)
    {
        $this->booking = $booking;
        $this->messageText = $messageText;
    }

    public function build()
    {
        return $this->subject('Booking Cancelled')
            ->view('emails.booking_cancelled');
    }
}
