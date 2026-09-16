<?php

namespace App\Http\Middleware;

use App\Models\Announcement;
use App\Models\BreakingNews;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * প্রতিটি রিকোয়েস্টে সাইটের গ্লোবাল ডেটা সব ভিউতে শেয়ার করে —
 * নেভিগেশন, ক্যাটাগরি, ব্রেকিং নিউজ, ফুটার ও সেটিংস।
 * ক্যাশ ব্যবহার করায় অতিরিক্ত কুয়েরি খরচ হয় না।
 */
class ShareSiteData
{
    public function handle(Request $request, Closure $next): Response
    {
        $ttl = now()->addMinutes(5);

        // সাইট সেটিংস
        view()->share('siteSettings', Setting::all_settings());

        // মূল নেভিগেশন ক্যাটাগরি
        view()->share('navCategories', Cache::remember(
            'site.nav.categories', $ttl,
            fn () => Category::forMenu()->limit(12)->get()
        ));

        // ফুটার ক্যাটাগরি
        view()->share('footerCategories', Cache::remember(
            'site.footer.categories', $ttl,
            fn () => Category::visible()->root()->ordered()->limit(10)->get()
        ));

        // মেনু (location অনুযায়ী)
        foreach (array_keys(Menu::LOCATIONS) as $location) {
            view()->share(
                'menu_'.$location,
                Cache::remember('site.menus.'.$location, $ttl, fn () => Menu::inLocation($location)->with('children')->get())
            );
        }

        // ব্রেকিং নিউজ বার
        view()->share('breakingItems', Cache::remember(
            'site.breaking.active', $ttl,
            fn () => BreakingNews::active()->with('post:id,slug,title')->limit(12)->get()
        ));

        // সক্রিয় ঘোষণা (ফুটার/সাইডবারে ব্যবহার হয়)
        view()->share('activeAnnouncements', Cache::remember(
            'site.announcements.active', $ttl,
            fn () => Announcement::active()->ordered()->limit(5)->get()
        ));

        return $next($request);
    }
}
