<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Permission-ভিত্তিক অ্যাক্সেস কন্ট্রোল (Role অনুযায়ী) */
class EnsureUserCanManage
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active || ! $user->can_manage($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'অনুমতি নেই'], 403);
            }
            abort(403, 'এই কাজটি করার অনুমতি আপনার নেই।');
        }

        return $next($request);
    }
}
