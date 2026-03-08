<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WhyChooseUs;

class WhyChooseUsController extends Controller
{
    public function upsert(Request $request)
    {
        $whyChoose = WhyChooseUs::firstOrNew([
            'user_id' => auth()->id()
        ]);

        $imageFields = [
            'feature_one_image',
            'feature_two_image',
            'feature_three_image'
        ];

        $data = $request->only($whyChoose->getFillable());
        $data['user_id'] = auth()->id();

        foreach ($imageFields as $field) {

            if ($request->hasFile($field)) {

                $request->validate([
                    $field => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                ]);

                $image = $request->file($field);

                $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $image->getClientOriginalExtension();
                $imageName = time() . '_' . $originalName . '.' . $extension;

                $folder = 'uploads/why_choose_us';

                if (!file_exists(public_path($folder))) {
                    mkdir(public_path($folder), 0755, true);
                }

                $image->move(public_path($folder), $imageName);

                $data[$field] = $folder . '/' . $imageName;

                if ($whyChoose->$field && file_exists(public_path($whyChoose->$field))) {
                    @unlink(public_path($whyChoose->$field));
                }
            }
        }

        $whyChoose->fill($data)->save();

        return response()->json([
            'success' => true,
            'message' => 'Why Choose Us saved successfully',
            'data' => $whyChoose,
        ]);
    }
}
