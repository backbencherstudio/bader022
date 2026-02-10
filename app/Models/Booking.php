<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'staff_id',
        'service_id',
        'customer_name',
        'email',
        'phone',
        'date_time',
        'status',
        'special_note',
        'booking_by'
    ];

    public function payment()
    {
        return $this->hasOne(MerchantPayment::class);
    }

    protected $dates = ['date_time'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

}
