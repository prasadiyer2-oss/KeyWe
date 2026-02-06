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

}