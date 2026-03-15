<?php

namespace App\Mail;
use Illuminate\Mail\Mailable;


class BookingConfirmationMail extends Mailable
{
    public $booking;

    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    public function build()
    {
        return $this->subject('Booking Confirmation')
            ->view('emails.booking_confirmation');
    }
}
