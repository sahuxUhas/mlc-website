<?php

namespace App\Support;

/**
 * সংবাদের বিস্তারিত কনটেন্টের (Rich Text) XSS নিরাপত্তা।
 *
 *  - অনুমোদিত ট্যাগ ছাড়া বাকি সব বাদ যায় (script/iframe/object/embed/form ইত্যাদি)
 *  - `on*` ইভেন্ট হ্যান্ডলার, `javascript:` URL ও `srcdoc` সরানো হয়
 *  - ছবি/লিংকের src-href শুধু নিরাপদ স্কিম (http, https, mailto, নিজের ডোমেইন)
 *
 * ডিজাইন বা কনটেন্টের সাধারণ মার্কআপ (p, h2, ul, table, figure, পরিচিত class/style)
 * অক্ষত থাকে — তাই পুরোনো সংবাদ ভাঙে না।
 */
class HtmlSanitizer
{
    /** অনুমোদিত ট্যাগ */
    private const TAGS = [
        'p', 'br', 'hr', 'span', 'div', 'section', 'article',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup', 'mark', 'small',
        'ul', 'ol', 'li', 'dl', 'dt', 'dd',
        'blockquote', 'pre', 'code',
        'a', 'img', 'figure', 'figcaption', 'picture', 'source',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'caption',
        'iframe', 'audio', 'video',
    ];

    /** নিরাপদ ট্যাগ-বিশেষ অ্যাট্রিবিউট */
    private const ATTRS = [
        'a'          => ['href', 'title', 'target', 'rel'],
        'img'        => ['src', 'alt', 'title', 'width', 'height', 'loading', 'decoding', 'class'],
        'iframe'     => ['src', 'title', 'width', 'height', 'allow', 'allowfullscreen', 'loading', 'frameborder'],
        'source'     => ['src', 'type', 'media', 'srcset', 'sizes'],
        'audio'      => ['src', 'controls', 'preload'],
        'video'      => ['src', 'controls', 'poster', 'preload', 'width', 'height'],
        'td'         => ['colspan', 'rowspan'],
        'th'         => ['colspan', 'rowspan'],
        'table'      => ['class'],
        '*'          => ['class', 'style', 'id', 'title', 'dir', 'lang'],
    ];

    /** embed সাপোর্ট শুধু পরিচিত ভিডিও হোস্টে */
    private const IFRAME_HOSTS = [
        'www.youtube.com', 'youtube.com', 'www.youtube-nocookie.com', 'youtube-nocookie.com',
        'player.vimeo.com', 'www.facebook.com', 'web.facebook.com', 'm.facebook.com',
        'www.dailymotion.com', 'dailymotion.com', 'player.twitch.tv', 'www.tiktok.com',
    ];

    /** allowed iframe ছাড়া বাকি সব ফ্রেম-ট্যাগ (পুরোনো কনটেন্টেও) সরানো হয় */
    private const DROP_TAGS = ['script', 'style', 'object', 'embed', 'form', 'input', 'button', 'select', 'textarea', 'link', 'meta'];

    public static function clean(?string $html): string
    {
        $html = (string) $html;

        if (trim($html) === '') {
            return '';
        }

        // দ্রুত প্রাক-পরিষ্কার (regex) — DOM লোড করার আগেই বিপজ্জনক অংশ বাদ
        $html = preg_replace('~<\s*(script|style|object|embed|form|link|meta)\b[^>]*>.*?<\s*/?\s*\1\s*>~is', '', $html) ?? $html;
        $html = preg_replace('~<\s*(script|style|object|embed|form|link|meta)\b[^>]*/?\s*>~is', '', $html) ?? $html;
        $html = preg_replace('~\s+on[a-z]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)~is', '', $html) ?? $html;
        $html = preg_replace('~\s+srcdoc\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)~is', '', $html) ?? $html;

        if (! class_exists(\DOMDocument::class)) {
            return self::stripDangerousUrls($html);
        }

        $previous = libxml_use_internal_errors(true);

        $document = new \DOMDocument('1.0', 'UTF-8');

        // meta charset সহ wrap করা হয় — PHP 8.2+ এ deprecated mb_convert_encoding ছাড়াই
        // বাংলা/ইউনিকোড অক্ষর সঠিকভাবে parse হয়। এরপর অপেক্ষাকৃত DOM বাদ দেওয়া হয়।
        $loaded = $document->loadHTML(
            '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>'.$html.'</body></html>',
            LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $body = $loaded ? $document->getElementsByTagName('body')->item(0) : null;

        if (! $body) {
            return self::stripDangerousUrls($html);
        }

        self::walk($body);

        $clean = '';
        foreach ($body->childNodes as $child) {
            $clean .= $document->saveHTML($child);
        }

        return trim(self::stripDangerousUrls($clean));
    }

    /** হালকা পরিষ্কার — কনটেন্ট সংরক্ষণ না করে শুধু render করার সময় (পুরোনো কনটেন্ট অক্ষত রাখতে) */
    public static function cleanLight(?string $html): string
    {
        $html = (string) $html;
        $html = preg_replace('~<\s*(script|object|embed|form)\b[^>]*>.*?<\s*/?\s*\1\s*>~is', '', $html) ?? $html;
        $html = preg_replace('~<\s*(script|object|embed|form)\b[^>]*/?\s*>~is', '', $html) ?? $html;
        $html = preg_replace('~\s+on[a-z]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)~is', '', $html) ?? $html;
        $html = preg_replace('~\s+srcdoc\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)~is', '', $html) ?? $html;

        return self::stripDangerousUrls($html);
    }

    /* ---------------- private ---------------- */

    private static function walk(\DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (! $child instanceof \DOMElement) {
                continue;
            }

            $tag = strtolower($child->nodeName);

            if (in_array($tag, self::DROP_TAGS, true) || ! in_array($tag, self::TAGS, true)) {
                // অননুমোদিত ট্যাগের ভেতরের টেক্সট রাখা হয়, ট্যাগ বাদ
                self::replaceWithText($child);
                continue;
            }

            if ($tag === 'iframe' && ! self::isAllowedIframe((string) $child->getAttribute('src'))) {
                $child->parentNode?->removeChild($child);
                continue;
            }

            self::cleanAttributes($child, $tag);
            self::walk($child);
        }
    }

    private static function cleanAttributes(\DOMElement $element, string $tag): void
    {
        $allowed = array_merge(self::ATTRS[$tag] ?? [], self::ATTRS['*']);

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->nodeName);
            $value = (string) $attribute->nodeValue;

            if (! in_array($name, $allowed, true) || str_starts_with($name, 'on') || $name === 'srcdoc') {
                $element->removeAttribute($attribute->nodeName);
                continue;
            }

            if (in_array($name, ['href', 'src', 'poster', 'srcset'], true) && ! self::isSafeUrl($value, $name === 'href')) {
                $element->removeAttribute($attribute->nodeName);
                continue;
            }

            if ($name === 'style' && preg_match('~(expression\s*\(|url\s*\(\s*[\'"]?\s*javascript|behaviou?r\s*:)~i', $value)) {
                $element->removeAttribute($attribute->nodeName);
                continue;
            }

            if ($name === 'class' && ! preg_match('~^[\p{L}\p{N}\s\-_]+$~u', $value)) {
                $element->removeAttribute($attribute->nodeName);
            }
        }

        // বাইরের লিংকে নিরাপত্তা
        if ($tag === 'a' && $element->hasAttribute('target')) {
            $element->setAttribute('rel', 'noopener nofollow');
        }
    }

    private static function isSafeUrl(string $url, bool $isHref = false): bool
    {
        $url = trim($url);

        if ($url === '' || str_starts_with($url, '#') || str_starts_with($url, '/') || str_starts_with($url, '?')) {
            return true;
        }

        if (str_starts_with($url, 'data:')) {
            // data: শুধু ছবির জন্য, script/svg নয়
            return (bool) preg_match('~^data:image/(jpeg|jpg|png|webp|gif|avif);~i', $url);
        }

        if (preg_match('~^(https?:|mailto:|tel:)~i', $url)) {
            return ! preg_match('~^(javascript|vbscript|file)~i', $url);
        }

        return false;
    }

    private static function isAllowedIframe(?string $src): bool
    {
        $host = strtolower((string) parse_url((string) $src, PHP_URL_HOST));

        return $host !== '' && in_array($host, self::IFRAME_HOSTS, true);
    }

    private static function replaceWithText(\DOMNode $node): void
    {
        $text = $node->ownerDocument?->createTextNode($node->textContent ?? '');

        if ($text !== null && $node->parentNode) {
            $node->parentNode->replaceChild($text, $node);
        }
    }

    private static function stripDangerousUrls(string $html): string
    {
        $html = preg_replace('~\s(href|src|poster)\s*=\s*(["\']?)\s*(?:javascript|vbscript|data:text/html)[^"\'>]*\2~is', '', $html) ?? $html;

        return $html;
    }
}
