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
    'status',
];
}
