<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Branch extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'address',
        'status',
        'is_main',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function staffs()
{
    return $this->hasMany(\App\Models\Staff::class);
}

public function services()
{
    return $this->hasMany(Service::class, 'branch_id');
}

public function bookings()
{
    return $this->hasMany(\App\Models\Booking::class);
}


}
