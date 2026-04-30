<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_id');
    }

    public function user(): BelongsTo
    {

        return $this->belongsTo(User::class, 'user_id');
    }

    protected $casts = [
        'service_id' => 'array',
        'status' => 'boolean',
    ];
}
