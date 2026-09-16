<?php

namespace App\Http\Middleware;

use App\Models\Announcement;
use App\Models\Post;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Schedule Publish — নির্ধারিত সময় পার হওয়া সংবাদ/ঘোষণা
 * স্বয়ংক্রিয়ভাবে প্রকাশ করে (cPanel cron ছাড়াও কাজ চলে, cron থাকলে আরও নির্ভরযোগ্য)।
 */
class PublishScheduledContent
{
    private const LOCK = 'mc.scheduler.last_run';

    public function handle(Request $request, Closure $next): Response
    {
        // প্রতি মিনিটে একবারই চালাই — অতিরিক্ত কুয়েরি এড়াতে
        if (! Cache::has(self::LOCK)) {
            Cache::put(self::LOCK, 1, now()->addMinutes(1));

            $due = Post::dueScheduled()->get(['id', 'status', 'scheduled_at']);
            foreach ($due as $post) {
                $post->forceFill([
                    'status'       => 'published',
                    'published_at' => $post->scheduled_at ?? now(),
                ])->saveQuietly();
            }

            // মেয়াদ শেষ হওয়া ঘোষণা চিহ্নিত করা
            Announcement::where('status', 'published')
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', now())
                ->update(['status' => 'expired']);
        }

        return $next($request);
    }
}
