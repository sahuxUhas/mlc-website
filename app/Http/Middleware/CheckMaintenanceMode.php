<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * মেইনটেন্যান্স মোড — অ্যাডমিন প্যানেল → সাইট সেটিংস → আচরণ থেকে চালু/বন্ধ হয়।
 * চালু থাকলে লগ-ইন করা ব্যবহারকারী ছাড়া সবাই মেইনটেন্যান্স পেজ দেখবেন।
 */
class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->enabled() || $this->bypass($request)) {
            return $next($request);
        }

        return response()->view('errors.maintenance', [], 503)
            ->header('Retry-After', 3600);
    }

    /** সেটিংস/ডাটাবেস না থাকলে সাইট কখনো আটকাবে না */
    private function enabled(): bool
    {
        try {
            return mc_flag('site_maintenance', false);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function bypass(Request $request): bool
    {
        // অ্যাডমিন প্যানেল ও লগইন সবসময় খোলা থাকবে, না হলে মোড বন্ধ করা যাবে না
        if ($request->is('admin', 'admin/*')) {
            return true;
        }

        if ($request->user() !== null) {
            return true;
        }

        // হেলথ চেক ও স্ট্যাটিক asset
        return $request->is('up', 'storage/*', 'css/*', 'js/*', 'uploads/*', 'favicon.ico');
    }
}
