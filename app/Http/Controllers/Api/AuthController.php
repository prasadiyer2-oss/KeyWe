<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Get the authenticated user.
     */
    public function me(Request $request)
    {
        $user = $request->user();
        
        // Load roles to ensure we send the slug (e.g., "builder" or "admin")
        $user->load('roles');

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            // Get the first role slug, or default to 'user' if none exists
            'role' => $user->roles->first()?->slug ?? 'user',
            'verification_status' => $user->verification_status,
            'avatar' => $user->profile_image ?? null, // Example if you have an avatar
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200); 
    }

    public function register(Request $request)
    {
        // 1. Validate Input
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|digits:10|unique:users,phone',
            'password' => 'required|string|confirmed|min:6', // Expects 'password_confirmation' field
        ]);

        // 2. Generate OTP (Random 6 digits)
        $otp = 123456;

        // 3. Create User (Unverified)
        $user = \App\Models\User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'phone' => $fields['phone'],
            'password' => bcrypt($fields['password']),
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10), // Valid for 10 mins
            'is_phone_verified' => false,
        ]);

        // 4. SEND OTP (Simulated for Testing)
        // In production, replace this line with: SmsService::send($user->phone, $otp);
        \Illuminate\Support\Facades\Log::info(" [OTP SERVICE] Sending OTP to {$user->phone}: {$otp}");

        return response()->json([
            'message' => 'User registered. OTP sent to mobile.',
            'phone' => $user->phone,
            // 'dev_otp' => $otp // Uncomment this if you want to see OTP in response for easier testing
        ], 201);
    }

    /**
     * Verify OTP and Login
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|string',
        ]);

        // 1. Find User
        $user = \App\Models\User::where('phone', $request->phone)->first();

        // 2. Check if user exists and OTP matches
        if (!$user || $user->otp_code !== $request->otp) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        // 3. Check Expiry
        if (now()->greaterThan($user->otp_expires_at)) {
            return response()->json(['message' => 'OTP Expired. Request a new one.'], 400);
        }

        // 4. Verification Success
        $user->update([
            'is_phone_verified' => true,
            'otp_code' => null,       // Clear OTP
            'otp_expires_at' => null, // Clear Expiry
            'verification_status' => 'verified' // Optional: If you use this for builders
        ]);

        // 5. Login User & Generate Token
        $token = $user->createToken('mobile-login')->plainTextToken;

        return response()->json([
            'message' => 'Phone verified successfully. Logged in.',
            'token' => $token,
            'user' => $user,
        ]);
    }

}