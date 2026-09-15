<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ভিউ কাউন্টার — একই সেশনে একই সংবাদ বারবার গুনা হয় না।
 * ক্যাশ-ভিত্তিক থ্রটলিং রাখায় ডাটাবেসে অতিরিক্ত চাপ পড়ে না।
 */
class TrackPostView
{
    public function handle(Request $request, Closure $next): Response
    {
        $post = $request->route('post') ?? $request->route('news');

        if ($post && $post->id) {
            $key = 'viewed_post_'.$post->id;

            if (! $request->session()->has($key)) {
                $request->session()->put($key, now()->toDateTimeString());
                $post->incrementViews();
            }
        }

        return $next($request);
    }
}
