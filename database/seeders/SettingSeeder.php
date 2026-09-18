<?php
namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/** ডেমোর SITE কনস্ট্যান্ট থেকে আসল ডাটাবেস সেটিংস */
class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $general = [
            'site_name' => 'মহালছড়ি নিউজ', 'site_prefix' => 'দৈনিক',
            'site_name_a' => 'মহালছড়ি', 'site_name_b' => 'নিউজ',
            'site_tagline' => 'পাহাড়ের কথা বলে', 'site_domain' => 'mahalcharinews.com',
            'site_location' => 'মহালছড়ি, খাগড়াছড়ি',
            'site_address' => 'উপজেলা কার্যালয় সংলগ্ন, মহালছড়ি সদর, খাগড়াছড়ি-৪৪৩০',
            'site_phone' => '+৮৮০ ১৭১১-২৩৪৬৭', 'site_email' => 'news@mahalcharinews.com',
            'newsroom_email' => 'newsroom@mahalcharinews.com',
            'timezone' => 'Asia/Dhaka',
            'footer_about' => 'মহালছড়ি উপজেলা, খাগড়াছড়ি ও পার্বত্য চট্টগ্রামের সর্বশেষ সংবাদ, ব্রেকিং নিউজ, ভিডিও ও ফটো গ্যালারি।',
            'footer_text' => 'ডিজাইন ও ডেভেলপমেন্ট: মহালছড়ি নিউজ টিম',
            'copyright_text' => '© '.bn_num(date('Y')).' দৈনিক মহালছড়ি নিউজ। সর্বস্বত্ব সংরক্ষিত।',
            'posts_per_page' => '15',
        ];
        foreach ($general as $k => $v) { Setting::put($k, $v, 'string', 'general'); }

        $social = [
            'social_facebook' => 'https://www.facebook.com/profile.php?id=100068836585906',
            'social_youtube' => 'https://www.youtube.com/@mahalcharinews',
            'social_twitter' => '', 'social_instagram' => '',
            'social_whatsapp' => '+8801711234567',
        ];
        foreach ($social as $k => $v) { Setting::put($k, $v, 'string', 'social'); }

        $seo = [
            'seo_title' => 'দৈনিক মহালছড়ি নিউজ | পাহাড়ের কথা বলে',
            'seo_description' => 'দৈনিক মহালছড়ি নিউজ — খাগড়াছড়ি জেলার মহালছড়ি উপজেলা, পার্বত্য চট্টগ্রাম ও সারাদেশের সর্বশেষ সংবাদ, ব্রেকিং নিউজ, ভিডিও ও ফটো গ্যালারি।',
            'seo_keywords' => 'মহালছড়ি, খাগড়াছড়ি, পার্বত্য চট্টগ্রাম, দৈনিক মহালছড়ি নিউজ, mahalcharinews',
            'seo_robots' => 'index, follow', 'seo_canonical' => 'https://mahalcharinews.com',
            'robots_txt' => "User-agent: *\nDisallow: /admin\nDisallow: /login\nAllow: /\n\nSitemap: https://mahalcharinews.com/sitemap.xml",
        ];
        foreach ($seo as $k => $v) { Setting::put($k, $v, 'textarea', 'seo'); }

        Setting::put('seo_sitemap_enabled', '1', 'bool', 'seo');
        Setting::put('seo_schema_enabled', '1', 'bool', 'seo');
        Setting::put('comments_enabled', '1', 'bool', 'behavior');
        Setting::put('comments_auto_approve', '0', 'bool', 'behavior');
        Setting::put('site_maintenance', '0', 'bool', 'behavior');
        Setting::put('maintenance_message', '', 'textarea', 'behavior');

        // হেডার (অ্যাডমিন → সাইট সেটিংস → হেডার)
        $header = [
            'header_sticky'            => '1',
            'header_show_date'         => '1',
            'header_show_clock'        => '1',
            'header_show_theme_toggle' => '1',
            'header_show_search'       => '1',
            'date_strip_enabled'       => '1',
        ];
        foreach ($header as $k => $v) { Setting::put($k, $v, 'bool', 'header'); }
        Setting::put('breaking_label', 'ব্রেকিং', 'string', 'header');
        Setting::put('header_more_label', 'আরও', 'string', 'header');
        Setting::put('header_live_label', 'লাইভ টিভি', 'string', 'header');
        Setting::put('header_live_url', '', 'string', 'header');

        // ফুটার কলাম দৃশ্যমানতা
        $footer = [
            'footer_show_about'      => '1',
            'footer_show_categories' => '1',
            'footer_show_links'      => '1',
            'footer_show_contact'    => '1',
            'footer_show_social'     => '1',
        ];
        foreach ($footer as $k => $v) { Setting::put($k, $v, 'bool', 'footer'); }
        Setting::put('footer_heading_links', 'গুরুত্বপূর্ণ লিংক', 'string', 'footer');

        // ছোটখাটো লেখা
        Setting::put('search_placeholder', 'সংবাদ খুঁজুন…', 'string', 'texts');
        Setting::put('search_button_label', 'খুঁজুন', 'string', 'texts');
        Setting::put('search_popular_label', 'জনপ্রিয় বিভাগ:', 'string', 'texts');
        Setting::put('home_highlight_title', 'সর্বশেষ সংবাদ', 'string', 'texts');

        // ImgBB Settings - user provided key
        Setting::put('imgbb_api_key', '4bfac8cf6fa4714236c08292299d2862', 'string', 'media');
        Setting::put('imgbb_enabled', '1', 'bool', 'media');
        Setting::put('imgbb_expiration', '0', 'string', 'media'); // 0 = never expire

        Setting::flushCache();
    }
}
