<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhyChooseUs extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'section_title',
        'section_subtitle',

        'feature_one_image',
        'feature_one_title',
        'feature_one_des',

        'feature_two_image',
        'feature_two_title',
        'feature_two_des',

        'feature_three_image',
        'feature_three_title',
        'feature_three_des',

        'background_color',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function minisite()
    {
        return $this->hasMany(MiniSite::class, 'user_id', 'user_id');
    }
}
