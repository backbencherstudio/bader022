<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\MerchantPayment;
use Carbon\Carbon;

class MerchantController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->where('type', 2);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('business_category', 'LIKE', "%{$search}%")
                    ->orWhere('current_package', 'LIKE', "%{$search}%");
            });
        }

        $merchants = $query->paginate(12);


        $merchants->getCollection()->transform(function ($merchant) {
            return [
                'id' => $merchant->id,
                'business_name' => $merchant->name,
                'business_type' => $merchant->business_category,
                'email' => $merchant->email,
                'package' => $merchant->current_package,
                'plan_type' => $merchant->package_duration,
                'expire_date' => $merchant->package_expire_date,
                'status' => $merchant->status == 1 ? 'Active' : 'Inactive',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $merchants
        ]);
    }

    public function show($id)
    {
        $merchant = User::where('type', 2)->findOrFail($id);

        $payments = MerchantPayment::where('user_id', $merchant->id)
            ->orderBy('paid_at', 'desc')
            ->get()
            ->map(function ($payment) {
                return [
                    'invoice_id' => $payment->id,  
                    'payment_date' => Carbon::parse($payment->paid_at)->format('d-m-Y'),
                    'amount_paid' => '$' . $payment->amount,
                    'payment_status' => ucfirst($payment->status),
                ];
            });

        return response()->json([
            'business_information' => [
                'image'            => $merchant->image,
                'business_name'   => $merchant->name,
                'business_type'   => ucfirst(str_replace('_', ' ', $merchant->business_category)),
                'owner_name'      => $merchant->name,
                'phone'           => $merchant->phone,
                'email'           => $merchant->email,
                'location'        => $merchant->address,
                'website_domain'  => $merchant->website_domain,
                'hosting_status'  => $merchant->status == 1 ? 'Active' : 'Inactive',
                // 'live_status'     => $merchant->status == 1 ? 'Live' : 'Offline',
                'platform_access' => $merchant->platform_access == 1 ? 'Enabled' : 'Disabled',
            ],

            'package_info' => [
                'current_package'     => $merchant->current_package,
                'package_duration'    => $merchant->package_duration,
                'package_start_date'  => $merchant->package_start_date,
                'expire_date'         => $merchant->package_expire_date,
                'remaining_days'      => $merchant->remaining_day . ' days',
                'package_status'      => $merchant->package_status == 1 ? 'Active' : 'Inactive',
            ],

            'payment_history' => $payments
        ]);
    }

    public function update(Request $request, $id)
    {
        $merchant = User::where('type', 2)->findOrFail($id);

        $validatedData = $request->validate([
            'status' => 'required|in:1,0',
            'platform_access' => 'required|in:1,0',
        ]);

        $merchant->status = $validatedData['status'];
        $merchant->platform_access = $validatedData['platform_access'];

        $merchant->save();

        return response()->json([
            'message' => 'Merchant profile updated successfully',
            'merchant' => $merchant,
        ]);
    }
}
