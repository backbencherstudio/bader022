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
        'paid_at',
        'refund_id',
        'refund_date',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
    public function getAmountAttribute($value)
    {
        return (int) $value;
    }
}
