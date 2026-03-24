<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Hash, Http, Mail, Validator};
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\{Payment, Plan, Subscription, User};
use Spatie\Permission\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Mail\PaymentCompletedMail;

class AuthController extends Controller
{
    public function index()
    {
        $admins = User::where('type', 1)->get();

        return response()->json([
            'status' => 'success',
            'admin' => $admins,

        ]);
    }

    public function login(Request $request)
    {
        $email = $request->email;
        $password = $request->password;

        $user = User::where('email', $email)->first();

        if (! $user) {
            return response()->json(['error' => 'Email Incorrect'], 401);
        }

        if (! Hash::check($password, $user->password)) {
            return response()->json(['error' => 'Password Incorrect'], 401);
        }

        // Check subscription for merchants
        if ($user->type == 2) {
            $subscription = $user->subscription;

            if (! $subscription || $subscription->status == 'expired' || $subscription->ends_at < now()) {
                return response()->json([
                    'error' => 'Your subscription has expired. Please renew to login.',
                ], 403);
            }
        }

        // Determine user role
        if ($user->type == 0) {
            $role = 'User';
        } elseif ($user->type == 1) {
            $role = 'Admin';
        } elseif ($user->type == 2) {
            $role = 'Merchant';
        } else {
            return response()->json(['error' => 'Invalid user type'], 403);
        }

        // Generate JWT token
        $token = Auth::guard('api')->login($user);

        if ($user->jwt_token) {
            try {
                JWTAuth::setToken($user->jwt_token)->invalidate();
            } catch (\Exception $e) {
            }
        }

        $user->update(['jwt_token' => $token]);

        return response()->json([
            'success' => true,
            'message' => $role . ' login successfully',
            'data' => [
                'user' => $user,
                'user_type' => $role,
            ],
            'token' => $token,
        ]);
    }

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('user'), $imageName);

            $imagePath = 'user/' . $imageName;
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'type' => 0,
            'image' => $imagePath,
            'password' => Hash::make($request->password),
        ]);

        $token = Auth::guard('api')->login($user);

        $user->update([
            'jwt_token' => $token,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => $user,
            'token' => $token,
        ], 201);
    }

    public function adminregister(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $role = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'api',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('user'), $imageName);
            $imagePath = 'user/' . $imageName;
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'type' => 1,
            'status' => 1,

            'image' => $imagePath,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($role->name);

        $token = Auth::guard('api')->login($user);
        $user->update(['jwt_token' => $token]);

        return response()->json([
            'success' => true,
            'message' => 'Admin registered successfully',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function marchantregister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'business_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6|confirmed',
            'business_category' => 'required|in:salon_beauty,home_services,health,fitness_pro_gym,others',
            'plan_id' => 'required|exists:plans,id',
            'number_of_branches' => 'nullable|integer',
            'address' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $plan = Plan::find($request->plan_id);
        $rawSubdomain = Str::before($request->email, '@');
        $subdomain = Str::slug($rawSubdomain);

        if (User::where('website_domain', $subdomain)->exists()) {
            return response()->json(['status' => false, 'message' => 'This subdomain is already taken.'], 422);
        }

        // --- CASE 1: FREE PLAN (ID = 1) ---
        if ($plan->id == 1) {
            DB::beginTransaction();
            try {
                $merchant = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'type' => 2,
                    'password' => Hash::make($request->password),
                    'business_category' => $request->business_category,
                    'number_of_branches' => $request->number_of_branches,
                    'address' => $request->address,
                    'business_name' => $request->business_name,
                    'website_domain' => $subdomain,
                ]);

                $subscription = Subscription::create([
                    'user_id' => $merchant->id,
                    'plan_id' => $plan->id,
                    'starts_at' => now(),
                    'ends_at' => now()->addDays(7),
                    'status' => 'active',
                    'auto_renew' => 0,
                ]);

                Payment::create([
                    'user_id' => $merchant->id,
                    'subscription_id' => $subscription->id,
                    'amount' => 0,
                    'currency' => 'SAR',
                    'payment_method' => 'free',
                    'transaction_id' => Str::uuid(),
                    'status' => 'paid',
                ]);

                DB::commit();
                $token = auth('api')->login($merchant);

                return response()->json(['success' => true, 'message' => 'Register is successfull', 'token' => $token], 201);
            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
        }

        // --- CASE 2: PAID PLAN (ID = 2, 3) ---
        $tapSetting = DB::table('settings')->latest()->first();
        if (! $tapSetting || ! $tapSetting->tap_secret_key) {
            return response()->json(['success' => false, 'message' => 'Payment config missing'], 422);
        }

        $tapResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $tapSetting->tap_secret_key,
            'Content-Type' => 'application/json',
        ])->post('https://api.tap.company/v2/charges', [
            'amount' => $plan->price,
            'currency' => 'SAR',
            'customer' => [
                'first_name' => $request->name,
                'email' => $request->email,
                'phone' => ['country_code' => '966', 'number' => $request->phone],
            ],
            'source' => ['id' => 'src_all'],
            'redirect' => [
                'url' => url('/api/tap-successregister'),
            ],

            'metadata' => [
                'udf1' => $request->name,
                'udf2' => $request->email,
                'udf3' => $request->phone,
                'udf4' => $request->password,
                'business_name' => $request->business_name,
                'business_category' => $request->business_category,
                'plan_id' => $plan->id,
                'subdomain' => $subdomain,
                'address' => $request->address,
                'branches' => $request->number_of_branches,
            ],
        ]);

        if ($tapResponse->failed()) {
            return response()->json(['success' => false, 'message' => 'Payment creation failed'], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Redirect to payment',
            'tap_payment_url' => $tapResponse->json()['transaction']['url'],
        ], 201);
    }

    public function tapSuccessregister(Request $request)
    {
        $chargeId = $request->tap_id;
        $tapSetting = DB::table('settings')->latest()->first();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $tapSetting->tap_secret_key,
        ])->get("https://api.tap.company/v2/charges/$chargeId");

        $data = $response->json();

        if ($data['status'] === 'CAPTURED') {
            $meta = $data['metadata'];

            DB::beginTransaction();
            try {

                $merchant = User::create([
                    'name' => $meta['udf1'],
                    'email' => $meta['udf2'],
                    'phone' => $meta['udf3'],
                    'type' => 2,
                    'password' => Hash::make($meta['udf4']),
                    'business_name' => $meta['business_name'],
                    'business_category' => $meta['business_category'],
                    'website_domain' => $meta['subdomain'],
                    'address' => $meta['address'] ?? null,
                    'number_of_branches' => $meta['branches'] ?? null,
                ]);

                $endDate = ($meta['plan_id'] == 2) ? now()->addMonth() : now()->addYear();

                $subscription = Subscription::create([
                    'user_id' => $merchant->id,
                    'plan_id' => $meta['plan_id'],
                    'starts_at' => now(),
                    'ends_at' => $endDate,
                    'status' => 'active',
                    'auto_renew' => 0,
                ]);

                Payment::create([
                    'user_id' => $merchant->id,
                    'subscription_id' => $subscription->id,
                    'amount' => $data['amount'],
                    'currency' => 'SAR',
                    'payment_method' => 'tap',
                    'transaction_id' => $chargeId,
                    'status' => 'paid',
                ]);

                DB::commit();

                Mail::to($merchant->email)->send(new PaymentCompletedMail($merchant));

                $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000') . "/create-account?user_id=" . $merchant->id;

                return redirect()->away($frontendUrl);
            } catch (\Exception $e) {
                DB::rollBack();

                $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000') . "/registration-failed?user_id=" . $merchant->id;

                return redirect()->away($frontendUrl);
            }
        }

        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000') . "/registration-failed?user_id=" . $chargeId;
        return redirect()->away($frontendUrl);
    }

    public function getPaymentStatus($user_id)
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        $payment = Payment::where('user_id', $user_id)
            ->latest()
            ->first();

        if (!$payment) {
            return response()->json(['status' => false, 'message' => 'No payment found for this user'], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $payment,
        ]);
    }

    public function getStoreDetails($subdomain)
    {
        $merchant = User::where('website_domain', $subdomain)->firstOrFail();

        return response()->json([
            'status' => true,
            'data' => [
                'name' => $merchant->name,
                'category' => $merchant->business_category,
                'phone' => $merchant->phone,
            ],
        ]);
    }

    public function edit($id)
    {

        $user = User::find($id);
        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        if ($user->type != 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'admin' => $user,
        ], 200);
    }

    public function adminUpdate(Request $request, $id)
    {
        $user = User::where('id', $id)->where('type', 1)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Admin not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $user->id,
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'required|in:0,1',
            // 'role' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->hasFile('image')) {
            if ($user->image && file_exists(public_path($user->image))) {
                unlink(public_path($user->image));
            }

            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('user'), $imageName);
            $user->image = 'user/' . $imageName;
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->status = $request->status;
        // $user->role = $request->role;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        $role = Role::where('id', $request->role)
            ->where('guard_name', 'api')
            ->first();

        // if (! $role) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Role not found for this guard',
        //     ], 422);
        // }

        // $user->syncRoles([$role->name]);

        return response()->json([
            'success' => true,
            'message' => 'Admin updated successfully',
            'user' => $user,
        ], 200);
    }

    // public function delete($id)
    // {

    //     $user = User::where('id', $id)->where('type', 1)->first();

    //     if (! $user) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Admin not found',
    //         ], 404);
    //     }

    //     if ($user->image && file_exists(public_path($user->image))) {
    //         unlink(public_path($user->image));
    //     }

    //     $user->syncRoles([]);

    //     $user->delete();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Admin deleted successfully',
    //     ], 200);
    // }

    public function logout()
    {
        $user = Auth::guard('api')->user();

        if ($user && $user->jwt_token) {
            JWTAuth::setToken($user->jwt_token)->invalidate();
            $user->update(['jwt_token' => null]);
        }

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function password($id)
    {
        $admin = User::where('type', 1)->find($id);

        if (! $admin) {
            return response()->json([
                'status' => false,
                'message' => 'Admin not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $admin,
        ], 200);
    }

    public function passwordchange(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $admin = User::find($id);

        if (! $admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin not found',
            ], 404);
        }

        if (! Hash::check($request->current_password, $admin->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
            ], 400);
        }

        $admin->password = Hash::make($request->new_password);
        $admin->save();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
        ], 200);
    }

    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if ($user->type == 1) {
            return response()->json([
                'success' => false,
                'message' => 'Admin cannot reset password via OTP. Please change password from dashboard.',
            ], 403);
        }

        $otp = rand(100000, 999999);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'otp' => Hash::make($otp),
                'expires_at' => now()->addMinutes(5),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        Mail::raw(
            "Your password reset OTP is: {$otp}. It will expire in 5 minutes.",
            function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Password Reset OTP');
            }
        );

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to your email successfully',
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:password_resets,email',
            'otp' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $record = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (! $record) {
            return response()->json(['message' => 'OTP not found'], 404);
        }

        if (now()->gt($record->expires_at)) {
            return response()->json(['message' => 'OTP expired'], 400);
        }

        if (! Hash::check($request->otp, $record->otp)) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully',
        ]);
    }

    // public function resetPasswordWithOtp(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'email' => 'required|email|exists:users,email',
    //         'otp' => 'required',
    //         'password' => 'required|min:6|confirmed',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed',
    //             'errors' => $validator->errors(),
    //         ], 422);
    //     }

    //     $record = DB::table('password_resets')
    //         ->where('email', $request->email)
    //         ->first();

    //     if (! $record || now()->gt($record->expires_at)) {
    //         return response()->json(['message' => 'OTP expired or invalid'], 400);
    //     }

    //     if (! Hash::check($request->otp, $record->otp)) {
    //         return response()->json(['message' => 'Invalid OTP'], 400);
    //     }

    //     $user = User::where('email', $request->email)->first();
    //     $user->password = Hash::make($request->password);
    //     $user->save();

    //     DB::table('password_resets')->where('email', $request->email)->delete();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Password reset successfully',
    //     ]);
    // }

    public function resetPasswordWithOtp(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6|confirmed', // 'password_confirmation' must be sent
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully',
        ]);
    }

    public function profileInfo()
    {
        $user = auth()->user();

        return response()->json([
            'success' => true,
            'data' => $user->only(['name', 'image', 'email', 'phone', 'address']),
        ], 200);
    }

    public function saveInfo(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $user->id,
            'address' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = [];

        if ($request->filled('name')) {
            $data['name'] = $request->name;
        }

        if ($request->filled('email')) {
            $data['email'] = $request->email;
        }

        if ($request->filled('phone')) {
            $data['phone'] = $request->phone;
        }

        if ($request->filled('address')) {
            $data['address'] = $request->address;
        }

        if ($request->hasFile('image')) {
            if ($user->image && file_exists(public_path($user->image))) {
                unlink(public_path($user->image));
            }

            $imageName = time() . '_' . $request->image->getClientOriginalName();

            $request->image->move(public_path('uploads/users'), $imageName);

            $data['image'] = 'uploads/users/' . $imageName;
        }

        if (! empty($data)) {
            $user->update($data);
        }

        return response()->json([
            'success' => true,
            'message' => 'Personal information updated successfully',
            'data' => $user->fresh()->only(['name', 'image', 'phone', 'address', 'email']),
        ], 200);
    }





}
