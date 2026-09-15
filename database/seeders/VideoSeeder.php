<?php
namespace Database\Seeders;

use App\Models\Video;
use Illuminate\Database\Seeder;

/** ডেমোর MOCK_VIDEOS থেকে ভিডিও */
class VideoSeeder extends Seeder
{
    public function run(): void
    {
        $videos = [
            ['title' => 'মহালছড়ি ফায়ার সার্ভিস স্টেশন, ভিত্তিপ্রস্তরই সান্ত্বনা! সুরাহা চাই বাট দিবে কে?',
             'url' => 'https://www.facebook.com/share/v/19Xg5B3P78/', 'duration' => '৩:৩২', 'reel' => false, 'views' => 12400,
             'desc' => 'মহালছড়িতে ফায়ার সার্ভিস স্টেশনের দীর্ঘদিনের দাবি। ভিত্তিপ্রস্তর স্থাপন হলেও পূর্ণাঙ্গ স্টেশন চালুর দাবি জানিয়েছেন স্থানীয়রা।'],
            ['title' => 'মহালছড়ি বাজারের বর্তমান অবস্থা — সরেজমিন প্রতিবেদন',
             'url' => 'https://www.facebook.com/reel/1083023034090121/', 'duration' => '১:০৫', 'reel' => true, 'views' => 8730,
             'desc' => 'মহালছড়ি বাজারের সর্বশেষ অবস্থা নিয়ে সংক্ষিপ্ত ভিডিও প্রতিবেদন।'],
            ['title' => 'পাহাড়ি ঢলে ফেনী নদীর ভাঙন — ড্রোন চিত্র',
             'url' => 'https://www.facebook.com/mahalcharinews/videos/1234567890', 'duration' => '২:৪৮', 'reel' => false, 'views' => 15200,
             'desc' => 'ফেনী নদীর তীর ভাঙনের ড্রোন চিত্র। নিম্নাঞ্চলের কয়েকশ পরিবার পানিবন্দি।'],
            ['title' => 'উপজেলা ক্রীড়া সংস্থার ফুটবল লিগ — সেরা মুহূর্ত',
             'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'duration' => '৫:১২', 'reel' => false, 'views' => 4300,
             'desc' => 'শীতকালীন ফুটবল লিগের সেরা মুহূর্তগুলো নিয়ে ভিডিও।'],
            ['title' => 'বৃক্ষরোপণ কর্মসূচিতে স্কুল-ছাত্রদের অংশগ্রহণ',
             'url' => 'https://www.facebook.com/mahalcharinews/videos/9876543210', 'duration' => '৪:০৫', 'reel' => false, 'views' => 3120,
             'desc' => '৫০ হাজার চারা বৃক্ষরোপণ কর্মসূচিতে মহালছড়ির স্কুল-ছাত্ররা অংশ নেয়।'],
            ['title' => 'মাইসছড়ি পল্লী স্বাস্থ্য কেন্দ্রের সেবা কার্যক্রম',
             'url' => 'https://www.facebook.com/mahalcharinews/videos/1122334455', 'duration' => '৩:২০', 'reel' => false, 'views' => 1890,
             'desc' => 'মাইসছড়ি ইউনিয়নের ছয়টি গ্রামে পল্লী স্বাস্থ্য কেন্দ্রের চলমান সেবা।'],
        ];

        foreach ($videos as $i => $v) {
            Video::updateOrCreate(['slug' => mc_slug($v['title'])], [
                'title' => $v['title'], 'video_url' => $v['url'], 'duration' => $v['duration'],
                'is_reel' => $v['reel'], 'description' => $v['desc'], 'views' => $v['views'],
                'status' => 'published', 'is_visible' => true,
                'published_at' => now()->subDays($i + 1),
                'meta_title' => $v['title'].' | ভিডিও | দৈনিক মহালছড়ি নিউজ',
            ]);
        }
    }
}
