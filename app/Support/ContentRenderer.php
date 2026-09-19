<?php

namespace App\Support;

use App\Models\Media;

/**
 * সংবাদের বিস্তারিত কনটেন্ট render করা।
 *
 * ডাটাবেসে ছবি `<img src="https://i.ibb.co/...">` হিসেবে লেখা হয় না —
 * এডিটরে ছবি যোগ করলে সেখানে শুধু একটি নিরাপদ রেফারেন্স থাকে: `{{media:12}}`
 * অথবা ক্যাপশনসহ `{{media:12|ছবির ক্যাপশন}}`।
 *
 * render করার সময় সেই ID থেকে Media রেকর্ড পড়ে signed proxy URL তৈরি হয় ⇒
 *  - raw hosting URL কখনো HTML source-এ যায় না
 *  - হোস্টিং/ডোমেইন ভবিষ্যতে বদলালেও পুরোনো সংবাদের ছবি ঠিক থাকে
 *  - ছবি মুছে ফেললে বা মিডিয়া রেফারেন্স বদলালে সাথে সাথে সব জায়গায় প্রয়োগ হয়
 */
class ContentRenderer
{
    /** কনটেন্টে থাকা মিডিয়া রেফারেন্সগুলো (ID) */
    public static function mediaIds(?string $html): array
    {
        preg_match_all(self::pattern(), (string) $html, $matches);

        return array_values(array_unique(array_map('intval', $matches[1] ?? [])));
    }

    /** {{media:ID}} → নিরাপদ <figure><img>… চিহ্নিতকরণ */
    public static function render(?string $html): string
    {
        $html = (string) $html;

        if (trim($html) === '') {
            return '';
        }

        $ids = self::mediaIds($html);

        if ($ids !== []) {
            $media = Media::query()
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id');

            $html = preg_replace_callback(self::pattern(), function (array $match) use ($media) {
                $id = (int) $match[1];
                $caption = trim((string) ($match[2] ?? ''));
                $item = $media->get($id);

                if (! $item) {
                    return '';
                }

                $url = mc_image($item->path);
                $alt = $caption !== '' ? $caption : (string) ($item->alt_text ?: $item->file_name);

                return sprintf(
                    '<figure class="mc-content-figure"><img src="%s" alt="%s" loading="lazy" decoding="async"%s>%s</figure>',
                    e($url),
                    e($alt),
                    $item->width && $item->height ? ' width="'.(int) $item->width.'" height="'.(int) $item->height.'"' : '',
                    $caption !== '' ? '<figcaption>'.e($caption).'</figcaption>' : ''
                );
            }, $html) ?? $html;
        }

        // পুরোনো কনটেন্টেও যাতে স্ক্রিপ্ট/ইভেন্ট হ্যান্ডলার না থাকে (light sanitize)
        return HtmlSanitizer::cleanLight($html);
    }

    /** শুধু টেক্সট (excerpt, সার্চ, RSS) — মিডিয়া রেফারেন্স বাদ দিয়ে */
    public static function toText(?string $html): string
    {
        $html = preg_replace(self::pattern(), ' ', (string) $html) ?? (string) $html;
        $html = preg_replace('~<\s*(script|style)\b[^>]*>.*?<\s*/?\s*\1\s*>~is', ' ', $html) ?? $html;

        return trim(preg_replace('/\s+/u', ' ', strip_tags($html)) ?? '');
    }

    private static function pattern(): string
    {
        // {{media:12}} অথবা {{media:12|ক্যাপশন}}
        return '~\{\{\s*media\s*:\s*(\d+)\s*(?:\|\s*([^}]*))?\}\}~u';
    }
}
