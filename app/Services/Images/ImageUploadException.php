<?php

namespace App\Services\Images;

use RuntimeException;

/**
 * Image upload ব্যর্থ হলে এই exception throw হয়।
 * message টি ব্যবহারকারী-বান্ধব (বাংলা) এবং UI-তে দেখানো নিরাপদ —
 * এতে কখনো API Key বা raw provider response যুক্ত করা হয় না।
 */
class ImageUploadException extends RuntimeException
{
    public function __construct(string $message, public readonly ?string $reason = null)
    {
        parent::__construct($message);
    }

    /** লগে লেখার জন্য নিরাপদ বিবরণ (API Key ছাড়া) */
    public static function make(string $message, ?string $technical = null): self
    {
        return new self($message, $technical);
    }

    public static function notConfigured(): self
    {
        return new self('ছবি হোস্টিং সার্ভিস সেট করা নেই — অ্যাডমিনকে জানান (.env এ IMGBB_API_KEY সেট করতে হবে)।');
    }
}
