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

**নিরাপত্তা:** CSRF · XSS sanitize middleware · SQLi (Eloquent/pdo binding) · Role & Permission (4 রোল) · bcrypt hashing · Rate Limiting (login + comment) · Secure upload validation (MIME+extension+PHP-tag check) · Session hardening

**পারফরম্যান্স:** DB indexing + composite index · Eager loading · Pagination · Caching (settings/menu/breaking/ads/home) · Lazy loading · Gzip + expires (`.htaccess`)

## বিল্ড (ডেভেলপমেন্টে শুধু)
```bash
npx tailwindcss -c tailwind.config.js -i resources/css/app.css -o public/css/app.css --minify
```
`public/css/app.css` + `site.css` + `admin.css` কমিট করা আছে — **প্রোডাকশনে কোনো বিল্ড লাগবে না**।
