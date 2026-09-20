<?php

namespace App\Services\Images;

use Illuminate\Http\UploadedFile;

/**
 * Image Hosting Provider ইন্টারফেস।
 *
 * নতুন হোস্টিং (Cloudinary, S3, R2, অন্য API) যোগ করতে চাইলে শুধু এই
 * ইন্টারফেস implement করে ImageManager-এ রেজিস্টার করুন — বাকি সাইট,
 * অ্যাডমিন প্যানেল বা DB স্ট্রাকচার বদলাতে হবে না।
 */
interface ImageProvider
{
    /** মেশিন-নাম, যেমন: imgbb */
    public function name(): string;

    /** অ্যাডমিন UI-তে দেখানোর নিরাপদ নাম (API Key ছাড়া) */
    public function label(): string;

    /** ছবি কি বাইরের হোস্টিংয়ে যায়? (false = লোকাল ডিস্ক) */
    public function isRemote(): bool;

    /** প্রয়োজনীয় key/credential আছে কি? */
    public function isConfigured(): bool;

    /**
     * ছবি আপলোড করে ফলাফল ফেরত দেয়।
     *
     * @throws ImageUploadException ব্যর্থ হলে (message ব্যবহারকারী-বান্ধব)
     */
    public function upload(UploadedFile $file, string $folder, ?string $name = null): UploadedImage;

    /**
     * হোস্টিং থেকে ছবি মুছে ফেলা (best-effort — ব্যর্থ হলেও exception throw করে না)।
     */
    public function delete(?string $deleteUrl, ?string $providerId): bool;
}
