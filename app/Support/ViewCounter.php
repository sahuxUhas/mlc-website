<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * ==========================================================================
 * বাস্তব পঠনসংখ্যা (Real View Count) — ফেক/ডেমো ভিউ নয়
 * ==========================================================================
 * নিয়ম:
 *   ১. সংবাদ প্রকাশের সময় ভিউ = ০।
 *   ২. যতবার সত্যিকারের ভিজিটর ডিটেইলস পেজ খোলে, ততবার কাউন্ট বাড়ে।
 *   ৩. একই ভিজিটর বারবার রিফ্রেশ করলেও বারবার গোনা হয় না —
 *      (ক) ব্রাউজার সেশনে একবার, (খ) তারপরও সময়ভিত্তিক কুলডাউন (ডিফল্ট ১২ ঘণ্টা)।
 *   ৪. বট/ক্রলার/মনিটরিং টুল ভিজিট গোনা হয় না।
 *   ৫. কাউন্ট সরাসরি SQL দিয়ে বাড়ে — মডেল ইভেন্ট ট্রিগার হয় না, তাই
 *      প্রতিটি ভিউতে পাবলিক ক্যাশ (হোম/নেভ/ব্রেকিং) বাতিল হয় না।
 *
 * গোপনীয়তা: ভিজিটর শনাক্ত করা হয় IP + User-Agent এর salted SHA-256 হ্যাশ দিয়ে;
 * ক্যাশে কখনো কাঁচা IP লেখা হয় না।
 */
class ViewCounter
{
    /** বট/ক্রলার/মনিটরিং টুল — এদের ভিজিট হিসেবে গোনা হয় না */
    private const BOT_PATTERN = '/(bot|crawler|spider|slurp|preview|facebookexternalhit|facebookcatalog'
        .'|whatsapp|telegram|twitterbot|linkedinbot|embedly|pinterest|redditbot|applebot|bingpreview'
        .'|google-inspectiontool|curl|wget|python-requests|python-urllib|aiohttp|httpx|go-http-client'
        .'|okhttp|headless|phantomjs|puppeteer|playwright|lighthouse|pingdom|uptimerobot|statuscake'
        .'|site24x7|newrelic|semrush|ahrefs|mj12|dotbot|yandex|baiduspider|duckduckbot|exabot'
        .'|ia_archiver|archive\.org_bot|monitoring|node-fetch|axios)/i';

    /**
     * ভিউ গুনে DB-তে সংরক্ষণ করে (ডিডুপ্লিকেশন মানে না হলে কিছুই করে না)।
     *
     * @return bool নতুন ভিউ গোনা হয়েছে কি না
     */
    public function record(Request $request, Model $model): bool
    {
        $key = $model->getKey();

        if (! $key) {
            return false;
        }

        $sessionKey = $this->sessionKey($model);

        // (ক) একই ব্রাউজার সেশন — একবারই
        if ($request->session()->has($sessionKey)) {
            return false;
        }

        // (খ) কুলডাউন — একই ভিজিটর নির্দিষ্ট সময়ের মধ্যে আর গোনা হবে না
        if (! $this->claimVisitorSlot($request, $model)) {
            $request->session()->put($sessionKey, now()->toDateTimeString());

            return false;
        }

        $request->session()->put($sessionKey, now()->toDateTimeString());

        $this->increment($model);

        return true;
    }

    /** সরাসরি SQL ইনক্রিমেন্ট (কোনো মডেল ইভেন্ট/টাইমস্ট্যাম্প টাচ নয়) */
    public function increment(Model $model, int $amount = 1): void
    {
        if (! $model->getKey()) {
            return;
        }

        DB::table($model->getTable())
            ->where($model->getKeyName(), $model->getKey())
            ->increment('views', $amount);

        // একই রিকোয়েস্টে যাতে নতুন সংখ্যাটিই দেখা যায়
        if ($model->getAttribute('views') !== null) {
            $model->setAttribute('views', (int) $model->getAttribute('views') + $amount);
        }
    }

    /** এই ভিজিটরকে এই সংবাদের জন্য "ফ্রি স্লট" দেওয়া যায় কি (atomic) */
    private function claimVisitorSlot(Request $request, Model $model): bool
    {
        $minutes = max(1, (int) config('views.dedupe_minutes', 720));

        return Cache::add(
            'view:'.$model->getTable().':'.$model->getKey().':'.$this->visitorHash($request),
            now()->toDateTimeString(),
            $minutes * 60               // সেকেন্ড (file/database/redis — সব driver-এ কাজ করে)
        );
    }

    /** ভিজিটর শনাক্তকরণ (কাঁচা IP নয় — salted hash) */
    private function visitorHash(Request $request): string
    {
        return hash('sha256', implode('|', [
            (string) $request->ip(),
            (string) $request->userAgent(),
            (string) config('app.key'),
        ]));
    }

    private function sessionKey(Model $model): string
    {
        return 'mc_viewed_'.$model->getTable().'_'.$model->getKey();
    }

    /** UA দেখে বট/ক্রলার/মনিটর শনাক্তকরণ */
    public static function isBot(?string $userAgent): bool
    {
        $ua = trim((string) $userAgent);

        if ($ua === '') {
            return true;   // ব্রাউজার ছাড়া কিছু (skript/monitor) ভিজিট নয়
        }

        return (bool) preg_match(self::BOT_PATTERN, $ua);
    }
}
