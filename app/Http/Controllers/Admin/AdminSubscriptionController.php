<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;

class AdminSubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::with([
            'user.merchantSetting',
            'plan',
        ])->latest()->get();

        $mapped = $subscriptions->map(function ($subscription) {
            return [
                'id' => $subscription->id,

                'user' => [
                    'id' => $subscription->user->id ?? null,
                    'name' => $subscription->user->name ?? null,
                    'email' => $subscription->user->email ?? null,
                    'store_name' => optional($subscription->user->merchantSetting)->store_name,
                    'business_logo' => optional($subscription->user->merchantSetting)->business_logo
                        ? asset('storage/' . optional($subscription->user->merchantSetting)->business_logo)
                        : null,
                ],

                'plan' => [
                    'id' => $subscription->plan->id ?? null,
                    'name' => $subscription->plan->name ?? null,
                    'price' => $subscription->plan->price ?? null,
                    'package' => $subscription->plan->package ?? null,
                ],

                'status' => $subscription->status,
                'starts_at' => $subscription->starts_at,
                'ends_at' => $subscription->ends_at,
                'created_at' => $subscription->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $mapped,
        ], 200);
    }

    public function show($id)
    {
        $subscription = Subscription::with([
            'user.merchantSetting',
            'plan',
        ])->find($id);

        if (! $subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found',
            ], 404);
        }

        $mapped = [
            'id' => $subscription->id,

            'user' => [
                'id' => $subscription->user->id ?? null,
                'name' => $subscription->user->name ?? null,
                'email' => $subscription->user->email ?? null,
                'store_name' => optional($subscription->user->merchantSetting)->store_name,
                'business_logo' => optional($subscription->user->merchantSetting)->business_logo
                    ? asset('storage/' . optional($subscription->user->merchantSetting)->business_logo)
                    : null,
            ],

            'plan' => [
                'id' => $subscription->plan->id ?? null,
                'name' => $subscription->plan->name ?? null,
                'price' => $subscription->plan->price ?? null,
            ],

            'status' => $subscription->status,
            'starts_at' => $subscription->starts_at,
            'ends_at' => $subscription->ends_at,
            'created_at' => $subscription->created_at->format('Y-m-d H:i:s'),
        ];

        return response()->json([
            'success' => true,
            'data' => $mapped,
        ], 200);
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


        $validated = $request->validate([
            'status' => 'required|in:active,pending,expired,cancelled',
        ]);

        $subscription->status = $validated['status'];
        $subscription->save();

        return response()->json([
            'success' => true,
            'message' => 'Subscription status updated successfully',
            'data' => [
                'id' => $subscription->id,
                'status' => $subscription->status,
            ],
        ]);
    }

    public function summary()
    {
        $subscriptions = Subscription::with('plan')->get();

        $totalPackages = $subscriptions->pluck('plan.id')->count();
        $activeSubscriptions = $subscriptions->where('status', 'active')->count();
        $expiredSubscriptions = $subscriptions->where('ends_at', '<', now())->count();
        $expiringSoon = $subscriptions->whereBetween('ends_at', [now(), now()->addDays(7)])->count();

        return response()->json([
            'success' => true,
            'summary' => [
                'total_packages' => $totalPackages,
                'active_subscriptions' => $activeSubscriptions,
                'expired_subscriptions' => $expiredSubscriptions,
                'expiring_soon' => $expiringSoon,
            ]
        ]);
    }
}
