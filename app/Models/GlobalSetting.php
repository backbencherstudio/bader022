<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalSetting extends Model
{
    use HasFactory;

    protected $table = 'global_settings';

    protected $fillable = [
        'user_id',
        'branding_logo',
        'logo_position',
        'logo_size',
        'color_system',
        'typography',
        'body_text_size',
        'font_family',
        'section_spacing',
        'website_name',
        'footer_des',
        'footer_background',
        'footer_text_color',
        'social_links',
        'home',
        'home_url',
        'about',
        'about_url',
        'why_choose_us',
        'why_choose_us_url',
        'service',
        'service_url',
        'contact_us',
        'contact_url',
        'privacy_policy',
        'privacy_policy_url',
        'terms_condition',
        'terms_condition_url',
        'contact_info',
        'contact_email',
        'country',
        'turn_off',
    ];

    protected $casts = [
        'color_system' => 'array',
        'typography' => 'array',
        'social_links' => 'array',
        'contact_info' => 'array',
        'turn_off' => 'boolean',
    ];
}
