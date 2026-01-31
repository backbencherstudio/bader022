<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            'user' => $user,
            'user_type' => $role,
            'message' => $role.' login successfully',
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
            'user' => $user,
            'success' => true,
            'message' => 'User registered successfully',
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
            'role' => $request->role,
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
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6|confirmed',
            'business_category' => 'required|string|in:salon_beauty,home_services,health,fitness_gym,others',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $merchant = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'type' => 2,
            'password' => Hash::make($request->password),
            'business_category' => $request->business_category,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Merchant registered successfully',
            'data' => $merchant,
        ], 201);
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
            'role' => 'required|exists:roles,id',
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
        $user->role = $request->role;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        $role = Role::where('id', $request->role)
            ->where('guard_name', 'api')
            ->first();

        if (! $role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found for this guard',
            ], 422);
        }

        $user->syncRoles([$role->name]);

        return response()->json([
            'success' => true,
            'message' => 'Admin updated successfully',
            'user' => $user,
        ], 200);
    }

    public function delete($id)
    {

        $user = User::where('id', $id)->where('type', 1)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Admin not found',
            ], 404);
        }

        if ($user->image && file_exists(public_path($user->image))) {
            unlink(public_path($user->image));
        }

        $user->syncRoles([]);

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Admin deleted successfully',
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

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $admin = User::find($id);

        if (! $admin) {
            return response()->json([
                'status' => false,
                'message' => 'Admin not found',
            ], 404);
        }

        if (! Hash::check($request->current_password, $admin->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Current password is incorrect',
            ], 400);
        }
        $admin->password = Hash::make($request->new_password);
        $admin->save();

        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully',
        ], 200);
    }
}
