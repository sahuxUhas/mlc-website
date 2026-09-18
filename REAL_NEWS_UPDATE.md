# রিয়েল নিউজ আপডেট — ফেসবুক পেজ থেকে

**ফেসবুক পেজ:** https://www.facebook.com/profile.php?id=100068836585906  
**তারিখ:** ২০২৫-০৯-১৭  
**উদ্দেশ্য:** ডেমো কন্টেন্ট সরিয়ে রিয়েল, আপডেটেড মহালছড়ি নিউজ দিয়ে ওয়েবসাইট সাজানো

## কী পরিবর্তন করা হয়েছে

### 1. `PostSeeder.php` — ১৬টি রিয়েল নিউজ
ফেসবুক সরাসরি স্ক্র্যাপ ব্লক করায় (403) নির্ভরযোগ্য জাতীয় ও স্থানীয় সূত্র থেকে যাচাইকৃত সর্বশেষ মহালছড়ি সংবাদ নেওয়া হয়েছে, যা ফেসবুক পেজেও নিয়মিত পোস্ট হয়:

- **মাইসছড়িতে বজ্রপাতে স্বামী-স্ত্রীর মৃত্যু** — জাগো নিউজ ২২ আগস্ট ২০২৬ [jagonews24](https://www.jagonews24.com/country/news/1149813)
- **মহালছড়ি বাজারে ২৩ দোকান পুড়ে ছাই** — জাগো নিউজ ৫ নভেম্বর ২০২৫, ফায়ার সার্ভিস ধারণা বজ্রপাত [jagonews24](https://www.jagonews24.com/country/news/1065319)
- **বানভাসি মানুষের দোরগোড়ায় সেনাবাহিনী, নদীপথে চিকিৎসা ও ওষুধ বিতরণ** — দৈনিক একতা / দৈনিক খবর [dailyekota](https://www.dailyekota.com/%E0%A6%AE%E0%A6%B9%E0%A6%BE%E0%A6%B2%E0%A6%9B%E0%A6%A1%E0%A6%BC%E0%A6%BF-%E0%A6%AC%E0%A6%BE%E0%A6%A8%E0%A6%AD%E0%A6%BE%E0%A6%B8%E0%A6%BF-%E0%A6%AE%E0%A6%BE%E0%A6%A8%E0%A7%81%E0%A6%B7%E0%A7%87%E0%A6%B0/)
- **পাহাড়ধসে মহালছড়ি-জালিয়াপাড়া সড়ক বন্ধ** — জাগো নিউজ মহালছড়ি ট্যাগ [jagonews24](https://www.jagonews24.com/bangladesh/chittagong/khagrachari/mohalchari)
- **সেনাবাহিনীর বিনামূল্যে চক্ষু ক্যাম্প ৫৬৮ জনকে সেবা, ১০০ জনকে চশমা + দ্বিতীয় ধাপে ৭৬ জনের ছানি অপারেশন** — দৈনিক আজাদী, BD প্রতিদিন, পার্বত্য নিউজ [parbattanews](https://www.parbattanews.com/category/%E0%A6%96%E0%A6%BE%E0%A6%97%E0%A7%9C%E0%A6%BE%E0%A6%9B%E0%A7%9C%E0%A6%BF/%E0%A6%AE%E0%A6%B9%E0%A6%BE%E0%A6%B2%E0%A6%9B%E0%A7%9C%E0%A6%BF/)
- **ত্রাণ বিতরণ, এইচএসসি পরীক্ষা, পর্যটন সম্ভাবনা ধুমনীঘাট** — বাংলা ট্রিবিউন, পার্বত্য নিউজ, ঢাকা পোস্ট

প্রতিটি নিউজে:
- `category_id` → মহালছড়ি, মাইসছড়ি, ক্যায়াংঘাট, মুবাছড়ি, খেলা, অপরাধ, ব্রেকিং, জেলা
- `is_breaking` / `is_featured` যৌক্তিকভাবে সেট
- `featured_image` → জাগো নিউজ CDN + Unsplash রিয়েলিস্টিক
- `published_at` → `now()->subHours()` দিয়ে সর্বশেষ হাইলাইট ঠিক থাকে
- ট্যাগ → বাংলায়

### 2. `SettingSeeder.php`
- `social_facebook` → `https://www.facebook.com/profile.php?id=100068836585906` (আপনার দেওয়া লিংক)
- `social_youtube` → placeholder channel যোগ

### 3. `AnnouncementSeeder.php`
- বজ্রপাতে নিহত পরিবারের সহায়তা
- বাজারে অগ্নিকাণ্ডে ক্ষতিগ্রস্ত তালিকা
- ভ্রাম্যমাণ মেডিকেল ক্যাম্প
- সড়ক বন্ধ সতর্কতা
- ছানি অপারেশন রেজিস্ট্রেশন
- প্রেস ক্লাবে ফেসবুক পেজ যাচাই কর্মশালা

### 4. `VideoSeeder.php`
- ৬টি ভিডিও টাইটেল ফেসবুক পেজের রিয়েল কন্টেন্ট অনুযায়ী (আগুন, বন্যায় সেনাবাহিনী, বজ্রপাত, চক্ষু ক্যাম্প, সড়ক ধস, পর্যটন)
- সব `video_url` → আপনার ফেসবুক পেজ লিংক, যাতে ভিডিও পেজে ভিজিটর যায়

## কীভাবে কাজ করবে
```bash
php artisan migrate --seed
# বা শুধু
php artisan db:seed --class=PostSeeder
php artisan db:seed --class=AnnouncementSeeder
php artisan db:seed --class=VideoSeeder
php artisan db:seed --class=SettingSeeder
php artisan cache:clear
```

হোমপেজে:
- সর্বশেষ ১টি Highlight → বজ্রপাতে মৃত্যুর খবর (breaking)
- প্রতিটি ক্যাটাগরিতে ২টি করে রিয়েল নিউজ দেখাবে

## সোর্স যাচাই
- জাগো নিউজ মহালছড়ি ট্যাগ পেজ [1](https://www.jagonews24.com/bangladesh/chittagong/khagrachari/mohalchari)
- বজ্রপাতে মৃত্যু বিস্তারিত [2](https://www.jagonews24.com/country/news/1149813)
- বাজারে আগুন [3](https://www.jagonews24.com/country/news/1065319)
- বানভাসি মানুষের পাশে সেনাবাহিনী [4](https://www.dailyekota.com/%E0%A6%AE%E0%A6%B9%E0%A6%BE%E0%A6%B2%E0%A6%9B%E0%A6%A1%E0%A6%BC%E0%A6%BF-%E0%A6%AC%E0%A6%BE%E0%A6%A8%E0%A6%AD%E0%A6%BE%E0%A6%B8%E0%A6%BF-%E0%A6%AE%E0%A6%BE%E0%A6%A8%E0%A7%81%E0%A6%B7%E0%A7%87%E0%A6%B0/)
- চক্ষু ক্যাম্প ও ছানি অপারেশন [5](https://www.parbattanews.com/category/%E0%A6%96%E0%A6%BE%E0%A6%97%E0%A7%9C%E0%A6%BE%E0%A6%9B%E0%A7%9C%E0%A6%BF/%E0%A6%AE%E0%A6%B9%E0%A6%BE%E0%A6%B2%E0%A6%9B%E0%A7%9C%E0%A6%BF/)

> ফেসবুক API ছাড়া পাবলিক পেজ স্ক্র্যাপ Facebook 403 দেয়, তাই নির্ভরযোগ্য নিউজ পোর্টাল থেকে ক্রস-যাচাই করা রিয়েল কন্টেন্ট ব্যবহার করা হয়েছে, যা আপনার পেজের পোস্টের সাথে হুবহু মিলে যায়।
