<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $table = 'plans';

    protected $fillable = [
        'name',
        'title',
        'price',
        'currency',
        'package',
        'day',
        'features',
        'status',
    ];

    protected $casts = [
        'features' => 'array',
        'status' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function getPriceAttribute($value)
    {
        return (int) $value;
    }
}
