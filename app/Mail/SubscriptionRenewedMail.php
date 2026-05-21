<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Subscription;

class SubscriptionRenewedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subscription;
    public $isSuccess;


    public function __construct(Subscription $subscription, $isSuccess = true)
    {
        $this->subscription = $subscription;
        $this->isSuccess = $isSuccess;
    }

    public function build()
    {
        $subject = $this->isSuccess
            ? 'Your Subscription Has Been Successfully Renewed!'
            : 'Action Required: Your Subscription Renewal Failed';

        return $this->subject($subject)
                    ->view('emails.subscription_renewed'); 
    }
}
