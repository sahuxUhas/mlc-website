<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Post;

/** ফুল নিউজ / আর্টিকেল পেজ */
class NewsController extends Controller
{
    public function show(string $slug)
    {
        $post = Post::published()
            ->with([
                'category', 'subcategory:id,name,slug', 'reporter',
                'author:id,name,avatar', 'images', 'tags',
                'comments' => fn ($q) => $q->approved()->topLevel()->with('replies')->newest()->limit(100),
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        // সম্পর্কিত সংবাদ ও সর্বশেষ সংবাদ (সাইডবার)
        $related = $post->related(4);
        $latest  = Post::published()->latestFirst()->limit(6)->get(['id', 'slug', 'title', 'featured_image', 'published_at', 'views']);

        // Article Structured Data (JSON-LD)
        $schema = [
            '@context'         => 'https://schema.org',
            '@type'            => 'NewsArticle',
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => route('news.show', $post->slug)],
            'headline'         => mb_substr($post->title, 0, 110),
            'description'      => mc_excerpt($post->excerpt ?: $post->content, 300),
            'image'            => $post->featured_image ? [asset($post->featured_image)] : [],
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
            'inLanguage'       => 'bn',
        ];

        return view('public.article', compact('post', 'related', 'latest', 'schema'));
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

        return redirect()->route('news.show', $post->slug), 301;
    }
}
