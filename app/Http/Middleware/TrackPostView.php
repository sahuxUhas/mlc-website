<?php

namespace App\Http\Middleware;

use App\Models\Post;
use App\Models\Video;
use App\Support\ViewCounter;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ভিউ কাউন্টার — শুধুমাত্র বাস্তব ভিজিট গোনা হয়।
 *
 * কীভাবে:
 *   - পেজ সফলভাবে রেন্ডার হওয়ার **পরে** গোনা হয়, তাই ৪০৪/রিডাইরেক্ট
 *     (খসড়া সংবাদ, ভাঙা লিংক) ভুল করে ভিউ যোগ করে না।
 *   - শুধু GET রিকোয়েস্ট; বট/ক্রলার/মনিটরিং টুল বাদ (config/views.php)।
 *   - একই ভিজিটর একবারই গোনা হয় — সেশন + সময়ভিত্তিক কুলডাউন (ViewCounter)।
 *
 * রাউটে মডেল বাইন্ডিং ({slug}) থাকলে সেটিই ব্যবহার হয় ⇒ অতিরিক্ত কুয়েরি নেই।
 */
class TrackPostView
{
    public function __construct(private ViewCounter $counter)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldCount($request, $response)) {
            return $response;
        }

        $model = $this->resolveModel($request);

        if (! $model || ! $this->isCountable($model)) {
            return $response;
        }

        $this->counter->record($request, $model);

        return $response;
    }

    /** শুধু সফল GET পেজ ভিজিট গোনা হয় */
    private function shouldCount(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET') || $response->isRedirection() || ! $response->isSuccessful()) {
            return false;
        }

        if ((bool) config('views.count_bots', false)) {
            return true;
        }

        return ! ViewCounter::isBot($request->userAgent());
    }

    /** রাউট বাইন্ডিং বা {slug} থেকে সংবাদ/ভিডিও মডেল */
    private function resolveModel(Request $request): ?Model
    {
        $model = $request->route('post') ?? $request->route('video') ?? $request->route('news');

        if ($model instanceof Model) {
            return $model;
        }

        $slug = $request->route('slug');

        if (! $slug) {
            return null;
        }

        return $request->routeIs('videos.*')
            ? Video::where('slug', $slug)->first()
            : Post::where('slug', $slug)->first();
    }

    /** প্রকাশিত (ও দৃশ্যমান) কনটেন্টই গোনা হয় */
    private function isCountable(Model $model): bool
    {
        if ($model instanceof Post) {
            // Post::getIsLiveAttribute() — status published + প্রকাশের সময় পেরিয়ে গেছে
            return (bool) $model->is_live;
        }

        if ($model instanceof Video) {
            return $model->status === 'published' && (bool) $model->is_visible;
        }

        return true;
    }
}
