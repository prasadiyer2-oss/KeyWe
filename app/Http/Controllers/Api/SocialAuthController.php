<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    /**
     * Step 1: Frontend asks for the Redirect URL
     * GET /api/auth/{provider}/redirect
     */
    public function redirect($provider)
    {
        // We generate the URL for the frontend to redirect the user to
        $url = Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();
        
        return response()->json(['url' => $url]);
    }

    /**
     * Step 2: Google redirects back here with a 'code'
     * GET /api/auth/{provider}/callback
     */
    public function callback($provider)
    {
        try {
            // 1. Get User from Provider (Stateless is required for APIs)
            $socialUser = Socialite::driver($provider)->stateless()->user();
            
            // 2. Find or Create User in your DB
            $user = User::firstOrCreate(
                ['email' => $socialUser->getEmail()],
                [
                    'name' => $socialUser->getName(),
                    'password' => bcrypt(Str::random(16)), // Random secure password
                    'email_verified_at' => now(),
                ]
            );

            // 3. Assign Default Role (e.g., 'Buyer') if using Roles
            // $user->assignRole('buyer'); 

            // 4. Create API Token (Sanctum)
            $token = $user->createToken('social-login')->plainTextToken;

            // 5. Redirect BACK to Next.js Frontend with the token
            // The frontend will grab this token from the URL and save it.
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            
            return redirect()->to("$frontendUrl/auth/social/callback?token=$token");

        } catch (\Exception $e) {
            return redirect()->to(env('FRONTEND_URL') . '/login?error=Unable to login');
        }
    }
}