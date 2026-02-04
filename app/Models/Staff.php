<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staffs';
    protected $fillable = [
        'name',
        'user_id',
        'role',
        'service_id',
        'image',
        'status',
    ];

    // public function services()
    // {
    //     return $this->belongsToMany(Service::class, 'service_id');
    // }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
