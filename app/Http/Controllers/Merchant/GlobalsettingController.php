<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\GlobalSetting;
use Illuminate\Http\Request;

class GlobalsettingController extends Controller
{
public function store(Request $request)
{
    $user = auth()->user();
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized',
        ], 401);
    }

    $validated = $request->validate([
        'branding_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'logo_position' => 'nullable|string|max:255',
        'logo_size' => 'nullable|string|max:255',
        'color_system' => 'nullable|array',
        'typography' => 'nullable|array',
        'body_text_size' => 'nullable|string|max:10',
        'font_family' => 'nullable|string|max:100',
        'section_spacing' => 'nullable|string|max:50',
        'website_name' => 'nullable|string|max:255',
        'footer_des' => 'nullable|string',
        'footer_background' => 'nullable|string|max:20',
        'footer_text_color' => 'nullable|string|max:20',
        'social_links' => 'nullable|array',
        'home' => 'nullable|string|max:255',
        'home_url' => 'nullable|url',
        'about' => 'nullable|string|max:255',
        'about_url' => 'nullable|url',
        'why_choose_us' => 'nullable|string|max:255',
        'why_choose_us_url' => 'nullable|url',
        'service' => 'nullable|string|max:255',
        'service_url' => 'nullable|url',
        'contact_us' => 'nullable|string|max:255',
        'contact_url' => 'nullable|url',
        'privacy_policy' => 'nullable|string|max:255',
        'privacy_policy_url' => 'nullable|url',
        'terms_condition' => 'nullable|string|max:255',
        'terms_condition_url' => 'nullable|url',
        'contact_info' => 'nullable|array',
        'contact_email' => 'nullable|email|max:255',
        'country' => 'nullable|string|max:100',
        'turn_off' => 'nullable|boolean',
    ]);

    if ($request->hasFile('branding_logo')) {
        $file = $request->file('branding_logo');
        $path = $file->store('branding_logos', 'public');
        $validated['branding_logo'] = $path;
    }

    $exists = GlobalSetting::where('user_id', $user->id)->exists();

    $branding = GlobalSetting::updateOrCreate(
        ['user_id' => $user->id],
        $validated
    );

    $message = $exists ? 'Global settings updated successfully.' : 'Global settings created successfully.';

    return response()->json([
        'success' => true,
        'message' => $message,
        'data' => $branding,
    ]);
}




}
