<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * পাবলিক মন্তব্য — কোনো অ্যাকাউন্ট ছাড়াই শুধু Name + Comment।
 * ভ্যালিডেশন, রেট লিমিট ও অ্যান্টি-স্প্যাম যাচাইসহ।
 */
class CommentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'commentable_type' => ['required', 'in:post,video'],
            'commentable_id'   => ['required', 'integer', 'exists:posts,id'],
            'parent_id'        => ['nullable', 'integer', 'exists:comments,id'],
            'guest_name'       => ['required', 'string', 'min:2', 'max:60'],
            'guest_email'      => ['nullable', 'email', 'max:190'],
            'body'             => ['required', 'string', 'min:4', 'max:2000'],
            // হানিপট — বট সাধারণত লুকানো ফিল্ড পূরণ করে ফেলে
            'website'          => ['nullable', 'max:0'],
        ], [], ['guest_name' => 'নাম', 'body' => 'মন্তব্য']);

        $post = Post::published()->findOrFail($validated['commentable_id']);

        if (! $post->allow_comments) {
            return back()->with('error', 'এই সংবাদে মন্তব্য বন্ধ রয়েছে।');
        }

        // একই IP থেকে পুনরাবৃত্তি রোধ (ডুপ্লিকেট স্প্যাম)
        $fingerprint = md5($request->ip().'|'.$post->id.'|'.mb_substr($validated['body'], 0, 80));
        if (Cache::has('mc_comment_fp_'.$fingerprint)) {
            return back()->with('error', 'একই মন্তব্য ইতিমধ্যে জমা দেওয়া হয়েছে।');
        }
        Cache::put('mc_comment_fp_'.$fingerprint, 1, now()->addMinutes(30));

        $comment = new Comment([
            'commentable_type' => Post::class,
            'commentable_id'   => $post->id,
            'parent_id'        => $validated['parent_id'] ?? null,
            'guest_name'       => $validated['guest_name'],
            'guest_email'      => $validated['guest_email'] ?? null,
            'body'             => $validated['body'],
            'ip_address'       => $request->ip(),
            'user_agent'       => substr((string) $request->userAgent(), 0, 500),
            // নিষিদ্ধ শব্দ থাকলে সরাসরি স্প্যাম, নাহলে মডারেশনের জন্য pending
            'status'           => 'pending',
        ]);

        $comment->save();

        if ($comment->containsBadWords()) {
            $comment->update(['status' => 'spam']);
        }

        // মডারেশন ছাড়াই দৃশ্যমান হবে কিনা (সাইট সেটিংস)
        if (filter_var(site_setting('comments_auto_approve', '0'), FILTER_VALIDATE_BOOLEAN)) {
            $comment->update(['status' => 'approved']);
            $post->increment('comments_count');
        }

        return back()->with('success', 'আপনার মন্তব্য জমা হয়েছে। মডারেশনের পর প্রকাশিত হবে।');
    }

    /** পাঠক কর্তৃক মন্তব্য রিপোর্ট */
    public function report(Request $request, Comment $comment)
    {
        $ipKey = 'mc_comment_report_'.$comment->id.'_'.$request->ip();

        if (Cache::has($ipKey)) {
            return back()->with('error', 'আপনি ইতিমধ্যে এই মন্তব্যটি রিপোর্ট করেছেন।');
        }

        Cache::put($ipKey, 1, now()->addHours(24));

        $count = $comment->report_count + 1;
        $comment->update([
            'report_count' => $count,
            'is_reported'  => true,
            // ৩টির বেশি রিপোর্ট হলে স্বয়ংক্রিয়ভাবে লুকানো
            'status'       => $count >= 3 ? 'rejected' : $comment->status,
        ]);

        return back()->with('success', 'রিপোর্ট গৃহীত হয়েছে। ধন্যবাদ।');
    }
}
