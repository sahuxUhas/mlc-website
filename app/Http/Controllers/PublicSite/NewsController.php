<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * ফুল নিউজ / আর্টিকেল পেজ।
 *
 * পেজের কাঠামো: শিরোনাম → ফিচার্ড ছবি → নিউজ কনটেন্ট → শেয়ার → ফেসবুক ফলো → মন্তব্য।
 * শুধুমাত্র ডাটাবেসের বাস্তব ডেটা রেন্ডার হয় — কোনো ডেমো/হার্ডকোড কনটেন্ট নেই।
 */
class NewsController extends Controller
{
    public function show(string $slug)
    {
        $post = Post::published()
            ->with([
                'category:id,name,slug',
                'reporter:id,name,slug',
                'author:id,name',
                'images',
                'tags:id,name,slug',
                // পাবলিক পেজে কেবল অনুমোদিত (approved) মন্তব্য ও তাদের উত্তর
                'comments' => fn ($q) => $q->approved()->topLevel()
                    ->with([
                        'user:id,name',
                        'replies' => fn ($r) => $r->approved()->newest()->limit(30)->with('user:id,name'),
                    ])
                    ->newest()->limit(60),
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        // অনুমোদিত মন্তব্যের সংখ্যা (আপেক্ষিক/short — DB-এর ডিনরমালাইজড কাউন্টার নয়)
        $commentCount = $post->comments->count() + (int) $post->comments->sum(fn ($c) => $c->replies->count());

        // শেয়ার/ফলো লিংক — সেটিংস থেকে (কোথাও hardcode করা হয়নি)
        $facebookFollowUrl = trim((string) site_setting('social_facebook')) ?: null;
        $facebookAppId     = trim((string) site_setting('facebook_app_id')) ?: null;

        // Article Structured Data (JSON-LD)
        $schema = [
            '@context'         => 'https://schema.org',
            '@type'            => 'NewsArticle',
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => route('news.show', $post->slug)],
            'headline'         => mb_substr($post->title, 0, 110),
            'description'      => mc_excerpt($post->excerpt ?: $post->content, 300),
            'image'            => $post->featured_image ? [mc_image($post->featured_image)] : [],
            'datePublished'    => optional($post->published_at)->toIso8601String(),
            'dateModified'     => optional($post->updated_at)->toIso8601String(),
            'author'           => ['@type' => 'Person', 'name' => $post->reporter?->name ?? $post->author?->name ?? site_setting('site_name')],
            'publisher'        => [
                '@type' => 'Organization',
                'name'  => site_setting('site_name', config('app.name')),
                'logo'  => ['@type' => 'ImageObject', 'url' => site_setting('site_logo')],
            ],
            'articleSection'   => $post->category?->name,
            'keywords'         => $post->tags->pluck('name')->implode(', '),
            'commentCount'     => $commentCount,
            'inLanguage'       => 'bn',
        ];

        return view('public.article', compact('post', 'schema', 'commentCount', 'facebookFollowUrl', 'facebookAppId'));
    }

    /**
     * লেগ্যাসি /article/{id} রাউট — পুরোনো লিংক ভাঙা রোধে
     * সরাসরি সঠিক /news/{slug} এ 301 রিডাইরেক্ট করে।
     */
    public function legacyRedirect(string $id)
    {
        $post = Post::withTrashed()->find($id);

        if (! $post) {
            abort(404);
        }

        return redirect()->to(route('news.show', $post->slug), 301);
    }

    /**
     * পাঠক কর্তৃক মন্তব্য রিপোর্ট (spam protection)।
     * শুধুমাত্র এই সংবাদের অনুমোদিত মন্তব্যই রিপোর্ট করা যায় — re-verify করা হয়।
     */
    public function report(Request $request, string $slug, Comment $comment)
    {
        $post = Post::published()->where('slug', $slug)->firstOrFail();

        if ($comment->commentable_type !== $post->getMorphClass()
            || (int) $comment->commentable_id !== (int) $post->id
            || $comment->status !== 'approved') {
            abort(404);
        }

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
