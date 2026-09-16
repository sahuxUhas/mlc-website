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
            'social_facebook' => 'https://www.facebook.com/mahalcharinews',
            'social_youtube' => '', 'social_twitter' => '', 'social_instagram' => '',
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

        Setting::flushCache();
    }
}
