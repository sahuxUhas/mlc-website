# `demo/` — স্ট্যাটিক ডেমো (Cloudflare Pages / যেকোনো static host)

> ⚠️ এটি **Laravel অ্যাপের অংশ নয়** — কোনো route/view/asset নয়।
> শুধু নতুন News Details পেজের layout রিভিউ/ডেমো দেখানোর জন্য একটি static HTML,
> যা `public/css/*` ও `public/js/site.js` (সাইটের আসল ফাইল) ব্যবহার করে।
> রিভিউ শেষে ফোল্ডারটি ডিলিট করা যায় — অ্যাপে কোনো প্রভাব পড়বে না।

## কেন Workers-এ Laravel চলেনি

Cloudflare **Workers**-এর রানটাইম JavaScript/WASM — PHP (Laravel 11), MySQL,
Blade, `storage/` কিছুই চলে না। তাই repo-র সাথে Workers Git integration লাগালে
প্রতিটি push-এ build fail দেখাবে (কোডের সমস্যা নয়, প্ল্যাটফর্মের সীমা)।
Laravel চালাতে হবে PHP-সাপোর্টেড হোস্টে (cPanel / Render / Railway / VPS)।

## ১) Cloudflare Pages-এ এই ডেমো (PHP ছাড়াই, ২ মিনিট)

**CLI (সবচেয়ে সহজ):**
```bash
# repo root থেকে
npx wrangler pages deploy demo --project-name mlc-news-details-demo
```
→ পাওয়া যাবে `https://mlc-news-details-demo.pages.dev`

**Dashboard (Git integration):**
Cloudflare Dashboard → Workers & Pages → **Create → Pages → Connect to Git** →
repo `sahuxUhas/mlc-website` → Branch: `arena/01a0a859-mlc-website`
- Framework preset: **None**
- Build command: *(ফাঁকা রাখুন)*
- Build output directory: **`demo`**
→ ডিপ্লয় শেষে `*.pages.dev` URL।

## ২) আসল Laravel অ্যাপ Cloudflare URL-এ (Cloudflare Tunnel)

PHP থাকা কোনো মেশিনে (লোকাল বা VPS):

```bash
composer install
php artisan key:generate

# ডেমো ডেটার জন্য SQLite (MySQL সার্ভার ছাড়াই চলে)
php artisan config:clear
sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$(pwd)/database/demo.sqlite|" .env
touch database/demo.sqlite
php artisan migrate --seed

php artisan serve            # http://127.0.0.1:8000
```

অন্য টার্মিনালে (ফ্রি, অ্যাকাউন্ট লাগে না):
```bash
cloudflared tunnel --url http://127.0.0.1:8000
```
→ `https://xxxx.trycloudflare.com` — এটাই আসল অ্যাপের ডেমো URL (Cloudflare এজ দিয়ে)।

## ৩) স্থায়ী ডেমো সার্ভার (দরকার হলে)

Render / Railway / Fly.io — Docker দিয়ে PHP-FPM + SQLite চালানো যায়।
`Dockerfile` + `render.yaml` এই রিপোতে নেই; দরকার হলে যোগ করে দেওয়া হবে।
