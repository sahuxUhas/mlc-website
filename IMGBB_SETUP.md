# ছবি হোস্টিং (ImgBB) সেটআপ — নিরাপদ ইমেজ সিস্টেম

> ⚠️ **জরুরি:** পুরোনো সংস্করণে এই ডকুমেন্টে এবং `config/services.php` / `.env.example` /
> `SettingSeeder` এ API Key টি লেখা ছিল এবং Git-এ commit হয়ে গেছে। সেই Key **আর নিরাপদ নয়** —
> https://api.imgbb.com/ থেকে একটি **নতুন Key** তৈরি করে `.env` এ বসান এবং পুরোনোটিকে
> ImgBB ড্যাশবোর্ড/অ্যাকাউন্ট থেকে বাতিল (revoke) করুন।

## এক নজরে — ছবি কোথায় যায়

```
Admin → ছবি নির্বাচন → Laravel Backend (MediaUploader → ImageManager)
      → ImgBB API (API Key শুধু .env থেকে)
      → MySQL-এ শুধু রেফারেন্স (hosting URL / provider id)
      → ওয়েবসাইটে signed proxy URL:  /img/{token}?s=signature
```

* API Key **কখনো** ফ্রন্টএন্ড, JS, admin UI বা API response-এ যায় না।
* ছবি MySQL-এ **BLOB হিসেবে সংরক্ষিত হয় না** — শুধু URL/ID রেফারেন্স।
* ImgBB-এর raw URL (`https://i.ibb.co/...`) কোথাও টেক্সট বা `src` হিসেবে দেখানো হয় না;
  `mc_image()` সব remote ছবিকে নিজের ডোমেইনের signed proxy URL-এ বদলে দেয়।

## ধাপ ১ — `.env` (শুধু সার্ভারে, কখনো Git-এ নয়)

```env
IMGBB_API_KEY=এখানে-নতুন-কী
IMGBB_ENABLED=true
IMGBB_TIMEOUT=30

# image system (ভবিষ্যতে হোস্টিং বদলাতে এখানেই)
IMAGE_PROVIDER=imgbb            # imgbb | local
IMAGE_LOCAL_FALLBACK=true       # provider নিষ্ক্রিয়/কনফিগার না থাকলে public/uploads
MC_UPLOAD_MAX_KB=4096           # প্রতিটি ছবির সর্বোচ্চ সাইজ (KB)
MC_UPLOAD_MAX_FILES=20          # একসাথে সর্বোচ্চ কতটি

# ছবি proxy (raw hosting URL লুকানোর জন্য)
IMAGE_PROXY_ENABLED=true
IMAGE_PROXY_TTL=10080           # ৭ দিন
IMAGE_PROXY_HOSTS=i.ibb.co,ibb.co,imgbb.com,i.ibb.co.com
IMAGE_PROXY_ALL=false
```

`.env.example`-এ সবসময় খালি রাখুন: `IMGBB_API_KEY=` (কোনো আসল Key কখনো commit নয়)।

## ধাপ ২ — Deploy কমান্ড (cPanel)

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate          # APP_KEY খালি থাকলে (proxy signature এর জন্য দরকার)
php artisan migrate --seed        # নতুন media/provider কলাম যোগ হবে
php artisan optimize:clear        # config/route/view cache পরিষ্কার
php artisan config:cache && php artisan route:cache && php artisan view:cache
chmod -R 775 storage bootstrap/cache public/uploads
```

## ধাপ ৩ — পরীক্ষা

1. `.env` সেভ করার পর `php artisan optimize:clear` চালান।
2. **Admin → মিডিয়া লাইব্রেরি** — উপরে provider ব্যানার দেখাবে (ImgBB ক্লাউড হোস্টিং / সার্ভার স্টোরেজ)।
3. **Admin → নতুন সংবাদ** — ফিচার্ড ইমেজ বা গ্যালারিতে ছবি দিয়ে সংরক্ষণ করুন।
4. ছবির `src` চেক করুন: `/img/…?s=…` হবে — `i.ibb.co` দেখা যাবে না।
5. ব্রাউজারে ছবি লোড না হলে `public/uploads/….htaccess`/৭৭৫ permission এবং
   `IMAGE_PROXY_ENABLED=true` কিনা দেখুন।

## কীভাবে কাজ করে (কোড ম্যাপ)

| ফাইল | কাজ |
|---|---|
| `config/images.php` | provider, সীমা, সাইজ, proxy হোস্ট allowlist |
| `app/Services/MediaUploader.php` | ভ্যালিডেশন (extension+MIME, ডাবল এক্সটেনশন, ছবির ভেতরে স্ক্রিপ্ট স্ক্যান, সাইজ) → `Media` রেকর্ড |
| `app/Services/Images/ImageManager.php` | কোন provider-এ যাবে (imgbb/local) + fallback নীতি |
| `app/Services/Images/ImgbbProvider.php` | base64 upload, delete_url দিয়ে best-effort ডিলিট, key কখনো return করে না |
| `app/Services/Images/LocalProvider.php` | `public/uploads` (ডকুমেন্ট/PDF ও fallback) |
| `app/Support/ImageUrl.php` | signed token + HMAC signature, host allowlist (SSRF নিরাপদ) |
| `app/Http/Controllers/ImageProxyController.php` | `/img/{token}?s=…` → হোস্টিং থেকে ছবি এনে ক্যাশ করে সার্ভ করে |
| `app/Support/ContentRenderer.php` | কনটেন্টের `{{media:ID}}` → signed `<figure><img>` |
| `app/Support/HtmlSanitizer.php` | সংরক্ষণের আগে XSS পরিষ্কার |
| `app/Http/Controllers/Admin/NewsController.php` | Featured/Gallery/Editor ছবি আপলোড ও DB রেফারেন্স |

## হোস্টিং ভবিষ্যতে বদলাতে চাইলে

1. `.env` এ `IMAGE_PROVIDER=local` (বা নতুন driver) দিন — পুরোনো ImgBB ছবি proxy দিয়ে আগের মতোই চলবে।
2. নতুন হোস্টিং যোগ করতে `app/Services/Images/` এ নতুন ক্লাস তৈরি করে `ImageManager::providerFor()`
   তে যুক্ত করুন — বাকি কোড (News, Media Library, অন্যান্য ভিউ) অপরিবর্তিত থাকবে।

## Error Handling

| পরিস্থিতি | আচরণ |
|---|---|
| ভুল ফরম্যাট / fake MIME / ডাবল এক্সটেনশন | আপলোড বাতিল, বাংলা error message (failsafe, 500 নয়) |
| সাইজ সীমার বেশি | আপলোড বাতিল + সঠিক বার্তা |
| Provider কনফিগার নেই (`IMGBB_API_KEY` খালি) | `IMAGE_LOCAL_FALLBACK=true` হলে `public/uploads`, নইলে স্পষ্ট বার্তা |
| ImgBB API ব্যর্থ (কী সঠিক কিন্তু সার্ভিস down) | কোনো নীরব fallback নয় — অ্যাডমিনকে বাংলা error; ছবি হারায় না |
| Proxy fetch ব্যর্থ | placeholder ছবি দেখায়, ধ্বংসাত্মক ক্যাশ হয় না (পরের রিকোয়েস্টে আবার চেষ্টা) |
| ছবি ডিলিট | DB রেফারেন্স দেখে ডিলিট; হোস্টিং ডিলিট best-effort (`delete_url`) |

## সীমাবদ্ধতা / পরবর্তী কাজ

- Expiration support (ImgBB temporary image) এখনো ব্যবহার করা হয়নি (`IMGBB_EXPIRATION` ভবিষ্যতের জন্য)।
- খুব বড় ছবি আপলোডের আগে resize/compress করলে ব্যান্ডউইথ বাঁচবে।
- CDN ব্যবহার করলে `public/uploads` এর জন্য আলাদা নীতি দরকার হতে পারে।

## আরও দেখুন

- `NEWS_EDITOR_GUIDE.md` — সংবাদ তৈরি/সম্পাদনা পেজের পূর্ণ গাইড
- `PHP_ONLY_DEPLOY.md` — cPanel deploy
