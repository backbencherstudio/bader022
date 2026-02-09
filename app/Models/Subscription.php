<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'starts_at',
        'ends_at',
        'status',
        'auto_renew'
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function payments()
{
    return $this->hasMany(Payment::class);
}

}
