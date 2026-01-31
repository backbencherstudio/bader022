<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiniSite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'hero_title',
        'hero_subtitle',
        'hero_description',
        'cta_button_text',
        'hero_image',
        'hero_overlay_color',
        'about_title',
        'hero_hero_image',
        'about_description',
        'background_color',
        'cta_title',
        'cta_subtitle',
        'cta_image',
        'cta_overlay_color',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
