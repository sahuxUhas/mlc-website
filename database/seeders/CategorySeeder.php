<?php
namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/** ডেমোর MOCK_CATEGORIES + MC_CAT_META থেকে আসল ক্যাটাগরি */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'মহালছড়ি', 'slug' => 'mahalchhari', 'icon' => 'ph-map-pin', 'color' => '#E21D2B', 'union_name' => 'মহালছড়ি সদর', 'description' => 'মহালছড়ি উপজেলা সদর, বাজার ও আশপাশের এলাকার সংবাদ'],
            ['name' => 'মাইসছড়ি', 'slug' => 'maichhari', 'icon' => 'ph-tree', 'color' => '#1F7A3D', 'union_name' => 'মাইসছড়ি', 'description' => 'মাইসছড়ি ইউনিয়নের গ্রাম-হাট ও পাহাড়ি পল্লীর সংবাদ'],
            ['name' => 'ক্যায়াংঘাট', 'slug' => 'kayangghat', 'icon' => 'ph-mountains', 'color' => '#0F766E', 'union_name' => 'ক্যায়াংঘাট', 'description' => 'ক্যায়াংঘাট ইউনিয়নের সংবাদ — সড়ক, শিক্ষা ও কৃষি'],
            ['name' => 'মুবাছড়ি', 'slug' => 'mubachhari', 'icon' => 'ph-house-line', 'color' => '#1D4ED8', 'union_name' => 'মুবাছড়ি', 'description' => 'মুবাছড়ি ইউনিয়নের সংবাদ — স্বাস্থ্য, পানি ও দুর্যোগ প্রস্তুতি'],
            ['name' => 'খেলার খবর', 'slug' => 'khela', 'icon' => 'ph-soccer-ball', 'color' => '#B45309', 'union_name' => 'উপজেলা জুড়ে', 'description' => 'মাঠ-ময়দান, টুর্নামেন্ট, নৌকা বাইচ ও যুব ক্রীড়া'],
            ['name' => 'ব্রেকিং নিউজ', 'slug' => 'breaking-news', 'icon' => 'ph-lightning', 'color' => '#E21D2B', 'union_name' => 'উপজেলা জুড়ে', 'description' => 'জরুরি ও দ্রুত বদলানো খবর — ব্রেকিং ট্যাগ করা সংবাদ এখানে আসে'],
            ['name' => 'অপরাধ', 'slug' => 'aparadh', 'icon' => 'ph-scales', 'color' => '#7C2D12', 'union_name' => 'থানা এলাকা', 'description' => 'থানা, মামলা-অভিযোগ, চুরি ও নিরাপত্তা সংশ্লিষ্ট খবর'],
            ['name' => 'জেলার খবর', 'slug' => 'jela', 'icon' => 'ph-globe-hemisphere-east', 'color' => '#4338CA', 'union_name' => 'জেলা', 'description' => 'খাগড়াছড়ি জেলা ও দেশ পর্যায়ের যেসব খবর মহালছড়িকে ছুঁয়ে আছে'],
        ];

        foreach ($categories as $i => $data) {
            Category::updateOrCreate(['slug' => $data['slug']], $data + [
                'is_visible' => true, 'show_on_home' => true, 'show_in_menu' => true, 'sort_order' => $i,
            ]);
        }
    }
}
