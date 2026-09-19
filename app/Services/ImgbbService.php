<?php

namespace App\Services;

use App\Services\Images\ImageManager;
use App\Services\Images\ImgbbProvider;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * ImgBB Image Upload Service (Backward-compatible wrapper)।
 *
 * প্রকৃত কাজ করে `App\Services\Images\ImgbbProvider` ও `ImageManager` —
 * ভবিষ্যতে অন্য হোস্টিং ব্যবহার করতে শুধু `.env`-এ IMAGE_PROVIDER বদলালেই হবে।
 *
 * নিরাপত্তা:
 *  - API Key শুধু .env/config থেকে পড়া হয়, কোথাও return বা log করা হয় না।
 *  - getApiKey() মেথডটি ইচ্ছাকৃতভাবে নেই — UI/JS-এ key যাওয়ার সুযোগ নেই।
 *
 * ব্যবহারের জন্য প্রস্তাবিত: MediaUploader / ImageManager ব্যবহার করুন।
 */
class ImgbbService
{
    private ImgbbProvider $provider;

    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager();
        $this->provider = $this->manager->imgbb();
    }

    /** ImgBB চালু ও key সেট করা আছে কি? */
    public function isEnabled(): bool
    {
        return $this->provider->isConfigured();
    }

    /** অ্যাডমিন UI-তে দেখানোর নিরাপদ তথ্য (API Key ছাড়া) */
    public function status(): array
    {
        return [
            'name'       => $this->provider->name(),
            'label'      => $this->provider->label(),
            'enabled'    => $this->provider->isEnabled(),
            'configured' => $this->provider->isConfigured(),
        ];
    }

    /**
     * ImgBB-তে আপলোড।
     *
     * @return array{url:string,display_url:string,thumb:string,medium:string,delete_url:string,id:?string,width:?int,height:?int,size:int}
     *
     * @throws ValidationException ব্যর্থ হলে (বাংলা, নিরাপদ বার্তা)
     */
    public function upload(UploadedFile $file, ?string $name = null): array
    {
        if (! $this->isEnabled()) {
            throw ValidationException::withMessages([
                'files' => 'ছবি হোস্টিং (ImgBB) কনফিগার করা নেই — .env এ IMGBB_API_KEY সেট করুন।',
            ]);
        }

        try {
            $image = $this->provider->upload($file, 'general', $name);
        } catch (\App\Services\Images\ImageUploadException $e) {
            Log::warning('ImgbbService upload failed', ['reason' => $e->reason]);

            throw ValidationException::withMessages(['files' => $e->getMessage()]);
        }

        return [
            'url'         => $image->url,
            'display_url' => $image->displayUrl ?: $image->url,
            'thumb'       => $image->thumbUrl ?: $image->url,
            'medium'      => $image->displayUrl ?: $image->url,
            'delete_url'  => (string) $image->deleteUrl,
            'id'          => $image->providerId,
            'width'       => $image->width,
            'height'      => $image->height,
            'size'        => $image->size,
        ];
    }

    /** একাধিক ফাইল একসাথে আপলোড */
    public function uploadMany(array $files): array
    {
        $results = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $results[] = $this->upload($file);
            }
        }

        return $results;
    }
}
