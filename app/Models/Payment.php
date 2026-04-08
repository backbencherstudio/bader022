<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'subscription_id',
        'amount',
        'currency',
        'payment_method',
        'transaction_id',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function getAmountAttribute($value)
    {
        return (int) $value;
    }
}
