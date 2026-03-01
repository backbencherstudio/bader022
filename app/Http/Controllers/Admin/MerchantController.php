<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;

class MerchantController extends Controller
{
    public function index(Request $request)
    {
        $query = Subscription::with(['user', 'plan']);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;

            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('business_category', 'LIKE', "%{$search}%")
                    ->orWhere('current_package', 'LIKE', "%{$search}%");
            });
        }

        $subscriptions = $query->get();

        return response()->json([
            'success' => true,
            'data' => $subscriptions,
        ]);
    }

    public function show($id)
    {

        $subscription = Subscription::with(['user', 'plan', 'payments'])->find($id);

        if (! $subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $subscription,
        ]);
    }

    public function update(Request $request, $id)
    {

        $subscription = Subscription::find($id);

        if (! $subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found',
            ], 404);
        }

        $merchant = $subscription->user;

        if (! $merchant || $merchant->type != 2) {
            return response()->json([
                'success' => false,
                'message' => 'Merchant not found for this subscription',
            ], 404);
        }

        $validatedData = $request->validate([
            'status' => 'nullable|in:1,0',
            'platform_access' => 'nullable|in:1,0',
            'platform_status' => 'nullable|string',
        ]);

        if (isset($validatedData['status'])) {
            $merchant->status = $validatedData['status'];
        }

        if (isset($validatedData['platform_access'])) {
            $merchant->platform_access = $validatedData['platform_access'];
        }

        if (isset($validatedData['platform_status'])) {
            $merchant->platform_status = $validatedData['platform_status'];
        }

        $merchant->save();

        return response()->json([
            'success' => true,
            'message' => 'Merchant updated successfully subscription',
            'merchant' => $merchant,
            // 'merchant' => $merchant,
        ]);
    }
}
