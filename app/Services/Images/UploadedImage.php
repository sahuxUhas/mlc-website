<?php

namespace App\Services\Images;

/**
 * একটি সফল আপলোডের ফলাফল (Immutable DTO)।
 *
 * মনে রাখবেন: `url` / `displayUrl` / `thumbUrl` — এগুলো provider-এর raw URL।
 * এগুলো শুধুমাত্র Backend (DB সংরক্ষণ, proxy fetch) এ ব্যবহার হয়;
 * কখনো সরাসরি Blade/JS/UI-তে পাঠানো হয় না — সেখানে mc_image() ব্যবহৃত হয়।
 */
final class UploadedImage
{
    public function __construct(
        public readonly string $provider,
        public readonly bool $remote,
        public readonly string $disk,
        public readonly string $url,               // provider URL অথবা লোকাল relative path
        public readonly ?string $displayUrl = null,
        public readonly ?string $thumbUrl = null,
        public readonly ?string $providerId = null,
        public readonly ?string $deleteUrl = null,
        public readonly ?int $width = null,
        public readonly ?int $height = null,
        public readonly int $size = 0,
        public readonly ?string $mimeType = null,
        public readonly ?string $extension = null,
        public readonly ?string $fileName = null,
        public readonly ?string $folder = null,
    ) {
    }

    /** DB-তে path হিসেবে যা সংরক্ষিত হবে */
    public function path(): string
    {
        return $this->remote ? ($this->displayUrl ?: $this->url) : $this->url;
    }

    public function isRemote(): bool
    {
        return $this->remote;
    }

    /** অ্যাডমিন UI-তে দেখানোর জন্য নিরাপদ লেবেল (raw URL নয়) */
    public function providerLabel(): string
    {
        return match ($this->provider) {
            'imgbb' => 'ImgBB ক্লাউড হোস্টিং',
            default => 'সার্ভার স্টোরেজ',
        };
    }
}
