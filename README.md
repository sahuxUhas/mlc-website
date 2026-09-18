# দৈনিক মহালছড়ি নিউজ — Production Laravel News Portal

**Tagline:** পাহাড়ের কথা বলে • **Domain:** mahalcharinews.com

PHP 8.2+ / Laravel 11 / Blade / MySQL / Tailwind CSS — **cPanel Shared Hosting** উপযোগী। কোনো Node.js/Python সার্ভার নির্ভরতা নেই।

> মূল React ডেমো (`index.html`, `admin.html`) `legacy-demo/` এ সংরক্ষিত — ডিজাইন, কালার, টাইপোগ্রাফি, কার্ড, মোবাইল বটম নেভ ও ডার্ক মোড হুবহু নতুন Blade ভিউতে রূপান্তরিত।

## cPanel ডিপ্লয়মেন্ট

```bash
# ১. ফাইল আপলোড (public_html এর parent এ পুরো প্রজেক্ট, বা subdomain root এ)
# ২. ডাটাবেস তৈরি করে .env কনফিগার করুন
cp .env.example .env
# DB_DATABASE / DB_USERNAME / DB_PASSWORD বসান

# ৩. নির্ভরতা ও কী
composer install --no-dev --optimize-autoloader
php artisan key:generate

# ৪. টেবিল + ডেমো ডেটা
php artisan migrate --seed

# ৫. স্টোরেজ লিংক ও অপ্টিমাইজেশন
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache

# ৬. পারমিশন
chmod -R 775 storage bootstrap/cache
```

**document_root `public/` এ সেট করুন** (cPanel → Domains)। সেট করা না গেলে `deploy/` গাইড দেখুন।

**Cron Job** (cPanel → Cron Jobs) — স্কেজিউলড নিউজ অটো-পাবলিশের জন্য:
```
* * * * * cd /home/USER/mahalcchari && php artisan schedule:run >> /dev/null 2>&1
```

### ডিফল্ট লগইন (প্রোডাকশনে অবশ্যই পাসওয়ার্ড বদলান)
| রোল | ইমেইল | পাসওয়ার্ড |
|---|---|---|
| Super Admin | admin@mahalcharinews.com | ChangeMe@123 |
| Editor | editor@mahalcharinews.com | ChangeMe@123 |
| Reporter | reporter@mahalcharinews.com | ChangeMe@123 |
| Moderator | moderator@mahalcharinews.com | ChangeMe@123 |

## ফিচার
**পাবলিক:** Home (Breaking → তারিখ/সময় → 728x90 অ্যাড → সর্বশেষ ১টি Highlight → প্রতি ক্যাটাগরিতে ২টি) · সর্বশেষ · Category · Full Article · Videos · Announcements · Gallery/Album · Search · E-paper · About · Contact · Advertise · Static Pages · Reporters · Tag · Sitemap/RSS/robots

**অ্যাডমিন:** Dashboard (১২+ স্ট্যাট, Recent/Scheduled/Most-Read, Activity, Quick Actions) · News CRUD + Draft/Pending/Publish/Unpublish/Schedule/Archive/Search/Filter/Sort/Pagination/Bulk · একাধিক ছবি + Image Order · Category & Subcategory · Reporter · Media Library · Breaking News · Videos · Announcements · Comments (Pending/Approved/Rejected/Spam/Bulk/Reported) · Advertisements (৮টি Position) · Albums + Reorder · Menu Manager (Mobile Bottom Nav সহ) · Static Pages · Settings · SEO · Users/Roles · Trash (Restore/Permanent/Empty) · Activity Log

## অ্যাডমিন প্যানেল থেকে যা যা পরিবর্তন করা যায়

**অ্যাডমিন → সাইট সেটিংস** — একটি ফর্মেই ৯টি ট্যাব:

| ট্যাব | যা নিয়ন্ত্রণ করে |
|---|---|
| সাধারণ | ওয়েবসাইটের নাম, পূর্বপদ, **লোগোর পাশের নাম (২ অংশ)**, ট্যাগলাইন, ডোমেইন, লোগো, ফেভিকন, অবস্থান, টাইমজোন |
| যোগাযোগ | ইমেইল, ফোন, ঠিকানা, নিউজরুম ইমেইল |
| সোশ্যাল মিডিয়া | ৮টি নির্দিষ্ট ঘর **+ যত খুশি লিংকের রিপিটার** (নাম/আইকন/URL/রঙ) |
| হেডার | স্টিকি হেডার, তারিখ, লাইভ সময়, ডার্ক-লাইট বোতাম, সার্চ বোতাম, তারিখ-স্ট্রিপ, ব্রেকিং লেবেল, "আরও" লেবেল, হাইলাইট বোতাম (লেখা + লিংক) |
| ফুটার | পরিচিতি/বিভাগ/লিংক/যোগাযোগ কলাম চালু-বন্ধ, সোশ্যাল আইকন, লিংক কলামের শিরোনাম, কপিরাইট, ফুটার টেক্সট |
| আচরণ ও মন্তব্য | **প্রতি পেজে সংবাদ (4–60)**, মন্তব্য চালু/অটো-অনুমোদন, **মেইনটেন্যান্স মোড + বার্তা** |
| ছোটখাটো লেখা | সার্চ ঘরের লেখা, সার্চ বোতাম, সার্চের নিচের লেবেল, হোমের "সর্বশেষ সংবাদ" শিরোনাম |
| মিডিয়া ও ImgBB | ImgBB চালু/বন্ধ, API Key, Expiration |
| ইন্টিগ্রেশন | Google Analytics, কাস্টম Head/Body HTML |

**অ্যাডমিন → মেনু ম্যানেজার** — ৫টি লোকেশন: প্রধান মেনু · টপ বার (সাইড মেনু ড্রয়ারে দেখায়) · "আরও" ড্রপডাউন · ফুটার · মোবাইল বটম নেভিগেশন।

**নোট:** ছবি আপলোডের ঘরের পাশে "অথবা সরাসরি URL" ঘরে URL বসালেও সেটি সংরক্ষিত হয় (আগে উপেক্ষা হতো)।

**নিরাপত্তা:** CSRF · XSS sanitize middleware · SQLi (Eloquent/pdo binding) · Role & Permission (4 রোল) · bcrypt hashing · Rate Limiting (login + comment) · Secure upload validation (MIME+extension+PHP-tag check) · Session hardening

**পারফরম্যান্স:** DB indexing + composite index · Eager loading · Pagination · Caching (settings/menu/breaking/ads/home) · Lazy loading · Gzip + expires (`.htaccess`)

## বিল্ড (ডেভেলপমেন্টে শুধু)
```bash
npx tailwindcss -c tailwind.config.js -i resources/css/app.css -o public/css/app.css --minify
```
**ভিউতে নতুন Tailwind ক্লাস যোগ করলে এই বিল্ড চালিয়ে `public/css/app.css` কমিট করতে হবে** — না হলে নতুন ক্লাসের স্টাইল প্রোডাকশনে আসবে না।

## যাচাই (PHP ছাড়াও)
স্যান্ডবক্স/সিআই-তে PHP না থাকলে কাঠামোগত যাচাই চালানো যায়:
```bash
node tools/verify.mjs
```
এটি Blade ডিরেক্টিভ ব্যালান্স, PHP ব্রেস/কোট, কম্পোনেন্ট ও ভিউ পথ, রাউটের নাম, সেটিংস কী এবং কম্পাইল করা CSS-এ ক্লাসের উপস্থিতি পরীক্ষা করে। (Laravel টেস্ট চালাতে হলে `composer install && php artisan test` প্রয়োজন।)
`public/css/app.css` + `site.css` + `admin.css` কমিট করা আছে — **প্রোডাকশনে কোনো বিল্ড লাগবে না**।
