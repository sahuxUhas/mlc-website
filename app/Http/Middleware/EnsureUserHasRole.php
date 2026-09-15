<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** নির্দিষ্ট রোল ছাড়া অ্যাডমিন এলাকায় প্রবেশ বন্ধ রাখে */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active || ! $user->hasRole(...$roles)) {
            abort(403, 'এই অংশে আপনার প্রবেশাধিকার নেই।');
        }

        return $next($request);
    }
}
