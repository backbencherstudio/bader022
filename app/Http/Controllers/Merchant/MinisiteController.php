<?php

namespace App\Http\Controllers\Merchant;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Validator};
use App\Http\Controllers\Controller;
use App\Models\{MiniSite, User};

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

        $miniSite = MiniSite::where('user_id', $user->id)->first();

        $data = $request->except(['hero_image', 'about_hero_image', 'cta_image']);
        $data['user_id'] = $user->id;

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

        if ($miniSite) {
            $miniSite->update($data);
            $message = 'Mini site updated successfully';
        } else {
            $miniSite = MiniSite::create($data);
            $message = 'Mini site created successfully';
        }

        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $miniSite,
        ], 200);
    }

    public function show()
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $miniSite = MiniSite::with('whychooseus', 'service')->where('user_id', $user->id)->first();

        if (! $miniSite) {
            return response()->json([
                'status' => false,
                'message' => 'Mini site not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $miniSite,
        ], 200);
    }

    public function usershow($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        }

        $miniSite = MiniSite::with(['whychooseus', 'service'])
            ->where('user_id', $user->id)
            ->first();

        if (!$miniSite) {
            return response()->json([
                'status' => false,
                'message' => 'Mini site not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $miniSite,
        ], 200);
    }

    // public function update(Request $request)
    // {
    //     $user = Auth::user();

    //     if (! $user) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Unauthorized',
    //         ], 401);
    //     }

    //     $miniSite = MiniSite::where('user_id', $user->id)->first();

    //     if (! $miniSite) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Mini site not found',
    //         ], 404);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'hero_title' => 'nullable|string|max:255',
    //         'hero_subtitle' => 'nullable|string|max:255',
    //         'hero_description' => 'nullable|string',
    //         'cta_button_text' => 'nullable|string|max:255',
    //         'cta_button_text_two' => 'nullable|string|max:255',

    //         'hero_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    //         'about_hero_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    //         'cta_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

    //         'hero_overlay_color' => 'nullable|string|max:50',
    //         'about_title' => 'nullable|string|max:255',
    //         'about_description' => 'nullable|string',
    //         'background_color' => 'nullable|string|max:50',
    //         'about_padding' => 'nullable|string',

    //         'cta_title' => 'nullable|string|max:255',
    //         'cta_subtitle' => 'nullable|string|max:255',
    //         'cta_overlay_color' => 'nullable|string|max:50',
    //         'cta_padding' => 'nullable|string',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Validation error',
    //             'errors' => $validator->errors(),
    //         ], 422);
    //     }

    //     $data = $request->except(['hero_image', 'about_hero_image', 'cta_image']);

    //     if ($request->hasFile('hero_image')) {
    //         if ($miniSite->hero_image && file_exists(public_path($miniSite->hero_image))) {
    //             unlink(public_path($miniSite->hero_image));
    //         }

    //         $file = $request->file('hero_image');
    //         $name = time().'_hero.'.$file->getClientOriginalExtension();
    //         $path = 'uploads/mini-sites/hero';

    //         $file->move(public_path($path), $name);
    //         $data['hero_image'] = $path.'/'.$name;
    //     }

    //     if ($request->hasFile('about_hero_image')) {
    //         if ($miniSite->about_hero_image && file_exists(public_path($miniSite->about_hero_image))) {
    //             unlink(public_path($miniSite->about_hero_image));
    //         }

    //         $file = $request->file('about_hero_image');
    //         $name = time().'_about.'.$file->getClientOriginalExtension();
    //         $path = 'uploads/mini-sites/about';

    //         $file->move(public_path($path), $name);
    //         $data['about_hero_image'] = $path.'/'.$name;
    //     }

    //     if ($request->hasFile('cta_image')) {
    //         if ($miniSite->cta_image && file_exists(public_path($miniSite->cta_image))) {
    //             unlink(public_path($miniSite->cta_image));
    //         }

    //         $file = $request->file('cta_image');
    //         $name = time().'_cta.'.$file->getClientOriginalExtension();
    //         $path = 'uploads/mini-sites/cta';

    //         $file->move(public_path($path), $name);
    //         $data['cta_image'] = $path.'/'.$name;
    //     }

    //     $miniSite->update($data);

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Mini site updated successfully',
    //         'data' => $miniSite,
    //     ], 200);
    // }

    public function userView($website_domain)
    {
        $user = User::with([
            'minisite',
            'services',
            'whyChooseUs',
            'globalSetting'
        ])
        ->where('website_domain', $website_domain)
        ->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        if ($user->type != 2) {
            return response()->json([
                'message' => 'Unauthorized access',
            ], 403);
        }

        return response()->json([
            'status' => true,
            'data' => $user
        ], 200, [], JSON_PRETTY_PRINT);
    }
}
