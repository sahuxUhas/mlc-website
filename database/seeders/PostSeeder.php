<?php
namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Reporter;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * ডেমোর MOCK_NEWS + MC_EXTRA_NEWS থেকে আসল ডাটাবেস রেকর্ড।
 * ছবিগুলো ডেমোর Unsplash URL — অ্যাডমিন থেকে নিজের ছবি দিয়ে বদলানো যাবে।
 */
class PostSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'super_admin')->first();
        $categories = Category::pluck('id', 'slug');
        $reporters = Reporter::pluck('id', 'slug');

        $news = [
            ['title' => 'মহালছড়িতে টানা বৃষ্টি: নদীতে ভাঙন, বাজার এলাকায় হাঁটুপানি', 'cat' => 'mahalchhari', 'rep' => 'mahalchhari-correspondent',
             'excerpt' => 'টানা তিন দিনের বর্ষণে ফেনী নদীর তীর ভেঙে যাওয়ায় নিম্নাঞ্চলের কয়েকশ পরিবার পানিবন্দি হয়ে পড়েছে। উপজেলা প্রশাসন সতর্কবার্তা জারি করেছে।',
             'content' => '<p>টানা তিন দিনের ভারী বৃষ্টিপাতে ফেনী নদী ফুঁসছে। পাহাড়ি ঢলে উপজেলার নিম্নাঞ্চলের কয়েকটি ইউনিয়নে পানি ঢুকেছে।</p><p>উপজেলা নির্বাহী অফিসার জানান, বন্যা আশঙ্কা থাকা এলাকাগুলো থেকে মানুষকে নিরাপদ আশ্রয়ে সরানোর প্রস্তুতি নেওয়া হয়েছে। মহালছড়ি বাজারের কয়েকটি দোকানে হাঁটু পরিমাণ পানি জমেছে।</p><h2>আবহাওয়ার পূর্বাভাস</h2><p>আবহাওয়া অফিস বলছে, আগামী ৪৮ ঘণ্টা আরও ভারী বৃষ্টির সম্ভাবনা রয়েছে।</p>',
             'img' => 'https://images.unsplash.com/photo-1515694346937-94d85e41e6f0?w=800&auto=format&fit=crop&q=80', 'views' => 18420, 'breaking' => true, 'featured' => true, 'tags' => 'বন্যা,ফেনী নদী,মহালছড়ি'],

            ['title' => 'দিঘিনালা–মহালছড়ি সড়কের ঝুঁকিপূর্ণ খণ্ড মেরামতের নির্দেশ', 'cat' => 'mahalchhari', 'rep' => 'staff-correspondent',
             'excerpt' => 'সড়কের ভাঙা অংশ দ্রুত মেরামতের নির্দেশ দিয়েছেন সড়ক ও জনপথ বিভাগের আঞ্চলিক প্রকৌশলী।',
             'content' => '<p>মানবজীবনের ঝুঁকি তৈরি হওয়ায় দিঘিনালা–মহালছড়ি সড়কের তিনটি পয়েন্টে জরুরি ভিত্তিতে মেরামত কাজ শুরু হবে বলে জানিয়েছেন সংশ্লিষ্ট প্রকৌশলী।</p><p>স্থানীয়রা বলছেন, বর্ষায় এই সড়কটিই যাতায়াতের একমাত্র ভরসা; দীর্ঘদিন সংস্কার না হওয়ায় দুর্ভোগ চরমে।</p>',
             'img' => 'https://images.unsplash.com/photo-1542385150-71cd6c703a89?w=800&auto=format&fit=crop&q=80', 'views' => 9310, 'breaking' => true, 'featured' => true, 'tags' => 'সড়ক,মেরামত'],

            ['title' => 'পাহাড়ে ৫০ হাজার চারা বৃক্ষরোপণ: অংশ নিল মহালছড়ির স্কুল-ছাত্ররা', 'cat' => 'jela', 'rep' => 'khagrachhari-bureau',
             'excerpt' => 'পাহাড়ের মাটি ধুয়ে যাওয়া রুখতে উপজেলা পর্যায়ে বৃহৎ বৃক্ষরোপণ কর্মসূচির উদ্যোগ নিয়েছে বন অধিদপ্তর ও স্থানীয় সমাজ।',
             'content' => '<p>গাছ ছাড়া পাহাড় টিকবে না—সেই বার্তা নিয়ে মহালছড়ির পাঁচটি শিক্ষাপ্রতিষ্ঠানের ছাত্র-ছাত্রীরা মিলে গড়ে তুলছে সবুজ বেল্ট।</p><p>গামেলা, গাভা, নগরকাস্তুর মতো দেশীয় জাতের চারা রোপণ করা হবে আগামী দুই মাসে।</p>',
             'img' => 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?w=800&auto=format&fit=crop&q=80', 'views' => 5120, 'breaking' => false, 'featured' => true, 'tags' => 'বৃক্ষরোপণ,পরিবেশ'],

            ['title' => 'মহালছড়ি সরকারি উচ্চ বিদ্যালয়ে নতুন ভবন হস্তান্তর, খুলল নতুন অধ্যায়', 'cat' => 'mahalchhari', 'rep' => 'mahalchhari-correspondent',
             'excerpt' => '১২ কক্ষবিশিষ্ট নতুন একাডেমিক ভবন আনুষ্ঠানিকভাবে হস্তান্তর করা হয়েছে; এবছর থেকেই শিক্ষার্থীরা সেখানে পাঠদান শুরু করবে।',
             'content' => '<p>দীর্ঘ প্রতীক্ষার অবসান। মাঠ ও ছাদে ক্লাস হওয়া শেষ করে নতুন ভবনে পা রাখল মহালছড়ি সরকারি উচ্চ বিদ্যালয়ের শিক্ষার্থীরা।</p><p>অধ্যক্ষ জানিয়েছেন, বিজ্ঞানাগার ও লাইব্রেরিসহ ভবনটি সম্পূর্ণভাবে চালু করতে আরও দুই মাস লেগে যাবে।</p>',
             'img' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=800&auto=format&fit=crop&q=80', 'views' => 4230, 'breaking' => false, 'featured' => false, 'tags' => 'শিক্ষা,বিদ্যালয়'],

            ['title' => 'মাইসছড়িতে ৬টি গ্রামে পল্লী স্বাস্থ্য কেন্দ্রের চলমান সেবা', 'cat' => 'maichhari', 'rep' => 'staff-correspondent',
             'excerpt' => 'সাপ্তাহিক ছয়দিন টিকাদান ও জরুরি ওষুধ পাওয়া যাচ্ছে; রাতের প্রয়োজনে আশপাশের কমিউনিটি ক্লিনিক খোলা রাখে।',
             'content' => '<p>মাইসছড়ি ইউনিয়নের ছয়টি গ্রামে পল্লী স্বাস্থ্য কেন্দ্রের সেবা চলমান রয়েছে।</p><h2>আশপাশের পরিস্থিতি</h2><p>সাপ্তাহিক ছয়দিন টিকাদান ও জরুরি ওষুধ পাওয়া যাচ্ছে।</p>',
             'img' => 'https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?w=800&auto=format&fit=crop&q=80', 'views' => 1820, 'breaking' => false, 'featured' => false, 'tags' => 'স্বাস্থ্য,মাইসছড়ি'],

            ['title' => 'মাইসছড়ির হাট সংলগ্ন নলকূপ চালু, পাহাড়ি পানির অভাব দূরীকরণের দাবি', 'cat' => 'maichhari', 'rep' => 'mahalchhari-correspondent',
             'excerpt' => 'হাটের ব্যবসায়ী ও অভিভাবকরা পানি সংরক্ষণের জন্য ছোট ট্যাঙ্ক বসানোর অনুরোধ জানিয়েছেন।',
             'content' => '<p>মাইসছড়ির হাট সংলগ্ন এলাকায় নতুন নলকূপ চালু হয়েছে।</p><p>স্থানীয়রা পানি সংরক্ষণের জন্য ছোট ট্যাঙ্ক বসানোর দাবি জানিয়েছেন।</p>',
             'img' => 'https://images.unsplash.com/photo-1541888946425-d0fbb18086f6?w=800&auto=format&fit=crop&q=80', 'views' => 1450, 'breaking' => false, 'featured' => false, 'tags' => 'পানি,মাইসছড়ি'],

            ['title' => 'ক্যায়াংঘাটে স্কুল পাড় থেকে ইউনিয়ন কার্যালয় পর্যন্ত পথ সংস্কার শুরু', 'cat' => 'kayangghat', 'rep' => 'staff-correspondent',
             'excerpt' => 'বর্ষার আগেই সংস্কার শেষ করার কথা; স্থানীয় যুবকরা স্বেচ্ছাসেবী টিম দিয়ে মাটি ভরাতে সহায়তা করছেন।',
             'content' => '<p>ক্যায়াংঘাট ইউনিয়নের গুরুত্বপূর্ণ সংযোগ সড়কটি সংস্কারের কাজ শুরু হয়েছে।</p><p>স্থানীয় যুবকরা স্বেচ্ছাসেবী টিম গঠন করে সহায়তা করছেন।</p>',
             'img' => 'https://images.unsplash.com/photo-1542385150-71cd6c703a89?w=800&auto=format&fit=crop&q=80', 'views' => 1320, 'breaking' => false, 'featured' => false, 'tags' => 'সড়ক,ক্যায়াংঘাট'],

            ['title' => 'ক্যায়াংঘাটের তিনটি মৌজায় মৌসুমি ফল চাষ বাড়ানোর উদ্যোগ', 'cat' => 'kayangghat', 'rep' => 'khagrachhari-bureau',
             'excerpt' => 'ছোট চাষিদের জন্য চারা ও প্রশিক্ষণের তালিকা তৈরি করা হয়েছে বলে ইউনিয়ন সূত্রে জানানো হয়েছে।',
             'content' => '<p>ক্যায়াংঘাটের তিনটি মৌজায় মৌসুমি ফল চাষ বাড়ানোর উদ্যোগ নেওয়া হয়েছে।</p><p>ছোট চাষিদের জন্য চারা ও প্রশিক্ষণ দেওয়া হবে।</p>',
             'img' => 'https://images.unsplash.com/photo-1526304640581-d334cdbbf45e?w=800&auto=format&fit=crop&q=80', 'views' => 980, 'breaking' => false, 'featured' => false, 'tags' => 'কৃষি,ফল চাষ'],

            ['title' => 'মুবাছড়িতে বন্যা আশ্রয়কেন্দ্র হিসেবে দুটি বিদ্যালয় ঘোষণা', 'cat' => 'mubachhari', 'rep' => 'mahalchhari-correspondent',
             'excerpt' => 'গরু-ছাগলসহ সরানোর জন্য খোলা মাঠ নির্ধারণ; কন্ট্রোল রুমে ফোন করা যাবে উপজেলা নম্বরে।',
             'content' => '<p>মুবাছড়ি ইউনিয়নে বন্যা আশ্রয়কেন্দ্র হিসেবে দুটি বিদ্যালয় ঘোষণা করা হয়েছে।</p><p>গবাদি পশু রাখার জন্য খোলা মাঠ নির্ধারণ করা হয়েছে।</p>',
             'img' => 'https://images.unsplash.com/photo-1515694346937-94d85e41e6f0?w=800&auto=format&fit=crop&q=80', 'views' => 1670, 'breaking' => true, 'featured' => false, 'tags' => 'দুর্যোগ,বন্যা,মুবাছড়ি'],

            ['title' => 'মুবাছড়ির বাজারে সাপ্তাহিক নিত্যপণ্যের দাম স্থিতিশীল', 'cat' => 'mubachhari', 'rep' => 'staff-correspondent',
             'excerpt' => 'ব্যবসায়ীরা বলছেন, সড়ক পথ ঠিক থাকলে আমদানি বাড়লে দাম আরও কমবে।',
             'content' => '<p>মুবাছড়ির বাজারে সাপ্তাহিক নিত্যপণ্যের দাম স্থিতিশীল রয়েছে।</p><p>ব্যবসায়ীরা সড়ক যোগাযোগ উন্নত করার দাবি জানিয়েছেন।</p>',
             'img' => 'https://images.unsplash.com/photo-1544717305-2782549b5136?w=800&auto=format&fit=crop&q=80', 'views' => 760, 'breaking' => false, 'featured' => false, 'tags' => 'বাজার,দাম'],

            ['title' => 'মহালছড়ি বাজার থেকে মোটরসাইকেল চুরি: তিনজন আটক', 'cat' => 'aparadh', 'rep' => 'staff-correspondent',
             'excerpt' => 'থানা বলছে, জব্দ করা হয়েছে দুটি চোরাই মোটরসাইকেল; মামলা দায়েরের প্রক্রিয়া চলছে।',
             'content' => '<p>মহালছড়ি বাজার এলাকা থেকে মোটরসাইকেল চুরির ঘটনায় তিনজনকে আটক করা হয়েছে।</p><p>পুলিশ দুটি চোরাই মোটরসাইকেল জব্দ করেছে।</p>',
             'img' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=800&auto=format&fit=crop&q=80', 'views' => 2340, 'breaking' => false, 'featured' => false, 'tags' => 'চুরি,মামলা,অপরাধ'],

            ['title' => 'জমি সংক্রান্ত বিরোধে মধ্যস্থতা: ১৪টি পরিবারের সমঝোতা', 'cat' => 'aparadh', 'rep' => 'khagrachhari-bureau',
             'excerpt' => 'ইউনিয়ন ভূমি সমিতি ও থানার উপস্থিতে সমঝোতা হয়; নকশা ও রেকর্ড হালনাগাদ করার সিদ্ধান্ত হয়েছে।',
             'content' => '<p>জমি সংক্রান্ত দীর্ঘদিনের বিরোধ নিরসনে ১৪টি পরিবারের মধ্যে সমঝোতা হয়েছে।</p><p>ইউনিয়ন ভূমি সমিতি ও থানা মধ্যস্থতা করে।</p>',
             'img' => 'https://images.unsplash.com/photo-1521791136064-7986c2920216?w=800&auto=format&fit=crop&q=80', 'views' => 1120, 'breaking' => false, 'featured' => false, 'tags' => 'মামলা,জমি বিরোধ'],

            ['title' => 'উপজেলা ক্রীড়া সংস্থার আয়োজনে শীতকালীন ফুটবল লিগ শুরু', 'cat' => 'khela', 'rep' => 'sports-reporter',
             'excerpt' => '১২টি দল, দুই গ্রুপ; ফাইনাল ম্যাচের আগে মাঠ সংস্কারের সিদ্ধান্ত।',
             'content' => '<p>উপজেলা ক্রীড়া সংস্থার আয়োজনে শীতকালীন ফুটবল লিগ শুরু হয়েছে।</p><p>১২টি দল দুই গ্রুপে বিভক্ত হয়ে অংশ নিচ্ছে।</p>',
             'img' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=800&auto=format&fit=crop&q=80', 'views' => 2890, 'breaking' => false, 'featured' => false, 'tags' => 'ফুটবল,ক্রীড়া'],
        ];

        foreach ($news as $i => $item) {
            $slug = mc_slug($item['title']);

            $post = Post::updateOrCreate(['slug' => $slug], [
                'title' => $item['title'],
                'category_id' => $categories[$item['cat']] ?? $categories->first(),
                'reporter_id' => $reporters[$item['rep']] ?? null,
                'author_id' => $admin?->id,
                'excerpt' => $item['excerpt'],
                'content' => $item['content'],
                'featured_image' => $item['img'],   // ডেমো URL — অ্যাডমিন থেকে আপলোডে বদলান
                'image_caption' => 'ডেমো নমুনা ছবি',
                'image_credit' => 'Unsplash (ডেমো)',
                'status' => 'published',
                'is_featured' => $item['featured'],
                'is_breaking' => $item['breaking'],
                'allow_comments' => true,
                'published_at' => now()->subHours($i * 5 + 2),
                'views' => $item['views'],
                'location' => 'মহালছড়ি, খাগড়াছড়ি',
                'meta_title' => $item['title'],
                'meta_description' => mc_excerpt($item['excerpt'], 200),
            ]);

            Tag::syncFromString($post, $item['tags']);
        }

        // ক্যাটাগরির posts_count হালনাগাদ
        foreach (Category::all() as $category) {
            $category->update(['posts_count' => Post::published()->where('category_id', $category->id)->count()]);
        }
    }
}
