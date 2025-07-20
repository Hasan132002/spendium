<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users',
            'username' => 'required|unique:users',
            'password' => 'required|min:6|confirmed',
            'role'     => 'required|in:father,mother,child',
            'image'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', $validator->errors(), 400);
        }

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('profile_images', 'public')
            : null;

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'image'    => $imagePath,
        ]);

        $user->assignRole($request->role);

        $token = JWTAuth::fromUser($user);

        return $this->success('User registered successfully', [
            'token' => $token,
            'user'  => $user
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return $this->error('Invalid credentials', null, 400);
        }

        $user = auth()->user();

        return $this->success('Login successful', [
            'token' => $token,
            'user' => [
                'id'       => $user->id,
                'name'     => $user->name,
                'email'    => $user->email,
                'username' => $user->username,
                'role'     => $user->role,
                'image'    => $user->image ? asset('storage/' . $user->image) : null,
            ]
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->error('Email not found', null, 404);
        }

        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->otp_expires_at = now()->addMinutes(10);
        $user->save();

        Log::info('OTP for ' . $user->email . ': ' . $otp);

        return $this->success('OTP sent successfully', [
            'user' => $user->only(['id', 'email']),
            'otp'  => $otp
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|numeric'
        ]);

        $user = User::where('email', $request->email)
            ->where('otp', $request->otp)
            ->where('otp_expires_at', '>', now())
            ->first();

        if (!$user) {
            return $this->error('Invalid or expired OTP');
        }

        return $this->success('OTP verified successfully');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'otp'      => 'required',
            'password' => 'required|min:6|confirmed'
        ]);

        $user = User::where('email', $request->email)
            ->where('otp', $request->otp)
            ->where('otp_expires_at', '>', now())
            ->first();

        if (!$user) {
            return $this->error('Invalid or expired OTP');
        }

        $user->password = Hash::make($request->password);
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        return $this->success('Password reset successfully');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return $this->error('Old password incorrect');
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return $this->success('Password changed successfully');
    }

    public function profile()
    {
        $user = auth()->user();
        $user->load('roles', 'permissions');

        return $this->success('Profile fetched', [
            'user'        => $user,
            'roles'       => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name')
        ]);
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return $this->success('Logged out successfully');
    }
    public function getNonFatherUsers()
{

    $users = User::where('role', '!=', 'father')->get();

    return response()->json([
        'success' => true,
        'message' => 'Users fetched successfully',
        'data' => $users
    ]);
}
}
