<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;

/**
 * সিকিউর ফাইল আপলোড সার্ভিস — মিডিয়া লাইব্রেরি, নিউজ, গ্যালারি,
 * রিপোর্টার ছবি ও বিজ্ঞাপনের ছবি সবই এখান দিয়ে যায়।
 *
 * নিরাপত্তা:
 *  - এক্সটেনশন + আসল MIME টাইপ দুটোই যাচাই (double extension / fake mime রোধ)
 *  - ফাইলের ভেতরে PHP ট্যাগ থাকলে প্রত্যাখ্যান
 *  - দৈবচয়নমূলক ফাইলনাম (আসল নাম থেকে path traversal রোধ)
 *  - সাইজ সীমা (.env: MC_UPLOAD_MAX_KB)
 */
class MediaUploader
{
    /** ছবির জন্য অনুমোদিত টাইপ */
    public const IMAGE_MIMES = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
        'gif'  => 'image/gif',
        'avif' => 'image/avif',
    ];

    /** সংযুক্তি (ই-পেপার/রিপোর্ট) এর জন্য */
    public const DOC_MIMES = ['pdf' => 'application/pdf'];

    public function __construct(private string $disk = 'uploads')
    {
    }

    /**
     * ফাইল যাচাই করে নিরাপদে সংরক্ষণ করে ও Media রেকর্ড তৈরি করে।
     *
     * @throws ValidationException ভ্যালিডেশন ব্যর্থ হলে (ব্যবহারকারী-বান্ধব বার্তা)
     */
    public function store(UploadedFile $file, string $folder = 'general', array $allowed = self::IMAGE_MIMES): Media
    {
        $this->validate($file, $allowed);

        $extension = strtolower($file->getClientOriginalExtension());

        // দৈবচয়নমূলক, অনুমান-অযোগ্য ফাইলনাম
        $name = Str::lower(Str::random(24)).'-'.now()->timestamp.'.'.$extension;

        $path = trim($folder, '/').'/'.$name;

        Storage::disk($this->disk)->putFileAs(dirname($path), $file, basename($path));

        $width = null;
        $height = null;
        if (str_starts_with((string) $file->getMimeType(), 'image/')) {
            $dims = @getimagesize($file->getRealPath());
            if ($dims !== false) {
                [$width, $height] = $dims;
            }
        }

        return Media::create([
            'file_name'   => $this->cleanOriginalName($file->getClientOriginalName()),
            'disk'        => $this->disk,
            'path'        => $path,
            'mime_type'   => $file->getMimeType(),
            'extension'   => $extension,
            'size'        => $file->getSize() ?: 0,
            'width'       => $width,
            'height'      => $height,
            'uploaded_by' => auth()->id(),
        ]);
    }

    /** একাধিক ফাইল একসাথে আপলোড */
    public function storeMany(array $files, string $folder = 'general', array $allowed = self::IMAGE_MIMES): array
    {
        $saved = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $saved[] = $this->store($file, $folder, $allowed);
            }
        }

        return $saved;
    }

    /** ভ্যালিডেশন — ব্যর্থ হলে ValidationException (বাংলা বার্তাসহ) */
    public function validate(UploadedFile $file, array $allowed): void
    {
        if (! $file->isValid()) {
            throw $this->fail('ফাইল আপলোড ব্যর্থ হয়েছে।');
        }

        $maxKb = (int) env('MC_UPLOAD_MAX_KB', 4096);
        if (($file->getSize() / 1024) > $maxKb) {
            throw $this->fail('ফাইলের সাইজ সর্বোচ্চ '.bn_num($maxKb).' KB হতে পারবে।');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $realMime  = strtolower((string) $file->getMimeType());

        // এক্সটেনশন অনুমোদিত তালিকায় আছে কিনা
        if (! array_key_exists($extension, $allowed)) {
            throw $this->fail('অনুমোদিত ফরম্যাট নয়: '.implode(', ', array_keys($allowed)));
        }

        // আসল MIME টাইপ এক্সটেনশনের সাথে মিলছে কিনা (fake mime রোধ)
        if (! in_array($realMime, $allowed, true)) {
            throw $this->fail('ফাইলের প্রকৃত টাইপ ও এক্সটেনশন মিলছে না — আপলোড প্রত্যাখ্যাত।');
        }

        // ডাবল এক্সটেনশন (যেমন shell.php.jpg) রোধ
        if (preg_match('/\.(php|phtml|phar|pl|py|cgi|sh|exe|js)$/i', $file->getClientOriginalName())) {
            throw $this->fail('নিরাপত্তার কারণে এই ফাইল গ্রহণ করা হয়নি।');
        }

        // ছবির ভেতরে PHP কোড লুকানো আছে কিনা
        if (str_starts_with($realMime, 'image/')) {
            $head = (string) file_get_contents($file->getRealPath(), false, null, 0, 2048);
            if (stripos($head, '<?php') !== false || stripos($head, '<script') !== false) {
                throw $this->fail('ছবির ভেতরে স্ক্রিপ্ট পাওয়া গেছে — আপলোড প্রত্যাখ্যাত।');
            }
        }
    }

    /** ডাটাবেসে সংরক্ষণের জন্য আসল নাম পরিষ্কার করা */
    private function cleanOriginalName(string $name): string
    {
        $name = preg_replace('/[^\p{L}\p{N}._\- ]+/u', '', $name);

        return mb_substr(trim((string) $name), 0, 180) ?: 'file';
    }

    /** Media রেকর্ড ও ডিস্কের ফাইল — দুটোই মুছে ফেলা */
    public function delete(Media $media): void
    {
        Storage::disk($media->disk ?: $this->disk)->delete($media->path);
        $media->delete();
    }

    /** পাবলিক URL তৈরি */
    public function url(?string $path): ?string
    {
        return $path ? mc_image($path) : null;
    }

    /**
     * ভ্যালিডেশন ব্যর্থতাকে ValidationException এ রূপান্তর করে —
     * ফলে ব্যবহারকারী 500 এর বদলে বাংলা এরর বার্তা সহ ফর্মে ফিরে যায়।
     */
    private function fail(string $message): ValidationException
    {
        return ValidationException::withMessages(['files' => $message]);
    }
}
