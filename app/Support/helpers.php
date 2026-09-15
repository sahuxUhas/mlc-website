<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

if (! function_exists('mc_slug')) {
    /**
     * বাংলা + ইংরেজি টাইটেল থেকে SEO-friendly স্লাগ তৈরি।
     * ডেমোর mcSlug() লজিকের সাথে সামঞ্জস্যপূর্ণ — বাংলা অক্ষর অক্ষুণ্ণ থাকে।
     */
    function mc_slug(?string $text): string
    {
        $text = trim((string) $text);
        if ($text === '') {
            return 'item-'.Str::lower(Str::random(6));
        }

        // বাংলা (\u0980-\u09FF), ইংরেজি, সংখ্যা ও ড্যাশ বাদে সব বাদ
        $text = preg_replace('/[^\x{0980}-\x{09FF}a-zA-Z0-9\s-]/u', '', $text);
        $text = preg_replace('/\s+/u', '-', $text);
        $text = preg_replace('/-+/u', '-', $text);
        $text = trim($text, '-');

        return $text === '' ? 'item-'.Str::lower(Str::random(6)) : Str::lower($text);
    }
}

if (! function_exists('mc_unique_slug')) {
    /** একই টেবিলে স্লাগ ইউনিক রাখতে প্রয়োজনে -2, -3 যুক্ত করে */
    function mc_unique_slug(Model $model, ?string $slug, ?string $column = 'slug'): string
    {
        $slug = $slug ?: 'item';
        $base = $slug;
        $i = 2;

        while (
            $model->newQueryWithoutScopes()
                ->where($column, $slug)
                ->when($model->exists, fn ($q) => $q->whereKeyNot($model->getKey()))
                ->exists()
        ) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}

if (! function_exists('bn_num')) {
    /** ইংরেজি সংখ্যা → বাংলা সংখ্যা (ডেমোর toBanglaNum/mcBn এর সমতুল্য) */
    function bn_num(int|float|string $number): string
    {
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];

        return str_replace($en, $bn, (string) $number);
    }
}

if (! function_exists('bn_count')) {
    /** হাজার/লাখ সহ সংক্ষিপ্ত বাংলা কাউন্ট — ভিউ কাউন্টারের জন্য */
    function bn_count(int $n): string
    {
        return bn_num(number_format($n));
    }
}

if (! function_exists('bn_date')) {
    /**
     * বাংলা তারিখ ফরম্যাট — ডেমোর formatBanglaDate() এর সমতুল্য।
     * উদাহরণ: "১৪ সেপ্টেম্বর ২০২৬, সকাল ৯:৪০"
     */
    function bn_date($date, bool $withTime = true): string
    {
        if (empty($date)) {
            return '';
        }

        $ts = $date instanceof \DateTimeInterface ? $date->getTimestamp() : strtotime((string) $date);
        if ($ts === false) {
            return '';
        }

        $months = [
            1 => 'জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন',
            'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর',
        ];
        $days = ['রবিবার', 'সোমবার', 'মঙ্গলবার', 'বুধবার', 'বৃহস্পতিবার', 'শুক্রবার', 'শনিবার'];

        $h24 = (int) gmdate('H', $ts);
        $min = (int) gmdate('i', $ts);

        $period = match (true) {
            $h24 < 4  => 'রাত',
            $h24 < 6  => 'ভোর',
            $h24 < 12 => 'সকাল',
            $h24 < 16 => 'দুপুর',
            $h24 < 18 => 'বিকাল',
            $h24 < 20 => 'সন্ধ্যা',
            default   => 'রাত',
        };

        $h12 = $h24 % 12 ?: 12;

        $out = bn_num((int) gmdate('j', $ts)).' '.$months[(int) gmdate('n', $ts)].' '.bn_num((int) gmdate('Y', $ts));

        if ($withTime) {
            $out .= ', '.$period.' '.bn_num($h12).':'.str_pad(bn_num($min), 2, '০', STR_PAD_LEFT);
        }

        return $out;
    }
}

if (! function_exists('bn_day_date')) {
    /** হেডারের জন্য পূর্ণ বাংলা তারিখ — "সোমবার, ১৫ সেপ্টেম্বর ২০২৬" */
    function bn_day_date($date = null): string
    {
        $ts = $date ? (strtotime((string) $date) ?: time()) : time();
        $days = ['রবিবার','সোমবার','মঙ্গলবার','বুধবার','বৃহস্পতিবার','শুক্রবার','শনিবার'];
        $months = [1=>'জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'];

        // Asia/Dhaka = UTC+6
        $ts += 6 * 3600;

        return $days[(int) gmdate('w', $ts)].', '.bn_num((int) gmdate('j', $ts)).' '
             .$months[(int) gmdate('n', $ts)].' '.bn_num((int) gmdate('Y', $ts));
    }
}

if (! function_exists('bn_ago')) {
    /** "৩ ঘণ্টা আগে" ধরনের আপেক্ষিক সময় */
    function bn_ago($date): string
    {
        if (empty($date)) {
            return '';
        }

        $ts = $date instanceof \DateTimeInterface ? $date->getTimestamp() : strtotime((string) $date);
        $diff = max(0, time() - $ts);

        return match (true) {
            $diff < 60        => 'এইমাত্র',
            $diff < 3600      => bn_num(intdiv($diff, 60)).' মিনিট আগে',
            $diff < 86400     => bn_num(intdiv($diff, 3600)).' ঘণ্টা আগে',
            $diff < 2592000   => bn_num(intdiv($diff, 86400)).' দিন আগে',
            default           => bn_date($date, false),
        };
    }
}

if (! function_exists('site_setting')) {
    /** গ্লোবাল সাইট সেটিংস পড়ার শর্টকাট */
    function site_setting(string $key, mixed $default = null): mixed
    {
        return \App\Models\Setting::get($key, $default);
    }
}

if (! function_exists('mc_placeholder_svg')) {
    /** ডেমোর ব্র্যান্ডেড PLACEHOLDER_IMG — ছবি লোড না হলে ব্যবহৃত হয় */
    function mc_placeholder_svg(): string
    {
        $svg = "<svg xmlns='http://www.w3.org/2000/svg' width='800' height='450'>"
             ."<rect width='800' height='450' fill='#EAF7EC'/>"
             ."<path d='M0 350 L170 215 L300 330 L440 190 L580 335 L700 250 L800 350 L800 450 L0 450 Z' fill='#C5E7C8'/>"
             ."<path d='M0 400 L210 305 L380 400 L540 300 L690 405 L800 355 L800 450 L0 450 Z' fill='#1F7A3D' opacity='0.22'/>"
             ."<text x='400' y='120' font-family='Helvetica,Arial,sans-serif' font-size='34' font-weight='700' fill='#0B0B0B' text-anchor='middle' letter-spacing='4'>mahalcharinews.com</text>"
             ."<circle cx='400' cy='160' r='6' fill='#D50E18'/></svg>";

        return 'data:image/svg+xml,'.rawurlencode($svg);
    }
}

if (! function_exists('mc_excerpt')) {
    /** HTML থেকে নিরাপদ সংক্ষিপ্ত বিবরণ */
    function mc_excerpt(?string $html, int $limit = 160): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $html)));
        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, $limit)).'…';
    }
}

if (! function_exists('mc_ad')) {
    /** নির্দিষ্ট পজিশনের সক্রিয় বিজ্ঞাপন (ক্যাশেড) */
    function mc_ad(string $position): ?\App\Models\Advertisement
    {
        return \Illuminate\Support\Facades\Cache::remember(
            'site.ads.'.$position,
            now()->addMinutes(10),
            fn () => \App\Models\Advertisement::forPosition($position)->first()
        );
    }
}
