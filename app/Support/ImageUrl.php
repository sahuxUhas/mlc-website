<?php

namespace App\Support;

use Illuminate\Support\Facades\URL;

/**
 * Image URL নিরাপত্তা ও Proxy লিংক তৈরি।
 *
 * কেন দরকার:
 *  - MySQL-এ ছবির reference (ImgBB URL) সংরক্ষিত থাকে, কিন্তু ব্রাউজারে সেই
 *    raw URL কখনো যায় না। `publicUrl()` সেটিকে নিজের ডোমেইনের signed
 *    /img/{token}?s=… লিংকে রূপান্তর করে (raw hosting path লুকানো থাকে)।
 *  - Signature ছাড়া কেউ এই route ব্যবহার করে যেকোনো URL proxy করতে পারবে না ⇒ SSRF নিরাপদ।
 */
class ImageUrl
{
    /** raw URL টি কি proxy করার যোগ্য? (http/https + allowlist host) */
    public static function isProxyable(?string $url): bool
    {
        $host = self::host($url);

        if ($host === null) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return false;   // IP দিয়ে host করলে proxy করা হয় না (SSRF প্রতিরোধ)
        }

        if ((bool) config('images.proxy.all_hosts', false)) {
            return true;
        }

        foreach ((array) config('images.proxy.hosts', []) as $allowed) {
            $allowed = strtolower(trim((string) $allowed));
            if ($allowed !== '' && ($host === $allowed || str_ends_with($host, '.'.$allowed))) {
                return true;
            }
        }

        return false;
    }

    /**
     * যেকোনো ছবির পাথ/URL → ব্রাউজারে ব্যবহারযোগ্য নিরাপদ URL।
     * remote URL হলে signed proxy URL, অন্যথায় null (caller asset() ব্যবহার করবে)।
     */
    public static function publicUrl(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '' || ! preg_match('~^(?:https?:)?//~i', $path)) {
            return null;
        }

        $absolute = self::absolute($path);

        // proxy বন্ধ থাকলে বা হোস্ট allowlist এ না থাকলে raw URL (পুরোনো বাহ্যিক ছবি অক্ষত থাকে)
        if (! (bool) config('images.proxy.enabled', true) || ! self::isProxyable($absolute)) {
            return $absolute;
        }

        return self::signedUrl($absolute);
    }

    /** proxy route-এর signed URL */
    public static function signedUrl(string $absoluteUrl): string
    {
        $token = self::token($absoluteUrl);

        return URL::route('image.proxy', ['token' => $token]).'?s='.self::signature($token);
    }

    /** signed token → মূল URL (যাচাই ব্যর্থ হলে null) */
    public static function decode(string $token, string $signature): ?string
    {
        if (! hash_equals(self::signature($token), $signature)) {
            return null;
        }

        $url = self::base64UrlDecode($token);

        if ($url === null || ! preg_match('~^https?://~i', $url)) {
            return null;
        }

        return self::isProxyable($url) ? $url : null;
    }

    public static function token(string $absoluteUrl): string
    {
        return rtrim(strtr(base64_encode($absoluteUrl), '+/', '-_'), '=');
    }

    public static function signature(string $token): string
    {
        return hash_hmac('sha256', 'mc-image:'.$token, self::signingKey());
    }

    /** ছবির এক্সটেনশন নিরাপদ কিনা (proxy response-এ ব্যবহৃত) */
    public static function extensionOf(string $url): string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'], true) ? $extension : 'jpg';
    }

    /** অ্যাডমিন UI-তে দেখানোর জন্য শুধু ফাইলনাম (raw URL নয়) */
    public static function safeLabel(?string $url): string
    {
        $path = (string) parse_url((string) $url, PHP_URL_PATH);
        $name = basename($path);

        return $name !== '' ? $name : 'ছবি';
    }

    /* ---------------- private helpers ---------------- */

    private static function absolute(string $path): string
    {
        if (str_starts_with($path, '//')) {
            return 'https:'.$path;
        }

        return $path;
    }

    private static function host(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        if (str_starts_with($url, '//')) {
            $url = 'https:'.$url;
        }

        if (! preg_match('~^https?://~i', $url)) {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && $host !== '' ? strtolower($host) : null;
    }

    private static function base64UrlDecode(string $token): ?string
    {
        $normalized = strtr($token, '-_', '+/');
        $padding = strlen($normalized) % 4;

        if ($padding > 0) {
            $normalized .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($normalized, true);

        return $decoded === false ? null : $decoded;
    }

    private static function signingKey(): string
    {
        $key = (string) config('app.key', 'mc-image-proxy');

        // APP_KEY সাধারণত base64:... আকারে থাকে
        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);
            if ($decoded !== false && $decoded !== '') {
                return $decoded;
            }
        }

        return $key !== '' ? $key : 'mc-image-proxy';
    }
}
