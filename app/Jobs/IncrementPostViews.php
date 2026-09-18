<?php

namespace App\Jobs;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * ⚡ Background Views Increment Job
 * 
 * উদ্দেশ্য: প্রতিটি news view এ database write এড়িয়ে background এ increment করা
 * 
 * সুবিধা:
 * - Response time দ্রুত হয় (user blocking operation নেই)
 * - Database load কমে
 * - Batch processing সম্ভব (একসাথে অনেক views update)
 * 
 * ব্যবহার: dispatch(new IncrementPostViews($post->id));
 */
class IncrementPostViews implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Job timeout (seconds)
     */
    public $timeout = 30;

    /**
     * Max retry attempts
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $postId)
    {
        // Queue name (low priority)
        $this->onQueue('low');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Timestamp update বন্ধ রেখে views increment
        Post::withoutTimestamps(function () {
            Post::where('id', $this->postId)->increment('views');
        });
    }

    /**
     * Job fail হলে কী করবে
     */
    public function failed(\Throwable $exception): void
    {
        // Log করুন বা notification পাঠান
        \Log::error('IncrementPostViews job failed', [
            'post_id' => $this->postId,
            'error' => $exception->getMessage(),
        ]);
    }
}
