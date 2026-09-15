<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Video;
use Illuminate\Support\Facades\Cache;

/** XML Sitemap, robots.txt ও RSS ফিড — সবই অ্যাডমিন সেটিংস-নিয়ন্ত্রিত */
class SitemapController extends Controller
{
    public function index()
    {
        $xml = Cache::remember('site.sitemap.xml', now()->addHours(6), function () {
            $urls = [];

            $urls[] = ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'hourly'];
            $urls[] = ['loc' => route('latest'), 'priority' => '0.9', 'changefreq' => 'hourly'];
            $urls[] = ['loc' => route('videos.index'), 'priority' => '0.7', 'changefreq' => 'daily'];
            $urls[] = ['loc' => route('announcements.index'), 'priority' => '0.6', 'changefreq' => 'daily'];
            $urls[] = ['loc' => route('gallery.index'), 'priority' => '0.6', 'changefreq' => 'weekly'];

            foreach (Category::visible()->get(['slug', 'updated_at']) as $category) {
                $urls[] = ['loc' => route('category.show', $category->slug), 'priority' => '0.8', 'changefreq' => 'daily', 'lastmod' => $category->updated_at];
            }

            foreach (Post::published()->latestFirst()->limit(2000)->get(['slug', 'updated_at']) as $post) {
                $urls[] = ['loc' => route('news.show', $post->slug), 'priority' => '0.9', 'changefreq' => 'weekly', 'lastmod' => $post->updated_at];
            }

            foreach (Video::published()->get(['slug', 'updated_at']) as $video) {
                $urls[] = ['loc' => route('videos.show', $video->slug), 'priority' => '0.6', 'changefreq' => 'weekly', 'lastmod' => $video->updated_at];
            }

            foreach (Page::visible()->get(['slug', 'updated_at']) as $page) {
                $urls[] = ['loc' => route('page.show', $page->slug), 'priority' => '0.5', 'changefreq' => 'monthly', 'lastmod' => $page->updated_at];
            }

            return view('public.sitemap', compact('urls'))->render();
        });

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /** অ্যাডমিন প্যানেল থেকে নিয়ন্ত্রিত robots.txt */
    public function robots()
    {
        $custom = Setting::get('robots_txt');

        $content = $custom ?: "User-agent: *\nDisallow: /admin\nDisallow: /login\nAllow: /\n\nSitemap: ".route('sitemap');

        return response($content, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function feed()
    {
        $posts = Post::published()->with('category')->latestFirst()->limit(30)->get();

        return response()
            ->view('public.feed', compact('posts'))
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
