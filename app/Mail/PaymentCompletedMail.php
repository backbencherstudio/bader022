<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $merchant;

    public function __construct($merchant)
    {
        $this->merchant = $merchant;
    }

    public function build()
    {
        return $this->subject('Payment Successful - Account Created')
                    ->view('emails.payment_completed')
                    ->with([
                        'merchant' => $this->merchant,  
                    ]);
    }
}
