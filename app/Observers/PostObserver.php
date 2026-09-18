<?php

namespace App\Observers;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;

/**
 * PostObserver — পোস্ট পরিবর্তনের সাথে সাথে সাইটের cache clear করে
 * 
 * উদ্দেশ্য: নতুন পোস্ট publish হলে বা পরিবর্তন হলে হোম পেজ ও অন্যান্য
 * cached data সাথে সাথে আপডেট হওয়া নিশ্চিত করা
 */
class PostObserver
{
    /**
     * পোস্ট তৈরি হলে cache clear
     */
    public function created(Post $post): void
    {
        if ($post->status === 'published') {
            $this->clearHomeCache();
        }
    }

    /**
     * পোস্ট আপডেট হলে — status/published_at/featured/breaking/category পরিবর্তনে
     */
    public function updated(Post $post): void
    {
        // গুরুত্বপূর্ণ ফিল্ড পরিবর্তন হলেই cache clear
        if ($post->isDirty(['status', 'published_at', 'is_featured', 'is_breaking', 'category_id'])) {
            $this->clearHomeCache();
        }
    }

    /**
     * পোস্ট মুছে ফেললে
     */
    public function deleted(Post $post): void
    {
        $this->clearHomeCache();
    }

    /**
     * Trash থেকে restore করলে
     */
    public function restored(Post $post): void
    {
        $this->clearHomeCache();
    }

    /**
     * সব relevant cache keys clear করে
     */
    private function clearHomeCache(): void
    {
        Cache::forget('site.home.data');
        Cache::forget('site.breaking.news');
        Cache::forget('site.latest.news');
        
        // ক্যাটাগরি-ভিত্তিক cache থাকলে সেগুলোও clear করুন
        // যেমন: Cache::tags(['home', 'posts'])->flush();
    }
}
