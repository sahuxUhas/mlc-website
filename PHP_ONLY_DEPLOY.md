# Only PHP Deployment Guide - cPanel

**এই প্রজেক্ট শুধু PHP দিয়ে চলবে, Node.js লাগবে না।**

## সমস্যা: ওয়েবসাইটে ঢুকা যাচ্ছে না

### কারণ ১: APP_KEY খালি ছিল
✅ **ফিক্স করা হয়েছে:** `.env` এ APP_KEY জেনারেট করা হয়েছে
```env
APP_KEY=base64:xFIqDl/qi9Ou78Kf3USvniD012OWWH3J02xMvt4z4JY=
```

### কারণ ২: vendor ফোল্ডার নেই
Laravel এর `vendor/` ফোল্ডার ছাড়া চলবে না। cPanel এ `composer install` চালাতে হবে।

`public/index.php` এখন চেক করে যদি vendor না থাকে, সুন্দর বাংলা error দেখাবে।

### কারণ ৩: SESSION_DRIVER=database ছিল
Database migrate না করলে session table না থাকায় 500 error। এখন `SESSION_DRIVER=file` করা হয়েছে।

### কারণ ৪: .htaccess এ php_flag engine off
PHP-FPM এ `php_flag` allow না, 500 error হতো। এখন compatible .htaccess বানানো হয়েছে।

## cPanel এ Only PHP Deploy Steps

### 1. Files Upload
- cPanel File Manager → `public_html` এর parent folder এ (যেমন `~/mahalcharinews.com/`) পুরো প্রজেক্ট upload করুন
- `public/` এর ভেতরের সব ফাইল `public_html/` এ কপি করুন **অথবা** cPanel → Domains → Document Root `public/` এ সেট করুন (Recommended)

### 2. Database তৈরি
- cPanel → MySQL Databases → নতুন DB তৈরি (যেমন `mcnews_db`)
- User তৈরি ও DB তে Add করুন
- `.env` এ DB_DATABASE, DB_USERNAME, DB_PASSWORD বসান

### 3. cPanel Terminal থেকে (Only PHP, No Node needed)
```bash
cd ~/mahalcharinews.com

# Composer install (PHP only)
composer install --no-dev --optimize-autoloader

# Key generate (already done, but run again for safety)
php artisan key:generate

# Migrate + Seed (real news from FB page)
php artisan migrate --seed

# Storage link
php artisan storage:link

# Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Permission
chmod -R 775 storage bootstrap/cache public/uploads
```

### 4. Check Health
- ব্রাউজারে যান: `https://yourdomain.com/health.php`
- এখানে PHP version, extensions, vendor, APP_KEY, ImgBB, storage writable সব check দেখাবে
- সব ✅ হলে `/` এ যান

### 5. Admin Login
- `/admin/login`
- Email: `admin@mahalcharinews.com`
- Password: `ChangeMe@123` (প্রোডাকশনে অবশ্যই বদলান)

## No Node.js Needed

- `public/css/app.css` (31KB) আগে থেকেই build করা আছে
- `public/css/admin.css` ও `site.css` commit করা আছে
- `package.json` শুধু dev এর জন্য, production এ লাগবে না

## ImgBB Setup (Only PHP)

`.env` এ আগে থেকেই আছে:
```env
IMGBB_API_KEY=4bfac8cf6fa4714236c08292299d2862
IMGBB_ENABLED=true
```

- Admin → Media Library থেকে আপলোড করলে ছবি `i.ibb.co` তে যাবে
- Local fallback আছে - ImgBB fail হলে `public/uploads/` এ যাবে

## Cloudflare Deploy Fix (যদি Cloudflare ব্যবহার করেন)

আগে `npx wrangler versions upload` এ error ছিল:
```
Missing entry-point
```

এখন `wrangler.jsonc` যোগ করা হয়েছে:
```json
{
  "name": "mlc-website",
  "main": "./src/index.js",
  "assets": { "directory": "./legacy-demo" }
}
```

`legacy-demo/index.html` এ 16টি রিয়েল নিউজ বসানো হয়েছে, তাই Cloudflare static deploy করলেও রিয়েল নিউজ দেখাবে।

**কিন্তু মনে রাখবেন:** Cloudflare Workers এ PHP চলে না। Full Laravel চালাতে হলে cPanel PHP hosting লাগবে। Cloudflare শুধু static demo এর জন্য।

## Troubleshooting

### 500 Error
- `/health.php` দেখুন
- `storage/logs/laravel.log` দেখুন
- `chmod -R 775 storage bootstrap/cache` চালান

### White Screen
- `APP_DEBUG=true` করুন `.env` এ, error দেখুন, তারপর false করুন

### Images Not Showing
- `php artisan storage:link` চালান
- `public/uploads/.gitkeep` আছে কিনা চেক
- ImgBB enabled হলে `i.ibb.co` URL আসবে, সেটা normal

### Database Connection Error
- `.env` এ DB credentials ঠিক আছে কিনা
- cPanel → MySQL → User কে DB তে All Privileges দিন

## Support

- Facebook Page: https://www.facebook.com/profile.php?id=100068836585906
- Health: /health.php
- Docs: README.md, REAL_NEWS_UPDATE.md, IMGBB_SETUP.md
