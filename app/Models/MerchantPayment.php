<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantPayment extends Model
{
    protected $fillable = [
        'booking_id',
        'user_id',
        'payment_method',
        'amount',
        'transaction_id',
        'payment_status',
        'paid_at'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }


    
}
