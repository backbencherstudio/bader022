<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_store_setting_id',
        'day',
        'open_time',
        'close_time',
        'is_closed',
    ];

    protected $casts = [
        'is_closed' => 'boolean',
        'open_time' => 'datetime:H:i',
        'close_time' => 'datetime:H:i',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function merchantSetting()
    {
        return $this->belongsTo(
            MerchantSetting::class,
            'merchant_store_setting_id'
        );
    }

    public function store()
{
    return $this->belongsTo(
        MerchantSetting::class,
        'merchant_store_setting_id'
    );
}
}
