<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** লগইন না করা ব্যবহারকারীকে অ্যাডমিন লগইনে পাঠায় */
class RedirectIfNotAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            if ($user) {
                auth()->logout();
            }

            return redirect()->route('admin.login')
                ->with('error', 'অ্যাডমিন প্যানেলে প্রবেশের জন্য লগইন করুন।');
        }

        return $next($request);
    }
}
