<?php
namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

/** ডেমোর MC_BOTTOM_ITEMS ও EXTRA_MENU থেকে মেনু */
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // মোবাইল বটম নেভিগেশন (৪টি — ডেমোর হুবহু)
        $bottom = [
            ['label' => 'হোম', 'icon' => 'ph-house', 'url' => '/', 'link_type' => 'internal'],
            ['label' => 'সর্বশেষ', 'icon' => 'ph-clock-counter-clockwise', 'url' => '/latest-news', 'link_type' => 'internal'],
            ['label' => 'ভিডিও', 'icon' => 'ph-play-circle', 'url' => '/videos', 'link_type' => 'internal'],
            ['label' => 'ঘোষণা', 'icon' => 'ph-megaphone', 'url' => '/announcements', 'link_type' => 'internal'],
        ];
        foreach ($bottom as $i => $item) {
            Menu::updateOrCreate(['location' => 'mobile_bottom', 'label' => $item['label']], $item + [
                'is_enabled' => true, 'sort_order' => $i,
            ]);
        }

        // "আরও" ড্রপডাউন (ডেমোর EXTRA_MENU)
        $more = [
            ['label' => 'ফটো গ্যালারি', 'icon' => 'ph-image-square', 'url' => '/photo-gallery'],
            ['label' => 'ই-পেপার', 'icon' => 'ph-newspaper', 'url' => '/epaper'],
            ['label' => 'রিপোর্টার', 'icon' => 'ph-user-focus', 'url' => '/reporters'],
            ['label' => 'প্রতিবেদন দিন', 'icon' => 'ph-article', 'url' => '/submit-report'],
            ['label' => 'আমাদের সম্পর্কে', 'icon' => 'ph-info', 'url' => '/about'],
            ['label' => 'যোগাযোগ', 'icon' => 'ph-phone', 'url' => '/contact'],
            ['label' => 'বিজ্ঞাপন দিন', 'icon' => 'ph-currency-circle-dollar', 'url' => '/advertise'],
        ];
        foreach ($more as $i => $item) {
            Menu::updateOrCreate(['location' => 'more', 'label' => $item['label']], $item + [
                'link_type' => 'internal', 'is_enabled' => true, 'sort_order' => $i,
            ]);
        }

        // ফুটার মেনু
        $footer = [
            ['label' => 'প্রাইভেসি পলিসি', 'url' => '/privacy-policy'],
            ['label' => 'শর্তাবলি', 'url' => '/terms'],
            ['label' => 'সম্পাদকীয় নীতিমালা', 'url' => '/editorial-policy'],
        ];
        foreach ($footer as $i => $item) {
            Menu::updateOrCreate(['location' => 'footer', 'label' => $item['label']], $item + [
                'link_type' => 'internal', 'is_enabled' => true, 'sort_order' => $i,
            ]);
        }
    }
}
