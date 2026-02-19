<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TapPayment extends Model
{
    protected $fillable = [
        'user_id',
        'tap_mode',
        'tap_secret_key',
        'tap_public_key',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
