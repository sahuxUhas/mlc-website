<?php

namespace App\Http\Middleware;

use App\Models\Post;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ভিউ কাউন্টার — একই সেশনে একই সংবাদ বারবার গুনা হয় না।
 * রাউটে মডেল বাইন্ডিং ({slug}) ব্যবহার হওয়ায় এখানে স্লাগ থেকে মডেল খোঁজা হয়,
 * ফলে অতিরিক্ত কুয়েরি লাগে না (কন্ট্রোলার ইতিমধ্যে একই রেকর্ড লোড করে)।
 */
class TrackPostView
{
    public function handle(Request $request, Closure $next): Response
    {
        $this->countView($request);

        return $next($request);
    }

    private function countView(Request $request): void
    {
        // কন্ট্রোলার মডেল বাইন্ড করলে সরাসরি সেটিই ব্যবহার হয়
        $model = $request->route('post') ?? $request->route('video') ?? $request->route('news');

        // না পেলে রাউটের {slug} প্যারামিটার থেকে খোঁজা হয়
        if (! $model instanceof \Illuminate\Database\Eloquent\Model) {
            $slug = $request->route('slug');

            if (! $slug) {
                return;
            }

            $model = $request->routeIs('videos.*')
                ? \App\Models\Video::where('slug', $slug)->first()
                : Post::where('slug', $slug)->first();
        }

        if (! $model || ! $model->id) {
            return;
        }

        // সেশন-ভিত্তিক ডিডুপ্লিকেশন — একই ভিজিটর বারবার গুনা হবে না
        $key = 'mc_viewed_'.class_basename($model).'_'.$model->id;

        if ($request->session()->has($key)) {
            return;
        }

        $request->session()->put($key, now()->toDateTimeString());

        $model->increment('views');
    }
}
