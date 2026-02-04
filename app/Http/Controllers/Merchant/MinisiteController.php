<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\MiniSite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MinisiteController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // Check duplicate first
        $exists = MiniSite::where('user_id', $user->id)->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'Mini site already exists for this user',
            ], 409); // 409 = Conflict (best for duplicate)
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'hero_title' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string|max:255',
            'hero_description' => 'nullable|string',
            'cta_button_text' => 'nullable|string|max:255',
            'cta_button_text_two' => 'nullable|string|max:255',

            'hero_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'about_hero_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'cta_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'hero_overlay_color' => 'nullable|string|max:50',
            'about_title' => 'nullable|string|max:255',
            'about_description' => 'nullable|string',
            'background_color' => 'nullable|string|max:50',
            'about_padding' => 'nullable|string',

            'cta_title' => 'nullable|string|max:255',
            'cta_subtitle' => 'nullable|string|max:255',
            'cta_overlay_color' => 'nullable|string|max:50',
            'cta_padding' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Prepare data
        $data = $request->except(['hero_image', 'about_hero_image', 'cta_image']);
        $data['user_id'] = $user->id;

        // Upload images
        if ($request->hasFile('hero_image')) {
            $data['hero_image'] = $request->file('hero_image')
                ->store('mini-sites/hero', 'public');
        }

        if ($request->hasFile('about_hero_image')) {
            $data['about_hero_image'] = $request->file('about_hero_image')
                ->store('mini-sites/about', 'public');
        }

        if ($request->hasFile('cta_image')) {
            $data['cta_image'] = $request->file('cta_image')
                ->store('mini-sites/cta', 'public');
        }

        $miniSite = MiniSite::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Mini site created successfully',
            'data' => $miniSite,
        ], 201);
    }
}
