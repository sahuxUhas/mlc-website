<?php

namespace App\Services;

use App\Models\Media;
use App\Services\Images\ImageManager;
use App\Services\Images\ImageUploadException;
use App\Services\Images\UploadedImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * সিকিউর ফাইল আপলোড সার্ভিস — মিডিয়া লাইব্রেরি, নিউজ (featured/gallery/editor),
 * অ্যালবাম, রিপোর্টার ছবি, বিজ্ঞাপন, ই-পেপার — সবই এই সার্ভিস দিয়ে যায়।
 *
 * Flow (ছবি):
 *   Admin → Image Select → এই সার্ভিস → ImageManager → ImgBB API
 *        → MySQL-এ শুধু URL/ID রেফারেন্স (BLOB নয়) → সাইটে signed proxy URL
 *
 * নিরাপত্তা:
 *  - এক্সটেনশন + আসল MIME দুটোই যাচাই (double extension / fake mime রোধ)
 *  - ফাইলের ভেতরে PHP/script ট্যাগ থাকলে প্রত্যাখ্যান
 *  - দৈবচয়নমূলক ফাইলনাম (path traversal রোধ)
 *  - সাইজ সীমা: config/images.php (MC_UPLOAD_MAX_KB)
 *  - provider ব্যর্থ হলে স্পষ্ট বাংলা error; API Key কখনো message/log-এ যায় না
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

    /** সংবাদের ছবিতে শুধু নিরাপদ ফরম্যাট */
    public const NEWS_MIMES = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
    ];

    private ImageManager $images;

    public function __construct(private string $disk = 'uploads')
    {
        $this->images = new ImageManager();
    }

    public function images(): ImageManager
    {
        return $this->images;
    }

    /** অ্যাডমিন UI-এর জন্য নিরাপদ সারাংশ (কোনো API Key বা raw URL নয়) */
    public function providerStatus(): array
    {
        return $this->images->status();
    }

    /** পিছনের কোডে সামঞ্জস্য রাখতে (আগের নাম) */
    public function isImgbbEnabled(): bool
    {
        return $this->images->imgbb()->isConfigured();
    }

    /**
     * ফাইল যাচাই করে নিরাপদে সংরক্ষণ করে Media রেকর্ড তৈরি করে।
     * ছবি হলে (provider কনফিগার থাকলে) সরাসরি ImgBB API-তে আপলোড হয়।
     *
     * @throws ValidationException ভ্যালিডেশন/আপলোড ব্যর্থ হলে (ব্যবহারকারী-বান্ধব বাংলা বার্তা)
     */
    public function store(UploadedFile $file, string $folder = 'general', array $allowed = self::IMAGE_MIMES): Media
    {
        $this->validate($file, $allowed);

        $extension = strtolower($file->getClientOriginalExtension());
        $isImage = str_starts_with(strtolower((string) $file->getMimeType()), 'image/');

        try {
            $uploaded = $isImage
                ? $this->images->upload($file, $folder, $this->cleanOriginalName($file->getClientOriginalName()))
                : $this->images->local()->upload($file, $folder, $this->cleanOriginalName($file->getClientOriginalName()));
        } catch (ImageUploadException $e) {
            Log::warning('Image upload failed via provider', [
                'provider' => $this->images->provider()->name(),
                'folder'   => $folder,
                'reason'   => $e->reason,
            ]);

            throw ValidationException::withMessages(['files' => $e->getMessage(), 'images' => $e->getMessage()]);
        } catch (\Throwable $e) {
            Log::error('Image upload crashed', ['error' => $e->getMessage(), 'folder' => $folder]);

            throw ValidationException::withMessages(['files' => 'ছবি সংরক্ষণে অপ্রত্যাশিত সমস্যা হয়েছে — আবার চেষ্টা করুন।']);
        }

        return $this->persist($uploaded, $file, $folder);
    }

    /** একাধিক ফাইল একসাথে আপলোড — কোনো একটি ব্যর্থ হলে পুরো কাজ থেমে যায় (পুরোনো আচরণ) */
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

    /**
     * একাধিক ফাইল — একটি ব্যর্থ হলেও বাকিগুলো সংরক্ষিত হয় এবং
     * ত্রুটির তালিকা ফেরত আসে (গ্যালারি AJAX আপলোডের জন্য)।
     *
     * @return array{saved: array<int, Media>, errors: array<int, string>}
     */
    public function storeManyCollect(array $files, string $folder = 'general', array $allowed = self::IMAGE_MIMES): array
    {
        $saved = [];
        $errors = [];

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            try {
                $saved[] = $this->store($file, $folder, $allowed);
            } catch (ValidationException $e) {
                foreach ($e->errors() as $messages) {
                    foreach ($messages as $message) {
                        $errors[] = $message;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Gallery upload failed', ['error' => $e->getMessage()]);
                $errors[] = 'একটি ছবি আপলোড করা যায়নি — আবার চেষ্টা করুন।';
            }
        }

        return ['saved' => $saved, 'errors' => array_values(array_unique($errors))];
    }

    /** ভ্যালিডেশন — ব্যর্থ হলে ValidationException (বাংলা বার্তাসহ) */
    public function validate(UploadedFile $file, array $allowed): void
    {
        if (! $file->isValid()) {
            throw $this->fail('ফাইল আপলোড ব্যর্থ হয়েছে — আবার চেষ্টা করুন।');
        }

        $maxKb = (int) (config('images.max_kb') ?: 4096);
        if (($file->getSize() / 1024) > $maxKb) {
            throw $this->fail('ছবির সাইজ সর্বোচ্চ '.bn_num($maxKb).' KB হতে পারবে। দয়া করে ছোট করে আবার চেষ্টা করুন।');
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

        // ছবির ভেতরে PHP কোড/স্ক্রিপ্ট লুকানো আছে কিনা
        if (str_starts_with($realMime, 'image/')) {
            $path = $file->getRealPath();

            if ($path === false || @getimagesize($path) === false) {
                throw $this->fail('ফাইলটি বৈধ ছবি নয় — শুধু jpg, png, webp গ্রহণ করা হয়।');
            }

            $head = (string) file_get_contents($path, false, null, 0, 2048);
            if (stripos($head, '<?php') !== false || stripos($head, '<script') !== false) {
                throw $this->fail('ছবির ভেতরে স্ক্রিপ্ট পাওয়া গেছে — আপলোড প্রত্যাখ্যাত।');
            }
        }
    }

    /** Media রেকর্ড তৈরি (ImgBB/লোকাল — দুই ক্ষেত্রেই) */
    private function persist(UploadedImage $uploaded, UploadedFile $file, string $folder): Media
    {
        return Media::create([
            'file_name'           => $this->cleanOriginalName($file->getClientOriginalName()),
            'disk'                => $uploaded->disk,
            'provider'            => $uploaded->provider,
            'provider_id'         => $uploaded->providerId,
            'path'                => $uploaded->path(),
            'provider_url'        => $uploaded->remote ? $uploaded->url : null,
            'provider_delete_url' => $uploaded->deleteUrl,
            'thumb_url'           => $uploaded->remote ? $uploaded->thumbUrl : null,
            'folder'              => trim($folder, '/') ?: 'general',
            'mime_type'           => $uploaded->mimeType ?: $file->getMimeType(),
            'extension'           => $uploaded->extension ?: strtolower($file->getClientOriginalExtension()),
            'size'                => $uploaded->size ?: (int) ($file->getSize() ?: 0),
            'width'               => $uploaded->width,
            'height'              => $uploaded->height,
            'uploaded_by'         => auth()->id(),
        ]);
    }

    /** ডাটাবেসে সংরক্ষণের জন্য আসল নাম পরিষ্কার করা */
    private function cleanOriginalName(string $name): string
    {
        $name = preg_replace('/[^\p{L}\p{N}._\- ]+/u', '', $name);

        return mb_substr(trim((string) $name), 0, 180) ?: 'file';
    }

    /**
     * Media রেকর্ড + হোস্টিং/ডিস্কের ফাইল — দুটোই মুছে ফেলা।
     * (ImgBB হলে হোস্টিং delete URL best-effort কল করা হয়, ব্যর্থ হলেও DB পরিষ্কার থাকে)
     */
    public function delete(Media $media): void
    {
        $media->deleteFile();
        $media->delete();
    }

    /** পাবলিক URL তৈরি (remote হলে signed proxy URL) */
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
        return ValidationException::withMessages(['files' => $message, 'images' => $message]);
    }
}
