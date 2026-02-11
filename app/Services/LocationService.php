<?php

namespace App\Services;

use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;
use Illuminate\Support\Facades\Log;

class LocationService
{
    /**
     * Detect the user's city from their IP address.
     * Returns null if location cannot be determined.
     */
    // <--- Import Log

    public function detectCity(Request $request): ?string
    {
        // 1. Try to get the real IP from Headers (vital for Ngrok/Cloudflare)
        $ip = $request->header('X-Forwarded-For') ?? $request->ip();

        // 2. Handle multiple IPs (take the first one, which is the client)
        if (str_contains($ip, ',')) {
            $ip = explode(',', $ip)[0];
        }

        $ip = trim($ip);

        // 3. Localhost Fallback (Keep this for local dev testing)
        // 103.48.198.141 is a generic Mumbai IP
        if ($ip === '127.0.0.1' || $ip === '::1') {
            $ip = '103.48.198.141';
        }

        // 4. Get Position
        $position = Location::get($ip);

        return $position ? $position->cityName : null;
    }
}