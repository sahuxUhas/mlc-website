<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * পাবলিক সাইটের ক্যাশ ব্যবস্থাপনা।
 *
 * কেন দরকার: হোমপেজ/নেভিগেশন/ব্রেকিং বার/সাইটম্যাপ কিছু সময়ের জন্য ক্যাশ হয়
 * (দ্রুত লোডের জন্য)। অ্যাডমিন থেকে সংবাদ তৈরি/সম্পাদনা/প্রকাশ বা ছবি বদলালে
 * সাথে সাথে Public Website-এ পরিবর্তন দেখা যেতে হবে ⇒ সংশ্লিষ্ট ক্যাশ মুছে দেওয়া হয়।
 */
class PublicCache
{
    /** সব পাবলিক ক্যাশ কী */
    public const KEYS = [
        'site.home.data',
        'site.breaking.active',
        'site.nav.categories',
        'site.footer.categories',
        'site.announcements.active',
        'site.sitemap.xml',
    ];

    public static function flush(): void
    {
        foreach (self::KEYS as $key) {
            Cache::forget($key);
        }

        // মেনু ক্যাশ (location অনুযায়ী) — সব নিরাপদভাবে বাদ দেওয়া হয়
        foreach (['main', 'header', 'footer', 'mobile', 'top'] as $location) {
            Cache::forget('site.menus.'.$location);
        }
    }

    /** নির্দিষ্ট সংবাদ বা কনটেন্ট বদলানোর পর (Post/Album/Advertisement ইত্যাদি) */
    public static function flushForContent(): void
    {
        self::flush();
    }
}
