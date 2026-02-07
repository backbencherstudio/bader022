<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MerchantStoreSetting extends Model
{
    use HasFactory;

    protected $table = 'merchant_store_settings';

    protected $fillable = [
        'user_id',
        'store_name',
        'business_logo',
        'business_id',
        'business_category',
        'business_address',
        'country',
        'city',
        'time_zone',
        'currency',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
