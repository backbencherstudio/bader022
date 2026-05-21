<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardPayment extends Model
{
    use HasFactory;


    protected $table = 'card_payments';


    protected $fillable = [
        'user_id',
        'tap_customer_id',
        'tap_card_token',
        'card_brand',
        'card_last_four',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
