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
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function bookedUser()
    {
        return $this->belongsTo(User::class, 'booking_by');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function merchant()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function merchantStore()
    {
        return $this->belongsTo(
            MerchantSetting::class,
            'user_id',
            'user_id'
        );
    }

    public function merchantPayment()
    {
        return $this->hasOne(MerchantPayment::class, 'booking_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }

}
