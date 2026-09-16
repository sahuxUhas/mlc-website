<?php
namespace Database\Seeders;

use App\Models\Reporter;
use Illuminate\Database\Seeder;

class ReporterSeeder extends Seeder
{
    public function run(): void
    {
        $reporters = [
            ['name' => 'নিজস্ব প্রতিবেদক', 'slug' => 'staff-correspondent', 'designation' => 'স্টাফ করেসপন্ডেন্ট', 'bio' => 'মহালছড়ি উপজেলার সার্বিক সংবাদ পরিবেশন করেন।'],
            ['name' => 'মহালছড়ি প্রতিনিধি', 'slug' => 'mahalchhari-correspondent', 'designation' => 'উপজেলা প্রতিনিধি', 'bio' => 'মহালছড়ি সদর ও আশপাশের এলাকার সংবাদ।'],
            ['name' => 'খাগড়াছড়ি ব্যুরো', 'slug' => 'khagrachhari-bureau', 'designation' => 'ব্যুরো প্রধান', 'bio' => 'খাগড়াছড়ি জেলা পর্যায়ের সংবাদ পরিবেশন।'],
            ['name' => 'ক্রীড়া প্রতিবেদক', 'slug' => 'sports-reporter', 'designation' => 'ক্রীড়া ডেস্ক', 'bio' => 'উপজেলার ক্রীড়া সংবাদ ও টুর্নামেন্ট কভারেজ।'],
        ];

        foreach ($reporters as $i => $data) {
            Reporter::updateOrCreate(['slug' => $data['slug']], $data + ['is_visible' => true, 'sort_order' => $i]);
        }
    }
}
