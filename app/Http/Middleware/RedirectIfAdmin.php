<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** ইতিমধ্যে লগইন হওয়া অ্যাডমিনকে প্যানেলে পাঠায় */
class RedirectIfAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->is_active) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
