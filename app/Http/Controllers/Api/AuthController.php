<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PaymentCompletedMail;
use App\Models\BusinessHour;
use App\Models\MerchantSetting;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\TapPayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserRegiMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;

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



    // public function login(Request $request)
    // {
    //     $credentials = $request->only('email', 'password');


    //     if (!$token = Auth::guard('api')->attempt($credentials)) {
    //         return response()->json(['error' => 'Invalid credentials'], 401);
    //     }

    //     $user = Auth::guard('api')->user();

    //     if ($user->type == 2) {
    //         $subscription = $user->subscription;
    //         if (!$subscription || $subscription->status == 'expired' || $subscription->ends_at < now()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Your subscription has expired. Please renew to login.',
    //                 'data' => null,
    //             ], 403);
    //         }
    //     }

    //     $roles = [0 => 'User', 1 => 'Admin', 2 => 'Merchant'];
    //     $role = $roles[$user->type] ?? null;

    //     if (!$role) {
    //         return response()->json(['success' => false, 'message' => 'Invalid user type'], 403);
    //     }

    //     $needsOtp = false;
    //     $clientRememberToken = $request->header('Remember-Token');

    //     if ($user->type == 1) {
    //         $needsOtp = true;
    //     } else {

    //         if (!$user->remember_token || $user->remember_token !== $clientRememberToken || $user->updated_at < now()->subDays(30)) {
    //             $needsOtp = true;
    //         }
    //     }

    //     if ($needsOtp) {
    //         $otp = rand(100000, 999999);
    //         $user->update([
    //             'otp' => $otp,
    //             'otp_expires_at' => now()->addMinutes(5),
    //         ]);

    //         try {

    //             Mail::send('emails.login_otp', ['otp' => $otp], function ($message) use ($user) {
    //                 $message->to($user->email)->subject('Login OTP Verification');
    //             });

    //         } catch (\Exception $e) {
    //             return response()->json(['success' => false, 'message' => 'Could not send OTP. Please try again.'], 500);
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'otp_required' => true,
    //             'message' => 'OTP sent to your email successfully.',
    //             'email' => $user->email,
    //         ]);
    //     }

    //     $hasMiniSiteMenu = false;
    //     if ($user->type == 2) {
    //         $plan = Subscription::where('user_id', $user->id)->latest()->first();
    //         $hasMiniSiteMenu = !($plan && $plan->plan_id == 1);
    //     }
    //     if ($user->jwt_token) {
    //         try {
    //             \JWTAuth::setToken($user->jwt_token)->invalidate();
    //         } catch (\Exception $e) {}
    //     }
    //     $newRememberToken = \Str::random(60);
    //     $user->setRememberToken($newRememberToken);
    //     $user->jwt_token = $token;
    //     $user->save();

    //     return response()->json([
    //         'success' => true,
    //         'message' => $role . ' login successfully',
    //         'data' => [
    //             'user' => $user,
    //             'user_type' => $role,
    //             'has_mini_site_menu' => $hasMiniSiteMenu,
    //             'remember_token' => $newRememberToken,
    //         ],
    //         'token' => $token,
    //     ]);
    // }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $user = Auth::guard('api')->user();

        if ($user->type == 2) {
            $subscription = $user->subscription;
            if (!$subscription || $subscription->status == 'expired' || $subscription->ends_at < now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your subscription has expired. Please renew to login.',
                    'data' => null,
                ], 403);
            }
        }

        $roles = [0 => 'User', 1 => 'Admin', 2 => 'Merchant'];
        $role = $roles[$user->type] ?? null;

        if (!$role) {
            return response()->json(['success' => false, 'message' => 'Invalid user type'], 403);
        }


        $needsOtp = false;
        $clientRememberToken = $request->header('Remember-Token');

        if ($user->type == 1) {

            $needsOtp = true;
        } else {

            if (!$user->remember_token || $user->remember_token !== $clientRememberToken || $user->updated_at < now()->subDays(30)) {
                $needsOtp = true;
            }
        }

        if ($needsOtp) {
            $otp = rand(100000, 999999);
            $user->update([
                'otp' => $otp,
                'otp_expires_at' => now()->addMinutes(5),
            ]);

            try {
                Mail::send('emails.login_otp', ['otp' => $otp], function ($message) use ($user) {
                    $message->to($user->email)->subject('Login OTP Verification');
                });
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Could not send OTP.'], 500);
            }

            return response()->json([
                'success' => true,
                'otp_required' => true,
                'message' => 'OTP sent to your email successfully.',
                'email' => $user->email,
            ]);
        }

        $hasMiniSiteMenu = false;
        if ($user->type == 2) {
            $plan = Subscription::where('user_id', $user->id)->latest()->first();
            $hasMiniSiteMenu = !($plan && $plan->plan_id == 1);
        }

        if ($user->jwt_token) {
            try {
                \JWTAuth::setToken($user->jwt_token)->invalidate();
            } catch (\Exception $e) {
            }
        }

        if (!$user->remember_token) {
            $user->remember_token = \Str::random(60);
        }

        $user->jwt_token = $token;


        $user->timestamps = false;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => $role . ' login successfully',
            'data' => [
                'user' => $user,
                'user_type' => $role,
                'has_mini_site_menu' => $hasMiniSiteMenu,
                'remember_token' => $user->remember_token,
            ],
            'token' => $token,
        ]);
    }

    public function loginOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|numeric',
        ]);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        if (!$user->otp || (int)$user->otp !== (int)$request->otp) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP'], 401);
        }
        if ($user->otp_expires_at < now()) {
            return response()->json(['success' => false, 'message' => 'OTP has expired'], 401);
        }
        $token = Auth::guard('api')->fromUser($user);

        if ($user->jwt_token) {
            try {
                \JWTAuth::setToken($user->jwt_token)->invalidate();
            } catch (\Exception $e) {
            }
        }

        $roles = [0 => 'User', 1 => 'Admin', 2 => 'Merchant'];
        $role = $roles[$user->type] ?? 'User';
        $hasMiniSiteMenu = false;
        if ($user->type == 2) {
            $plan = Subscription::where('user_id', $user->id)->latest()->first();
            $hasMiniSiteMenu = !($plan && $plan->plan_id == 1);
        }
        $newRememberToken = \Str::random(60);

        $user->update([
            'otp' => null,
            'otp_expires_at' => null,
            'jwt_token' => $token,
            'remember_token' => $newRememberToken,
        ]);

        return response()->json([
            'success' => true,
            'message' => $role . ' verified and logged in successfully',
            'data' => [
                'user' => $user,
                'user_type' => $role,
                'has_mini_site_menu' => $hasMiniSiteMenu,
                'remember_token' => $newRememberToken,
            ],
            'token' => $token,
        ]);
    }





    // public function register(Request $request)
    // {

    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|email|unique:users,email',
    //         'password' => 'required|string|min:6|confirmed',
    //         'phone' => 'nullable|string|max:20',
    //         'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'errors' => $validator->errors(),
    //         ], 422);
    //     }

    //     $imagePath = null;
    //     if ($request->hasFile('image')) {
    //         $image = $request->file('image');
    //         $imageName = time().'_'.Str::random(10).'.'.$image->getClientOriginalExtension();
    //         $image->move(public_path('user'), $imageName);

    //         $imagePath = 'user/'.$imageName;
    //     }

    //     $user = User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'phone' => $request->phone,
    //         'type' => 0,
    //         'image' => $imagePath,
    //         'password' => Hash::make($request->password),
    //     ]);

    //     Mail::to($user->email)->send(new UserRegiMail($user));

    //     $token = Auth::guard('api')->login($user);

    //     $user->update([
    //         'jwt_token' => $token,
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'User registered successfully',
    //         'data' => $user,
    //         'token' => $token,
    //     ], 201);
    // }

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
                'errors' => $validator->errors()
            ], 422);
        }

        $otp = rand(100000, 999999);
        $email = $request->email;

        // temp image save
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('temp_images', 'public');
        }

        // Store ALL data in cache (NO DB)
        Cache::put('register_' . $email, [
            'name' => $request->name,
            'email' => $email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'image' => $imagePath,
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(5),
        ], now()->addMinutes(5));

        try {
            Mail::send('emails.user_register_otp', ['otp' => $otp], function ($message) use ($email) {
                $message->to($email)->subject('Registration OTP Verification');
            });

            return response()->json([
                'success' => true,
                'message' => 'OTP sent. Please verify.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Mail failed: ' . $e->getMessage()
            ], 500);
        }
    }


    public function verifyRegisterOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required'
        ]);

        $data = Cache::get('register_' . $request->email);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'OTP expired or not found'
            ], 400);
        }

        if ($data['otp'] != $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP'
            ], 400);
        }

        if (now()->gt($data['otp_expires_at'])) {
            return response()->json([
                'success' => false,
                'message' => 'OTP expired'
            ], 400);
        }


        if (User::where('email', $data['email'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'User already exists'
            ], 409);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'],
            'image' => $data['image'],
            'email_verified_at' => now(),
        ]);

        Mail::to($user->email)->send(new UserRegiMail($user));

        $token = JWTAuth::fromUser($user);


        $user->jwt_token = hash('sha256', $token);
        $user->save();

        Cache::forget('register_' . $request->email);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',

        ]);
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

    // public function marchantregister(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string|max:255',
    //         'business_name' => 'required|string|max:255|unique:users,business_name',
    //         'email' => 'required|email|unique:users,email',
    //         'phone' => 'required|string|max:20|unique:users,phone',
    //         'password' => 'required|string|min:6|confirmed',
    //         'business_category' => 'required|string|max:255',
    //         'plan_id' => 'required|exists:plans,id',
    //         'number_of_branches' => 'nullable|integer',
    //         'address' => 'nullable|string|max:500',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
    //     }

    //     $plan = Plan::find($request->plan_id);
    //     $subdomain = strtolower(Str::slug($request->business_name, ''));

    //     if (empty($subdomain)) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Invalid business name for subdomain',
    //         ], 422);
    //     }

    //     if (User::where('website_domain', $subdomain)->exists()) {
    //         return response()->json(['status' => false, 'message' => 'This subdomain is already taken.'], 422);
    //     }

    //     if ($plan->id == 1) {
    //         DB::beginTransaction();
    //         try {
    //             $merchant = User::create([
    //                 'name' => $request->name,
    //                 'email' => $request->email,
    //                 'phone' => $request->phone,
    //                 'type' => 2,
    //                 'password' => Hash::make($request->password),
    //                 'business_category' => $request->business_category,
    //                 'number_of_branches' => $request->number_of_branches,
    //                 'address' => $request->address,
    //                 'business_name' => $request->business_name,
    //                 'website_domain' => $subdomain,
    //             ]);

    //             TapPayment::create([
    //                 'user_id' => $merchant->id,
    //                 'tap_mode' => 'test',
    //                 'tap_secret_key' => 'sk_test_XKokBfNWv6FIYuTMg5sLPjhJ',
    //                 'tap_public_key' => 'pk_test_EtHFV4BuPQokJT6jiROls87Y',
    //             ]);

    //             $subscription = Subscription::create([
    //                 'user_id' => $merchant->id,
    //                 'plan_id' => $plan->id,
    //                 'starts_at' => now(),
    //                 'ends_at' => now()->addDays(7),
    //                 'status' => 'active',
    //                 'auto_renew' => 0,
    //             ]);

    //             Payment::create([
    //                 'user_id' => $merchant->id,
    //                 'subscription_id' => $subscription->id,
    //                 'amount' => 0,
    //                 'currency' => 'SAR',
    //                 'payment_method' => 'free',
    //                 'transaction_id' => Str::uuid(),
    //                 'status' => 'paid',
    //             ]);

    //             $storeSetting = MerchantSetting::create([
    //                 'user_id' => $merchant->id,
    //                 'store_name' => $merchant->business_name,
    //                 'business_category' => $merchant->business_category,
    //                 'business_address' => $merchant->address ?? null,
    //                 'country' => 'Saudi Arabia',
    //                 'city' => 'Riyadh',
    //                 'time_zone' => 'Asia/Riyadh',
    //                 'currency' => 'SAR',
    //             ]);

    //             $defaultHours = [
    //                 'monday' => ['open' => '09:00', 'close' => '24:00'],
    //                 'tuesday' => ['open' => '09:00', 'close' => '24:00'],
    //                 'wednesday' => ['open' => '09:00', 'close' => '24:00'],
    //                 'thursday' => ['open' => '09:00', 'close' => '24:00'],
    //                 'friday' => ['open' => '13:00', 'close' => '24:00'],
    //                 'saturday' => ['open' => '09:00', 'close' => '24:00'],
    //                 'sunday' => ['open' => '09:00', 'close' => '24:00'],
    //             ];

    //             foreach ($defaultHours as $day => $time) {
    //                 BusinessHour::create([
    //                     'merchant_store_setting_id' => $storeSetting->id,
    //                     'day' => $day,
    //                     'open_time' => $time['open'],
    //                     'close_time' => $time['close'],
    //                     'is_closed' => 0,
    //                 ]);
    //             }

    //             DB::commit();
    //             $token = auth('api')->login($merchant);

    //             return response()->json(['success' => true, 'message' => 'Register is successfull', 'token' => $token], 201);
    //         } catch (\Exception $e) {
    //             DB::rollBack();

    //             return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    //         }
    //     }

    //     $tapSetting = DB::table('settings')->latest()->first();
    //     if (! $tapSetting || ! $tapSetting->tap_secret_key) {
    //         return response()->json(['success' => false, 'message' => 'Payment config missing'], 422);
    //     }

    //     $tapResponse = Http::withHeaders([
    //         'Authorization' => 'Bearer '.$tapSetting->tap_secret_key,
    //         'Content-Type' => 'application/json',
    //     ])->post('https://api.tap.company/v2/charges', [
    //         'amount' => $plan->price,
    //         'currency' => 'SAR',
    //         'customer' => [
    //             'first_name' => $request->name,
    //             'email' => $request->email,
    //             'phone' => ['country_code' => '966', 'number' => $request->phone],
    //         ],
    //         'source' => ['id' => 'src_all'],
    //         'redirect' => [
    //             'url' => url('/api/tap-successregister'),
    //         ],

    //         'metadata' => [
    //             'udf1' => $request->name,
    //             'udf2' => $request->email,
    //             'udf3' => $request->phone,
    //             'udf4' => $request->password,
    //             'business_name' => $request->business_name,
    //             'business_category' => $request->business_category,
    //             'plan_id' => $plan->id,
    //             'subdomain' => $subdomain,
    //             'address' => $request->address,
    //             'branches' => $request->number_of_branches,
    //         ],
    //     ]);

    //     if ($tapResponse->failed()) {
    //         return response()->json(['success' => false, 'message' => 'Payment creation failed'], 500);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Redirect to payment',
    //         'tap_payment_url' => $tapResponse->json()['transaction']['url'],
    //     ], 201);
    // }




    public function marchantregister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'business_name' => 'required|string|max:255|unique:users,business_name',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6|confirmed',
            'business_category' => 'required|string|max:255',
            'plan_id' => 'required|exists:plans,id',
            'number_of_branches' => 'nullable|integer',
            'address' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $otp = rand(100000, 999999);
        Cache::put('register_' . $request->email, [
            'data' => $request->all(),
            'otp' => $otp
        ], now()->addMinutes(5));

        Mail::send('emails.merchant_register_otp', ['otp' => $otp], function ($message) use ($request) {
            $message->to($request->email)
                ->subject('Your Registration OTP');
        });

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to email'
        ]);
    }

    public function verifyMerchantOtpAndRegister(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required'
        ]);

        $cached = Cache::get('register_' . $request->email);

        if (!$cached) {
            return response()->json([
                'success' => false,
                'message' => 'OTP expired'
            ], 400);
        }

        if ($cached['otp'] != $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP'
            ], 400);
        }

        $data = (object) $cached['data'];
        $plan = Plan::find($data->plan_id);
        $subdomain = strtolower(Str::slug($data->business_name, ''));

        Cache::forget('register_' . $request->email);

        if ($plan->id == 1) {

            DB::beginTransaction();
            try {
                $merchant = User::create([
                    'name' => $data->name,
                    'email' => $data->email,
                    'phone' => $data->phone,
                    'type' => 2,
                    'password' => Hash::make($data->password),
                    'business_category' => $data->business_category,
                    'number_of_branches' => $data->number_of_branches,
                    'address' => $data->address,
                    'business_name' => $data->business_name,
                    'website_domain' => $subdomain,
                ]);

                TapPayment::create([
                    'user_id' => $merchant->id,
                    'tap_mode' => 'test',
                    'tap_secret_key' => 'sk_test_XKokBfNWv6FIYuTMg5sLPjhJ',
                    'tap_public_key' => 'pk_test_EtHFV4BuPQokJT6jiROls87Y',
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

                $storeSetting = MerchantSetting::create([
                    'user_id' => $merchant->id,
                    'store_name' => $merchant->business_name,
                    'business_category' => $merchant->business_category,
                    'business_address' => $merchant->address ?? null,
                    'country' => 'Saudi Arabia',
                    'city' => 'Riyadh',
                    'time_zone' => 'Asia/Riyadh',
                    'currency' => 'SAR',
                ]);

                $defaultHours = [
                    'monday' => ['open' => '09:00', 'close' => '24:00'],
                    'tuesday' => ['open' => '09:00', 'close' => '24:00'],
                    'wednesday' => ['open' => '09:00', 'close' => '24:00'],
                    'thursday' => ['open' => '09:00', 'close' => '24:00'],
                    'friday' => ['open' => '13:00', 'close' => '24:00'],
                    'saturday' => ['open' => '09:00', 'close' => '24:00'],
                    'sunday' => ['open' => '09:00', 'close' => '24:00'],
                ];

                foreach ($defaultHours as $day => $time) {
                    BusinessHour::create([
                        'merchant_store_setting_id' => $storeSetting->id,
                        'day' => $day,
                        'open_time' => $time['open'],
                        'close_time' => $time['close'],
                        'is_closed' => 0,
                    ]);
                }

                DB::commit();

                $token = auth('api')->login($merchant);

                return response()->json([
                    'success' => true,
                    'message' => 'Registration successful',
                    'token' => $token
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
        }

        $tapSetting = DB::table('settings')->latest()->first();

        $tapResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $tapSetting->tap_secret_key,
        ])->post('https://api.tap.company/v2/charges', [
            'amount' => $plan->price,
            'currency' => 'SAR',
            'customer' => [
                'first_name' => $data->name,
                'email' => $data->email,
                'phone' => ['country_code' => '966', 'number' => $data->phone],
            ],
            'source' => ['id' => 'src_all'],
            'redirect' => [
                'url' => url('/api/tap-successregister'),
            ],
            'metadata' => [
                'udf1' => $data->name,
                'udf2' => $data->email,
                'udf3' => $data->phone,
                'udf4' => $data->password,
                'business_name' => $data->business_name,
                'business_category' => $data->business_category,
                'plan_id' => $plan->id,
                'subdomain' => $subdomain,
                'address' => $data->address,
                'branches' => $data->number_of_branches,
            ],
        ]);
        return response()->json([
            'success' => true,
            'tap_payment_url' => $tapResponse->json()['transaction']['url'],
        ]);
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

                TapPayment::create([
                    'user_id' => $merchant->id,
                    'tap_mode' => 'test',
                    'tap_secret_key' => 'sk_test_XKokBfNWv6FIYuTMg5sLPjhJ',
                    'tap_public_key' => 'pk_test_EtHFV4BuPQokJT6jiROls87Y',
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

                $storeSetting = MerchantSetting::create([
                    'user_id' => $merchant->id,
                    'store_name' => $merchant->business_name,
                    'business_category' => $merchant->business_category,
                    'business_address' => $merchant->address ?? null,
                    'country' => 'Saudi Arabia',
                    'city' => 'Riyadh',
                    'time_zone' => 'Asia/Riyadh',
                    'currency' => 'SAR',
                ]);

                $defaultHours = [
                    'monday' => ['open' => '09:00', 'close' => '24:00'],
                    'tuesday' => ['open' => '09:00', 'close' => '24:00'],
                    'wednesday' => ['open' => '09:00', 'close' => '24:00'],
                    'thursday' => ['open' => '09:00', 'close' => '24:00'],
                    'friday' => ['open' => '13:00', 'close' => '24:00'],
                    'saturday' => ['open' => '09:00', 'close' => '24:00'],
                    'sunday' => ['open' => '09:00', 'close' => '24:00'],
                ];

                foreach ($defaultHours as $day => $time) {
                    BusinessHour::create([
                        'merchant_store_setting_id' => $storeSetting->id,
                        'day' => $day,
                        'open_time' => $time['open'],
                        'close_time' => $time['close'],
                        'is_closed' => 0,
                    ]);
                }

                DB::commit();

                Mail::to($merchant->email)->send(new PaymentCompletedMail($merchant));


                $frontendUrl = env('FRONTEND_URL', 'https://bokli.io') . '/create-account?user_id=' . $merchant->id . '&website=' . $merchant->website_domain;

                return redirect()->away($frontendUrl);
            } catch (\Exception $e) {
                DB::rollBack();

                $frontendUrl = env('FRONTEND_URL', 'https://bokli.io') . '/booking-failed';

                return redirect()->away($frontendUrl);
            }
        }

        $frontendUrl = env('FRONTEND_URL', 'https://bokli.io') . '/booking-failed';

        return redirect()->away($frontendUrl);
    }

    public function getPaymentStatus($user_id)
    {
        $user = User::find($user_id);

        if (! $user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        $payment = Payment::where('user_id', $user_id)
            ->latest()
            ->first();

        if (! $payment) {
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

        Mail::send('emails.otp', ['otp' => $otp], function ($message) use ($request) {
            $message->to($request->email)
                ->subject('Password Reset OTP');
        });

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to your email successfully',
        ]);
    }


    public function resetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->type == 1) {
            return response()->json([
                'success' => false,
                'message' => 'Admin cannot reset password via OTP.',
            ], 403);
        }

        $email = $request->email;

        // ----------------------------
        // 1. 60 seconds cooldown check
        // ----------------------------
        if (Cache::has("otp_cooldown:$email")) {
            return response()->json([
                'success' => false,
                'message' => 'Please wait 60 seconds before requesting another OTP.',
            ], 429);
        }

        // ----------------------------
        // 2. 10 min limit (max 3 times)
        // ----------------------------
        $attemptKey = "otp_attempts:$email";
        $attempts = Cache::get($attemptKey, 0);

        if ($attempts >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'Too many OTP requests. Please try again after 10 minutes.',
            ], 429);
        }

        Cache::put($attemptKey, $attempts + 1, now()->addMinutes(10));

        // ----------------------------
        // cooldown 60 sec
        // ----------------------------
        Cache::put("otp_cooldown:$email", true, now()->addSeconds(60));

        // ----------------------------
        // generate new OTP
        // ----------------------------
        $otp = rand(100000, 999999);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $email],
            [
                'otp' => Hash::make($otp),
                'expires_at' => now()->addMinutes(5),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // send email
        Mail::send('emails.otp', ['otp' => $otp], function ($message) use ($email) {
            $message->to($email)->subject('Your New OTP Code');
        });

        return response()->json([
            'success' => true,
            'message' => 'New OTP sent successfully',
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

        // return
        return response()->json([
            'success' => true,
            'message' => 'Personal information updated successfully',
            'data' => $user->fresh()->only(['name', 'image', 'phone', 'address', 'email']),
        ], 200);
    }

    public function renew(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // --- Find user by email instead of auth ---
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        $plan = Plan::find($request->plan_id);

        // --- CASE 1: FREE PLAN ---
        if ($plan->id == 1) {
            DB::beginTransaction();
            try {
                $subscription = Subscription::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'plan_id' => $plan->id,
                        'starts_at' => now(),
                        'ends_at' => now()->addDays(7),
                        'status' => 'active',
                        'auto_renew' => 0,
                    ]
                );

                Payment::create([
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'amount' => 0,
                    'currency' => 'SAR',
                    'payment_method' => 'free',
                    'transaction_id' => Str::uuid(),
                    'status' => 'paid',
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Subscription renewed successfully',
                    'subscription' => $subscription,
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
        }

        // --- CASE 2: PAID PLAN ---
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
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => ['country_code' => '966', 'number' => $user->phone],
            ],
            'source' => ['id' => 'src_all'],
            'redirect' => [
                'url' => url('/api/tap-renew-success'),
            ],
            'metadata' => [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
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

    public function tapRenewSuccess(Request $request)
    {
        $tap_id = $request->input('tap_id');

        if (! $tap_id) {
            return response()->json(['success' => false, 'message' => 'Invalid payment ID'], 400);
        }

        $tapSetting = DB::table('settings')->latest()->first();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $tapSetting->tap_secret_key,
        ])->get("https://api.tap.company/v2/charges/{$tap_id}");

        $paymentData = $response->json();

        if ($response->successful() && $paymentData['status'] === 'CAPTURED') {

            $userId = $paymentData['metadata']['user_id'];
            $planId = $paymentData['metadata']['plan_id'];
            $plan = Plan::find($planId);

            DB::beginTransaction();
            try {

                $subscription = Subscription::updateOrCreate(
                    ['user_id' => $userId],
                    [
                        'plan_id' => $planId,
                        'starts_at' => now(),
                        'ends_at' => now()->addMonths(1),
                        'status' => 'active',
                        'auto_renew' => 1,
                    ]
                );

                Payment::create([
                    'user_id' => $userId,
                    'subscription_id' => $subscription->id,
                    'amount' => $paymentData['amount'],
                    'currency' => $paymentData['currency'],
                    'payment_method' => 'tap',
                    'transaction_id' => $tap_id,
                    'status' => 'paid',
                ]);

                DB::commit();

                $frontendUrl = env('FRONTEND_URL', 'https://bokli.io') . '/login';

                return redirect()->away($frontendUrl);
            } catch (\Exception $e) {
                DB::rollBack();

                $frontendUrl = env('FRONTEND_URL', 'https://bokli.io') . '/booking-failed';

                return redirect()->away($frontendUrl);
            }
        }

        $frontendUrl = env('FRONTEND_URL', 'https://bokli.io') . '/booking-failed';

        return redirect()->away($frontendUrl);
    }
}
