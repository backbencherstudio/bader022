<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TapPayment;
use App\Models\User;

class TapPaymentController extends Controller
{
    public function upsert(Request $request)
    {
        $request->validate([
            'tap_mode' => 'required|in:test,live',
            'tap_secret_key' => 'required|string',
            'tap_public_key' => 'required|string',
        ]);


        $user = auth()->user();
        if ($user->type != 2) {
            return response()->json([
                'message' => 'Only merchants can update Tap payment settings.'
            ], 403);
        }


        $tapPayment = TapPayment::updateOrCreate(
            ['user_id' => $user->id],
            [
                'tap_mode' => $request->tap_mode,
                'tap_secret_key' => $request->tap_secret_key,
                'tap_public_key' => $request->tap_public_key,
            ]
        );

        return response()->json([
            'message' => 'Tap payment settings saved successfully.',
            'data' => $tapPayment
        ]);
    }

    public function show()
    {
        $user = auth()->user();

        if ($user->type != 2) {
            return response()->json([
                'message' => 'Only merchants can view Tap payment settings.'
            ], 403);
        }

        $tapPayment = TapPayment::where('user_id', $user->id)->first();

        if (!$tapPayment) {
            return response()->json([
                'message' => 'Tap payment settings not found.'
            ], 404);
        }

        return response()->json([
            'message' => 'Tap payment settings retrieved successfully.',
            'data' => $tapPayment
        ]);
    }
}
