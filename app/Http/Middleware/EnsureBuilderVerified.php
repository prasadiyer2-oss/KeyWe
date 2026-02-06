<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureBuilderVerified
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        // 1. If user is not logged in, proceed (let normal auth handle it)
        if (!$user) {
            return $next($request);
        }

        // 2. CHECK: Is this user a Builder?
        if ($user->inRole('builder')) {
            // 3. CHECK: Is their status anything other than 'verified'?
            // This covers 'pending', 'rejected', etc.
            if ($user->verification_status !== 'verified') {

                Auth::logout(); // Kick them out

                return redirect()->route('builder.thankyou')
                    ->with('error', 'Your account is pending verification.');
            }
        }

        // Normal users (status is NULL) or Verified Builders pass here
        return $next($request);
    }
}