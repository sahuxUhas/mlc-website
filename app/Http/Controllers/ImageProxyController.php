<?php

namespace App\Http\Controllers;

use App\Support\ImageUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Image Proxy — হোস্টিং (ImgBB) এর ছবি নিজের ডোমেইন দিয়ে সার্ভ করা হয়।
 *
 * কেন:
 *  - raw hosting URL ব্রাউজারের HTML source/DevTools-এ কখনো দেখা যায় না
 *  - ভবিষ্যতে হোস্টিং বদলালে পুরোনো ছবির লিংক ভাঙে না
 *  - CSP/Referrer নিরাপত্তা বজায় থাকে (same-origin)
 *
 * নিরাপত্তা:
 *  - লিংক HMAC signature ছাড়া কাজ করে না ⇒ কেউ এই route ব্যবহার করে
 *    যেকোনো URL proxy করতে (SSRF) পারবে না
 *  - শুধু config('images.proxy.hosts') allowlist-এর হোস্ট অনুমোদিত
 *  - response অবশ্যই image/* হতে হবে, সাইজ সীমাও যাচাই হয়
 */
class ImageProxyController extends Controller
{
    public function show(Request $request, string $token)
    {
        $url = ImageUrl::decode($token, (string) $request->query('s', ''));

        if ($url === null) {
            return $this->notFound();
        }

        $ttlMinutes = max(1, (int) config('images.proxy.ttl_minutes', 10080));
        $maxBytes = max(256, (int) config('images.proxy.max_kb', 12288)) * 1024;

        $cacheKey = 'image.proxy.'.sha1($url);
        $image = Cache::get($cacheKey);

        if (! is_array($image) || empty($image['body'])) {
            // ব্যর্থ চেষ্টা ক্যাশ করা হয় না — পরের রিকোয়েস্টে আবার চেষ্টা হবে
            $image = $this->fetch($url, $maxBytes);

            if (! is_array($image) || empty($image['body'])) {
                return $this->notFound();
            }

            Cache::put($cacheKey, $image, now()->addMinutes($ttlMinutes));
        }

        $etag = '"'.substr(sha1($image['body']), 0, 32).'"';

        if (trim((string) $request->header('If-None-Match')) === $etag) {
            return response('', 304, ['ETag' => $etag, 'Cache-Control' => 'public, max-age='.($ttlMinutes * 60)]);
        }

        return response($image['body'], 200, [
            'Content-Type'           => $image['type'],
            'Content-Length'         => (string) strlen($image['body']),
            'Cache-Control'          => 'public, max-age='.($ttlMinutes * 60).', immutable',
            'ETag'                   => $etag,
            'X-Content-Type-Options' => 'nosniff',
            'Cross-Origin-Resource-Policy' => 'same-origin',
        ]);
    }

    /** হোস্টিং থেকে ছবি আনা (ব্যর্থ হলে null) — SSRF ও সাইজ/টাইপ যাচাই সহ */
    private function fetch(string $url, int $maxBytes): ?array
    {
        try {
            $response = Http::timeout((int) config('images.proxy.timeout', 20))
                ->withHeaders(['Accept' => 'image/avif,image/webp,image/*,*/*;q=0.8'])
                ->get($url);
        } catch (\Throwable $e) {
            Log::info('Image proxy fetch failed', ['error' => $e->getMessage()]);

            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        // redirect শেষে গন্তব্যও allowlist-এর হোস্ট হতে হবে (SSRF প্রতিরোধ)
        if (method_exists($response, 'effectiveUri')) {
            $effective = (string) ($response->effectiveUri() ?? '');

            if ($effective !== '' && ! ImageUrl::isProxyable($effective)) {
                return null;
            }
        }

        $body = $response->body();
        $type = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));

        if ($body === '' || strlen($body) > $maxBytes || ! str_starts_with($type, 'image/')) {
            return null;
        }

        return ['body' => $body, 'type' => $type];
    }

    private function notFound()
    {
        return response('', 404, [
            'Cache-Control'          => 'no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
