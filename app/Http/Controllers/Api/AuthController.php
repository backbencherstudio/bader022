<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Hash, Mail, Validator};
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\User;
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

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $user = Auth::guard('api')->user();
        if ($user->type == 0) {
            $role = 'User';
        } elseif ($user->type == 1) {
            $role = 'Admin';
        } elseif ($user->type == 2) {
            $role = 'Merchant';
        } else {
            return response()->json(['error' => 'Invalid user type'], 403);
        }

        if ($user->jwt_token) {
            try {
                JWTAuth::setToken($user->jwt_token)->invalidate();
            } catch (\Exception $e) {
            }
        }

        $user->update(['jwt_token' => $token]);

        return response()->json([
            'success' => true,
            'message' => $role.' login successfully',
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
            $imageName = time().'_'.Str::random(10).'.'.$image->getClientOriginalExtension();
            $image->move(public_path('user'), $imageName);

            $imagePath = 'user/'.$imageName;
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
            $imageName = time().'_'.Str::random(10).'.'.$image->getClientOriginalExtension();
            $image->move(public_path('user'), $imageName);
            $imagePath = 'user/'.$imageName;
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
    //         'email' => 'required|email|unique:users,email',
    //         'phone' => 'required|string|max:20|unique:users,phone',
    //         'password' => 'required|string|min:6|confirmed',
    //         'business_category' => 'required|string|in:salon_beauty,home_services,health,fitness_pro_gym,others',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'errors' => $validator->errors(),
    //         ], 422);
    //     }
    //     $subdomain = Str::before($request->email, '@');
    //     $merchant = User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'phone' => $request->phone,
    //         'type' => 2,
    //         'password' => Hash::make($request->password),
    //         'business_category' => $request->business_category,
    //         'website_domain' => $subdomain,
    //     ]);

    //     $token = Auth::guard('api')->login($merchant);

    //     $merchant->update([
    //         'jwt_token' => $token,
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Merchant registered successfully',
    //         'data' => $merchant,
    //         'token' => $token,
    //     ], 201);
    // }
    public function marchantregister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'business_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6|confirmed',
            'business_category' => 'required|in:salon_beauty,home_services,health,fitness_pro_gym,others',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }


        $rawSubdomain = Str::before($request->email, '@');
        $subdomain = Str::slug($rawSubdomain);

        if (User::where('website_domain', $subdomain)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'This subdomain is already taken.',
            ], 422);
        }

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

        $token = Auth::guard('api')->attempt([
            'email' => $request->email,
            'password' => $request->password,
        ]);

        $merchant->update([
            'jwt_token' => $token,
        ]);

        return response()->json([
            'data' => $merchant->makeHidden(['password', 'jwt_token']),
            'success' => true,
            'message' => 'Merchant registered successfully',
            'domain' => $subdomain.'.devlaro.com',
            'token' => $token,

        ], 201);
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
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20|unique:users,phone,'.$user->id,
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
            $imageName = time().'_'.Str::random(10).'.'.$image->getClientOriginalExtension();
            $image->move(public_path('user'), $imageName);
            $user->image = 'user/'.$imageName;
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

    public function resetPasswordWithOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required',
            'password' => 'required|min:6|confirmed',
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

        if (! $record || now()->gt($record->expires_at)) {
            return response()->json(['message' => 'OTP expired or invalid'], 400);
        }

        if (! Hash::check($request->otp, $record->otp)) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_resets')->where('email', $request->email)->delete();

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
            'email' => 'nullable|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20|unique:users,phone,'.$user->id,
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

            $imageName = time().'_'.$request->image->getClientOriginalName();

            $request->image->move(public_path('uploads/users'), $imageName);

            $data['image'] = 'uploads/users/'.$imageName;
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
