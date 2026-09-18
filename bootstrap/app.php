<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\SanitizeInput::class,
            \App\Http\Middleware\ShareSiteData::class,
            \App\Http\Middleware\CheckMaintenanceMode::class,
            \App\Http\Middleware\SecurityHeaders::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
        ]);

        $middleware->alias([
            'role'        => \App\Http\Middleware\EnsureUserHasRole::class,
            'permission'  => \App\Http\Middleware\EnsureUserCanManage::class,
            'admin'       => \App\Http\Middleware\RedirectIfNotAdmin::class,
            'guest.admin' => \App\Http\Middleware\RedirectIfAdmin::class,
            'mc.publish'  => \App\Http\Middleware\PublishScheduledContent::class,
            'mc.trackview'=> \App\Http\Middleware\TrackPostView::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->booting(function () {
        /* ===== রেট লিমিটিং (Anti-Spam / Brute-force protection) ===== */

        // অ্যাডমিন লগইন ব্রুট-ফোর্স প্রতিরোধ
        RateLimiter::for('mc_login', function (Request $request) {
            $key = strtolower((string) $request->input('email')).'|'.$request->ip();

            return Limit::perMinutes((int) env('MC_LOGIN_DECAY_MINUTES', 10), (int) env('MC_LOGIN_MAX_ATTEMPTS', 5))
                ->by($key)
                ->response(fn () => response()->view('errors.rate-limit', [
                    'message' => 'অনেকবার ব্যর্থ চেষ্টার কারণে লগইন সাময়িকভাবে বন্ধ। কিছুক্ষণ পর আবার চেষ্টা করুন।',
                ], 429));
        });

        // পাবলিক কমেন্ট স্প্যাম প্রতিরোধ (IP + ফিঙ্গারপ্রিন্ট ভিত্তিক)
        RateLimiter::for('mc_comment', function (Request $request) {
            return Limit::perMinute((int) env('MC_COMMENT_THROTTLE_PER_MINUTE', 3))
                ->by($request->ip().'|'.substr((string) $request->userAgent(), 0, 60))
                ->response(fn () => response()->json([
                    'message' => 'অনেক দ্রুত মন্তব্য পাঠানো হচ্ছে। একটু পরে আবার চেষ্টা করুন।',
                ], 429));
        });

        // সাধারণ পাবলিক ট্রাফিক
        RateLimiter::for('global', fn (Request $request) => Limit::perMinute(300)->by($request->ip()));
    })->create();
