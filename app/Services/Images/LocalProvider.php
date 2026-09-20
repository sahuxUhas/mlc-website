<?php

namespace App\Services\Images;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * লোকাল ডিস্ক ড্রাইভার (public/uploads) — ফলব্যাক ও ডকুমেন্ট (PDF) এর জন্য।
 * cPanel-এ কোনো বাইরের সার্ভিস ছাড়াও সাইট চলে।
 */
class LocalProvider implements ImageProvider
{
    public function __construct(private readonly string $disk = 'uploads')
    {
    }

    public function name(): string
    {
        return 'local';
    }

    public function label(): string
    {
        return 'সার্ভার স্টোরেজ';
    }

    public function isRemote(): bool
    {
        return false;
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function upload(UploadedFile $file, string $folder, ?string $name = null): UploadedImage
    {
        $extension = strtolower($file->getClientOriginalExtension());

        // ডাবল এক্সটেনশন ও অস্বাভাবিক অক্ষর বাদ দিয়ে দৈবচয়নমূলক নাম
        if (! preg_match('/^[a-z0-9]{1,8}$/', $extension)) {
            $extension = 'jpg';
        }

        $fileName = Str::lower(Str::random(24)).'-'.now()->timestamp.'.'.$extension;
        $path = trim($folder, '/').'/'.$fileName;

        Storage::disk($this->disk)->putFileAs(dirname($path), $file, basename($path));

        $width = null;
        $height = null;
        if (str_starts_with((string) $file->getMimeType(), 'image/')) {
            $dims = @getimagesize($file->getRealPath());
            if (is_array($dims)) {
                [$width, $height] = $dims;
            }
        }

        return new UploadedImage(
            provider: $this->name(),
            remote: false,
            disk: $this->disk,
            url: $path,
            displayUrl: $path,
            thumbUrl: $path,
            providerId: null,
            deleteUrl: null,
            width: $width,
            height: $height,
            size: (int) ($file->getSize() ?: 0),
            mimeType: $file->getMimeType(),
            extension: $extension,
            fileName: $name ?: $file->getClientOriginalName(),
            folder: trim($folder, '/'),
        );
    }

    public function delete(?string $deleteUrl, ?string $providerId): bool
    {
        // লোকাল ফাইল মোছার দায়িত্ব MediaUploader এর (Storage::delete)
        return false;
    }
}
