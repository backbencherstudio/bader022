<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\BusinessHour;
use App\Models\MerchantSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MerchantSettingController extends Controller
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

        $validated = $request->validate([
            'store_name' => 'nullable|string|max:255',
            'business_logo' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
            'business_category' => 'nullable',
            'business_address' => 'nullable|string',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'time_zone' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:10',
            'business_hours' => 'nullable|array',
        ]);

        $store = MerchantSetting::where('user_id', $user->id)->first();

        if ($request->hasFile('business_logo')) {
            $file = $request->file('business_logo');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('business_logos'), $filename);
            $validated['business_logo'] = '/business_logos/'.$filename;

            if ($store && $store->business_logo && file_exists(public_path($store->business_logo))) {
                unlink(public_path($store->business_logo));
            }
        }

        if ($store) {
            $store->update($validated + [
                'time_zone' => $request->time_zone ?? $store->time_zone,
                'currency' => $request->currency ?? $store->currency,
            ]);
            $message = 'Store updated successfully';
        } else {
            $store = MerchantSetting::create($validated + [
                'user_id' => $user->id,
                'time_zone' => $request->time_zone ?? 'Asia/Riyadh',
                'currency' => $request->currency ?? 'SAR',
            ]);
            $message = 'Store created successfully';
        }

        if (! empty($request->business_hours)) {
            foreach ($request->business_hours as $day => $time) {

                $open = $time['open'] ?? null;
                $close = $time['close'] ?? null;

                BusinessHour::updateOrCreate(
                    [
                        'merchant_store_setting_id' => $store->id,
                        'day' => $day,
                    ],
                    [
                        'open_time' => $time['open'] ?? null,
                        'close_time' => $time['close'] ?? null,
                        'is_closed' => ($open === null && $close === null) ? 1 : 0,
                    ]
                );
            }
        }

        $store->load('businessHours');

        return response()->json([
            'status' => true,
            'data' => $store,
            'message' => $message,
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

        $store = MerchantSetting::with('businessHours')
            ->where('user_id', $user->id)
            ->first();

        if (! $store) {
            return response()->json([
                'status' => false,
                'message' => 'Store not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $store,
        ], 200);
    }
}
