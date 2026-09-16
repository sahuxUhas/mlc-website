<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use App\Models\Post;
use App\Models\Video;
use Illuminate\Console\Command;

/** নির্ধারিত সময়ের সংবাদ/ভিডিও/ঘোষণা স্বয়ংক্রিয়ভাবে প্রকাশ করে */
class PublishScheduledContent extends Command
{
    protected $signature = 'mc:publish-scheduled';

    protected $description = 'সময় পার হওয়া নির্ধারিত (scheduled) কনটেন্ট প্রকাশ করুন';

    public function handle(): int
    {
        $now = now();

        $posts = Post::dueScheduled()->get();
        foreach ($posts as $post) {
            $post->forceFill([
                'status'       => 'published',
                'published_at' => $post->scheduled_at ?? $now,
            ])->save();
        }

        $videos = Video::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', $now)
            ->get();

        foreach ($videos as $video) {
            $video->forceFill([
                'status'       => 'published',
                'published_at' => $video->scheduled_at ?? $now,
            ])->save();
        }

        $expired = Announcement::where('status', 'published')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $now)
            ->update(['status' => 'expired']);

        $this->info(sprintf(
            'প্রকাশিত সংবাদ: %d | প্রকাশিত ভিডিও: %d | মেয়াদোত্তীর্ণ ঘোষণা: %d',
            $posts->count(), $videos->count(), $expired
        ));

        return self::SUCCESS;
    }
}
