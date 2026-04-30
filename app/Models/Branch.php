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

    protected $casts = [
        'status' => 'boolean',
        'is_main' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
