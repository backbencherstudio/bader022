<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'user_id',
        'service_name',
        'duration',
        'price',
        'description',
        'image',
        'status',
    ];

    protected $casts = [
        'duration' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function merchant()
    {
        return $this->belongsTo(User::class, 'user_id'); 
    }
}
