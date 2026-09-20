<?php

namespace App\Services\Images;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * ImgBB Image Hosting Driver — https://api.imgbb.com/
 *
 * নিরাপত্তা:
 *  - API Key শুধু config('images.providers.imgbb.key') ⇒ .env থেকে আসে।
 *  - Key কখনো return করা হয় না (getApiKey() নেই), লগ বা exception message-এও যায় না।
 *  - ব্যর্থতার বার্তা বাংলায় ও ব্যবহারকারী-বান্ধব; কাঁচা provider response UI-তে যায় না।
 */
class ImgbbProvider implements ImageProvider
{
    public function __construct(private readonly array $config = [])
    {
    }

    public function name(): string
    {
        return 'imgbb';
    }

    public function label(): string
    {
        return 'ImgBB';
    }

    public function isRemote(): bool
    {
        return true;
    }

    public function isEnabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? true);
    }

    public function isConfigured(): bool
    {
        return $this->isEnabled() && $this->key() !== '';
    }

    /** কখনো UI/লগে প্রকাশ করা হয় না */
    private function key(): string
    {
        return trim((string) ($this->config['key'] ?? ''));
    }

    private function endpoint(): string
    {
        return (string) ($this->config['endpoint'] ?? 'https://api.imgbb.com/1/upload');
    }

    public function upload(UploadedFile $file, string $folder, ?string $name = null): UploadedImage
    {
        if (! $this->isConfigured()) {
            throw ImageUploadException::notConfigured();
        }

        $realPath = $file->getRealPath();

        if ($realPath === false || ! is_readable($realPath)) {
            throw ImageUploadException::make('ছবির ফাইল পড়া যায়নি — আবার চেষ্টা করুন।');
        }

        // ছবি আসলেই কিনা নিশ্চিত হওয়া (MIME/extension ধোঁকা রোধ)
        if (@getimagesize($realPath) === false) {
            throw ImageUploadException::make('ফাইলটি বৈধ ছবি নয় — শুধু jpg, png, webp গ্রহণ করা হয়।');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $payload = [
            'key'   => $this->key(),
            'image' => base64_encode((string) file_get_contents($realPath)),
            'name'  => $this->safeName($name ?: $file->getClientOriginalName()),
        ];

        $expiration = (int) ($this->config['expiration'] ?? 0);
        if ($expiration > 0) {
            $payload['expiration'] = $expiration;
        }

        try {
            $response = Http::timeout((int) ($this->config['timeout'] ?? 30))
                ->acceptJson()
                ->asForm()
                ->post($this->endpoint(), $payload);
        } catch (\Throwable $e) {
            Log::warning('ImgBB upload connection failed', ['error' => $e->getMessage()]);

            throw ImageUploadException::make('ছবি হোস্টিং সার্ভারে সংযোগ করা যায়নি — ইন্টারনেট/সার্ভার সমস্যা হতে পারে।', $e->getMessage());
        }

        if (! $response->successful()) {
            Log::warning('ImgBB upload failed (HTTP)', ['status' => $response->status()]);

            $message = match (true) {
                $response->status() === 400 => 'ছবিটি হোস্টিং সার্ভিস গ্রহণ করেনি (ভুল বা বড় ফাইল)।',
                $response->status() === 401, $response->status() === 403 => 'ছবি হোস্টিং API Key সঠিক নয় বা মেয়াদোত্তীর্ণ।',
                $response->status() === 429 => 'ছবি হোস্টিংয়ে অনেক আপলোড হয়েছে — কিছুক্ষণ পরে চেষ্টা করুন।',
                default => 'ছবি আপলোড ব্যর্থ হয়েছে (সার্ভার কোড '.$response->status().')।',
            };

            throw ImageUploadException::make($message, 'HTTP '.$response->status());
        }

        $json = $response->json() ?: [];

        if (($json['success'] ?? false) !== true || empty($json['data']['url'])) {
            Log::warning('ImgBB upload rejected', [
                'error_code' => $json['error']['code'] ?? null,
                'status'     => $json['status'] ?? null,
            ]);

            throw ImageUploadException::make('ছবি হোস্টিং সার্ভিস আপলোড নাকচ করেছে — আবার চেষ্টা করুন।');
        }

        $data = (array) $json['data'];
        $url = (string) $data['url'];

        return new UploadedImage(
            provider: $this->name(),
            remote: true,
            disk: 'imgbb',
            url: $url,
            displayUrl: (string) ($data['display_url'] ?? $url),
            thumbUrl: (string) ($data['thumb']['url'] ?? $data['display_url'] ?? $url),
            providerId: isset($data['id']) ? (string) $data['id'] : null,
            deleteUrl: isset($data['delete_url']) ? (string) $data['delete_url'] : null,
            width: isset($data['width']) ? (int) $data['width'] : null,
            height: isset($data['height']) ? (int) $data['height'] : null,
            size: (int) ($data['size'] ?? $file->getSize() ?: 0),
            mimeType: $file->getMimeType(),
            extension: $extension,
            fileName: $this->safeName($name ?: $file->getClientOriginalName()),
            folder: trim($folder, '/'),
        );
    }

    /**
     * ImgBB ছবি মোছার জন্য `delete_url` (hash সহ পেজ URL) ব্যবহার হয়।
     * ব্যর্থ হলেও কিছুই ভাঙে না — শুধু লগ হয়, DB রেকর্ড আগেই মুছে যায়।
     */
    public function delete(?string $deleteUrl, ?string $providerId): bool
    {
        if (empty($deleteUrl) || ! filter_var($deleteUrl, FILTER_VALIDATE_URL)) {
            return false;
        }

        try {
            $response = Http::timeout(15)->get($deleteUrl);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::info('ImgBB remote delete skipped', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /** ফাইলের নাম নিরাপদ করা (DB/লগে ব্যবহৃত, URL নয়) */
    private function safeName(string $name): string
    {
        $base = pathinfo($name, PATHINFO_FILENAME);
        $base = preg_replace('/[^\p{L}\p{N} ._\-]+/u', '', (string) $base);

        return mb_substr(trim((string) $base) ?: 'image', 0, 120);
    }
}
