<?php

namespace App\Services\Images;

use Illuminate\Http\UploadedFile;

/**
 * ImageManager — ছবি কোথায় যাবে তা একজায়গায় ঠিক করে।
 *
 *  Admin → Image Select → MediaUploader → ImageManager → Provider (ImgBB API)
 *        → provider URL (শুধু Backend) → MySQL-এ রেফারেন্স → Website-এ signed proxy URL
 *
 * ভবিষ্যতে হোস্টিং বদলাতে: .env এ IMAGE_PROVIDER বদলান অথবা নতুন Provider
 * ক্লাস যোগ করে এখানে register করুন — বাকি কোড অপরিবর্তিত থাকবে।
 */
class ImageManager
{
    /** ছবি (image/*) আপলোড — provider, লোকাল ফলব্যাক ও নিরাপদ error handling সহ */
    public function upload(UploadedFile $file, string $folder = 'general', ?string $name = null, bool $forceLocal = false): UploadedImage
    {
        if ($forceLocal) {
            return $this->local()->upload($file, $folder, $name);
        }

        $provider = $this->provider();

        // লোকাল ড্রাইভার → সরাসরি লোকাল
        if (! $provider->isRemote()) {
            return $provider->upload($file, $folder, $name);
        }

        // remote provider কনফিগার করা না থাকলে (যেমন লোকাল ডেভ/টেস্টে key নেই)
        if (! $provider->isConfigured()) {
            if ((bool) config('images.local_fallback', true)) {
                return $this->local()->upload($file, $folder, $name);
            }

            throw ImageUploadException::notConfigured();
        }

        // কনফিগার করা provider — ব্যর্থ হলে exception উপরে যাবে (চুপচাপ লোকালে ফলব্যাক নয়,
        // যাতে অ্যাডমিন জানতে পারে ছবি হোস্টিংয়ে যায়নি)। মেসেজ বাংলায় ও নিরাপদ।
        return $provider->upload($file, $folder, $name);
    }

    /** active provider (config থেকে) */
    public function provider(): ImageProvider
    {
        return $this->providerFor((string) config('images.provider', 'imgbb'));
    }

    public function providerFor(string $name): ImageProvider
    {
        return match ($name) {
            'local', 'uploads' => $this->local(),
            default            => $this->imgbb(),
        };
    }

    public function local(): LocalProvider
    {
        return new LocalProvider((string) config('images.local_disk', 'uploads'));
    }

    public function imgbb(): ImgbbProvider
    {
        return new ImgbbProvider((array) config('images.providers.imgbb', []));
    }

    /** active provider কি বাইরের হোস্টিং? */
    public function isRemote(): bool
    {
        return $this->provider()->isRemote();
    }

    /** active provider ব্যবহারযোগ্য? (key আছে + চালু) */
    public function isConfigured(): bool
    {
        return $this->provider()->isConfigured();
    }

    /**
     * অ্যাডমিন UI/ড্যাশবোর্ডের জন্য নিরাপদ সারাংশ —
     * এখানে কখনো API Key বা raw URL থাকে না।
     *
     * @return array{name:string,label:string,remote:bool,configured:bool,provider_label:string,active_label:?string,fallback:bool}
     */
    public function status(): array
    {
        $provider = $this->provider();
        $remote = $provider->isRemote();
        $configured = $provider->isConfigured();

        return [
            'name'           => $provider->name(),
            'label'          => $provider->label(),
            'remote'         => $remote,
            'configured'     => $configured,
            'provider_label' => $remote ? $provider->label().' ক্লাউড হোস্টিং' : $provider->label(),
            'active_label'   => $configured ? $provider->label() : ($remote ? null : $provider->label()),
            'fallback'       => $remote && ! $configured && (bool) config('images.local_fallback', true),
        ];
    }

    /** হোস্টিং থেকে ছবি মুছে ফেলা (best-effort, কখনো exception throw করে না) */
    public function delete(?string $provider, ?string $deleteUrl, ?string $providerId): bool
    {
        try {
            return $this->providerFor((string) $provider)->delete($deleteUrl, $providerId);
        } catch (\Throwable) {
            return false;
        }
    }
}
