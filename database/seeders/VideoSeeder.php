<?php
namespace Database\Seeders;

use App\Models\Video;
use Illuminate\Database\Seeder;

/** রিয়েল ভিডিও — মহালছড়ি নিউজ ফেসবুক পেজ থেকে, এখন playable */
class VideoSeeder extends Seeder
{
    public function run(): void
    {
        $videos = [
            ['title' => 'মহালছড়ি বাজারে ২৩ দোকান পুড়ে ছাই — সরেজমিনে ক্ষতিগ্রস্ত ব্যবসায়ীদের কান্না',
             'url' => 'https://www.facebook.com/share/v/19Xg5B3P78/', 'duration' => '৪:১৫', 'reel' => false, 'desc' => 'মহালছড়ি বাজারে ভয়াবহ অগ্নিকাণ্ডে ২৩টি দোকান পুড়ে ছাই। ব্যবসায়ীরা ৫ কোটি টাকার ক্ষতির দাবি করেছেন। ফায়ার সার্ভিস স্টেশন চালুর দাবি।'],

            ['title' => 'বানভাসি মানুষের দোরগোড়ায় সেনাবাহিনী — নদীপথে চিকিৎসা ও ওষুধ বিতরণ',
             'url' => 'https://www.facebook.com/share/v/1Ber5oogFd/', 'duration' => '৩:০৫', 'reel' => true, 'desc' => 'টানা বর্ষণে পানিবন্দি মহালছড়ির মানুষের বাড়ি বাড়ি নৌকায় করে চিকিৎসা পৌঁছে দিচ্ছে মহালছড়ি সেনা জোন।'],

            ['title' => 'মাইসছড়িতে বজ্রপাতে স্বামী-স্ত্রীর মৃত্যু — পরিবারে শোকের ছায়া',
             'url' => 'https://www.facebook.com/share/v/14mVcr6iSDs/', 'duration' => '২:২০', 'reel' => true, 'desc' => '৩ নম্বর পুনর্বাসন পাড়ায় সোলারে বজ্রপাত হলে ঘটনাস্থলেই দুজনের মৃত্যু। প্রশাসনের সহায়তা প্রদান।'],

            ['title' => 'মহালছড়ি সেনা জোনের বিনামূল্যে চক্ষু ক্যাম্প — ৫৬৮ জনের চোখ পরীক্ষা',
             'url' => 'https://www.facebook.com/share/v/1CY2kvyRSY/', 'duration' => '৫:১২', 'reel' => false, 'desc' => 'শিশু মঞ্চ উচ্চ বিদ্যালয়ে লায়ন্স ক্লাবের সহযোগিতায় দিনব্যাপী চক্ষু ক্যাম্প। ১০০ জনকে চশমা বিতরণ।'],

            ['title' => 'পাহাড়ধসে মহালছড়ি-জালিয়াপাড়া সড়ক বন্ধ — ড্রোনে ক্ষতিগ্রস্ত সড়কের চিত্র',
             'url' => 'https://www.facebook.com/share/v/1BGLr3jiCs/', 'duration' => '২:৪৮', 'reel' => false, 'desc' => 'কাটিংটিলায় পাহাড়ধসে সড়ক দেবে গেছে। যাত্রীদের চরম ভোগান্তি, দ্রুত মেরামতের দাবি।'],

            ['title' => 'মহালছড়ি-সিন্দুকছড়ি সড়কে পর্যটনের হাতছানি — ধুমনীঘাট ঝরনায় পর্যটকদের ভিড়',
             'url' => 'https://www.facebook.com/share/v/19RNHFTGiQ/', 'duration' => '৪:৩০', 'reel' => false, 'desc' => '১৫ কিমি পাহাড়ি সড়ক, মাচাং ঘর ও ধুমনীঘাট ঝরনা — নতুন পর্যটন স্পট হিসেবে পরিচিতি পাচ্ছে।'],

            ['title' => 'সকল সম্প্রদায়ের সমন্বয়ে মহালছড়িতে সম্প্রীতি কাপ ফুটবল টুর্নামেন্টের উদ্বোধন',
             'url' => 'https://www.facebook.com/share/v/1LEHRuMaiE/', 'duration' => '২:৩৩', 'reel' => false, 'desc' => 'পাহাড়ি ও বাঙালি সব সম্প্রদায়ের সমন্বয়ে ১৬ দলের অংশগ্রহণে সম্প্রীতি কাপ ফুটবল টুর্নামেন্ট ২০২৬।'],

            ['title' => 'প্রস্তুতি সম্পন্ন: জমকালো আয়োজনে মহালছড়ি সম্প্রীতি ফুটবল কাপ ২০২৬',
             'url' => 'https://www.facebook.com/share/r/19DAvp2XJe/', 'duration' => '০:৫৯', 'reel' => true, 'desc' => 'জমকালো আয়োজনে অনুষ্ঠিত হবে মহালছড়ি সম্প্রীতি ফুটবল কাপ টুর্নামেন্ট ২০২৬-এর উদ্বোধনী অনুষ্ঠান।'],
        ];

        foreach ($videos as $i => $v) {
            Video::updateOrCreate(['slug' => mc_slug($v['title'])], [
                'title' => $v['title'], 'video_url' => $v['url'], 'duration' => $v['duration'],
                'is_reel' => $v['reel'], 'description' => $v['desc'],
                'status' => 'published', 'is_visible' => true,
                'published_at' => now()->subDays($i + 1),
                'meta_title' => $v['title'].' | ভিডিও | দৈনিক মহালছড়ি নিউজ',
            ]);
        }
    }
}
