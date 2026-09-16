<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * প্রোডাকশন সিকিউরিটি হেডার — প্রতিটি রেসপন্সে যুক্ত হয়।
 * (.htaccess এও একই হেডার আছে; ফ্রেমওয়ার্ক লেভেলটি ফলব্যাক হিসেবে কাজ করে,
 *  যেমন `php artisan serve` বা nginx এ ডিপ্লয় করলে।)
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options'        => 'SAMEORIGIN',
            'Referrer-Policy'        => 'strict-origin-when-cross-origin',
            'Permissions-Policy'     => 'camera=(), microphone=(), geolocation=()',
            'X-XSS-Protection'       => '1; mode=block',
        ];

        foreach ($headers as $key => $value) {
            if (! $response->headers->has($key)) {
                $response->headers->set($key, $value);
            }
        }

        return $response;
    }
}
