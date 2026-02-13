<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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

    public function register(RegisterRequest $request)
    {
        Log::info('Received registration request', $request->all());    

        $fields = $request->validated();

        $otp = 123456;

        $userData = [
            'name' => $fields['name'] ?? null,
            'email' => $fields['email'] ?? null,
            'phone' => $fields['phone'],
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
            'is_phone_verified' => false,
        ];

        // FIX: Check if password exists in the array before accessing it
        if (!empty($fields['password'])) {
            $userData['password'] = bcrypt($fields['password']);
        }

        $user = \App\Models\User::create($userData);

        \Illuminate\Support\Facades\Log::info(" [OTP SERVICE] Sending OTP to {$user->phone}: {$otp}");

        return response()->json([
            'message' => 'User registered. OTP sent to mobile.',
            'phone' => $user->phone,
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

    public function login(LoginRequest $request)
    {
        // 1. Retrieve validated data (automatically checked by LoginRequest)
        $validated = $request->validated();

        // 2. Find User
        $user = User::where('email', $validated['email'])->first();

        // 3. Check Password
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // 4. Generate Token
        // Deletes old tokens so only one device is logged in (Optional security step)
        $user->tokens()->delete(); 
        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ]);
    }

}