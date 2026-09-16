<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * XSS প্রতিরোধ — ইনপুট থেকে বিপজ্জনক ট্যাগ/হ্যান্ডলার সরায়।
 * সমৃদ্ধ টেক্সট ফিল্ড (content) বাদে সব ইনপুটে প্রয়োগ হয়।
 */
class SanitizeInput
{
    /** যেসব ফিল্ডে HTML অনুমোদিত (এডিটরের সমৃদ্ধ টেক্সট) */
    private array $allowedHtml = ['content', 'body', 'details', 'html_code', 'excerpt'];

    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        array_walk_recursive($input, function (&$value, $key) {
            if (! is_string($value)) {
                return;
            }

            if (in_array($key, $this->allowedHtml, true)) {
                $value = $this->cleanRichText($value);
                return;
            }

            // স্ক্রিপ্ট ট্যাগ, ইভেন্ট হ্যান্ডলার ও javascript: URL সরানো
            $value = preg_replace('~<\s*(script|iframe|object|embed|link|style)[^>]*>.*?<\s*/\s*\1\s*>~is', '', $value);
            $value = preg_replace('~<\s*(script|iframe|object|embed|style)[^>]*/?\s*>~is', '', $value);
            $value = preg_replace('~\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)~is', '', $value);
            $value = preg_replace('~javascript\s*:~iu', '', $value);
            $value = strip_tags($value);
        });

        $request->merge($input);

        return $next($request);
    }

    /** সমৃদ্ধ টেক্সটে শুধু নিরাপদ ট্যাগ রাখা হয় */
    private function cleanRichText(string $html): string
    {
        $html = preg_replace('~<\s*(script|iframe|object|embed|style)[^>]*>.*?<\s*/\s*\1\s*>~is', '', $html);
        $html = preg_replace('~\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)~is', '', $html);
        $html = preg_replace('~javascript\s*:~iu', '', $html);

        return $html;
    }
}
