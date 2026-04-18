<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MerchantRegFree extends Mailable
{
    use Queueable, SerializesModels;

    public $merchant;

    public function __construct($merchant)
    {
        $this->merchant = $merchant;
    }

    public function build()
    {
        return $this->subject('Merchant Registration Free - Account Created')
                    ->view('emails.reg_free')
                    ->with([
                        'merchant' => $this->merchant,
                    ]);
    }
}
