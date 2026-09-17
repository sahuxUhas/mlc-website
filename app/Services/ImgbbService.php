<?php
namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * ImgBB Image Upload Service
 * API Docs: https://api.imgbb.com/
 * User provided API Key: 4bfac8cf6fa4714236c08292299d2862
 */
class ImgbbService
{
    private string $apiKey;
    private string $endpoint = 'https://api.imgbb.com/1/upload';
    private bool $enabled;

    public function __construct()
    {
        $this->apiKey = (string) (env('IMGBB_API_KEY') ?: config('services.imgbb.key') ?: \App\Models\Setting::get('imgbb_api_key', ''));
        $this->enabled = (bool) (env('IMGBB_ENABLED', true) ?: \App\Models\Setting::get('imgbb_enabled', '1'));
    }

    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->apiKey);
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Upload image to ImgBB
     * @return array ['url', 'display_url', 'thumb', 'medium', 'delete_url', 'width', 'height', 'size']
     * @throws ValidationException
     */
    public function upload(UploadedFile $file, ?string $name = null): array
    {
        if (!$this->isEnabled()) {
            throw ValidationException::withMessages(['files' => 'ImgBB API key সেট করা নেই। .env এ IMGBB_API_KEY যোগ করুন।']);
        }

        // ImgBB accepts base64 or binary - we use base64 for reliability
        $base64 = base64_encode(file_get_contents($file->getRealPath()));

        try {
            $response = Http::timeout(30)->asForm()->post($this->endpoint, [
                'key' => $this->apiKey,
                'image' => $base64,
                'name' => $name ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            ]);

            if (!$response->successful()) {
                Log::warning('ImgBB upload failed HTTP', ['status' => $response->status(), 'body' => $response->body()]);
                throw ValidationException::withMessages(['files' => 'ImgBB আপলোড ব্যর্থ (HTTP '.$response->status().'): '.$response->body()]);
            }

            $json = $response->json();

            if (!isset($json['success']) || $json['success'] !== true) {
                $error = $json['error']['message'] ?? 'Unknown error';
                Log::warning('ImgBB upload failed API', ['response' => $json]);
                throw ValidationException::withMessages(['files' => 'ImgBB API error: '.$error]);
            }

            $data = $json['data'] ?? [];

            return [
                'url' => $data['url'] ?? $data['display_url'] ?? '',
                'display_url' => $data['display_url'] ?? $data['url'] ?? '',
                'thumb' => $data['thumb']['url'] ?? $data['display_url'] ?? '',
                'medium' => $data['medium']['url'] ?? $data['display_url'] ?? '',
                'delete_url' => $data['delete_url'] ?? '',
                'width' => $data['width'] ?? null,
                'height' => $data['height'] ?? null,
                'size' => $data['size'] ?? $file->getSize(),
                'id' => $data['id'] ?? null,
                'title' => $data['title'] ?? null,
            ];

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('ImgBB upload exception', ['error' => $e->getMessage()]);
            throw ValidationException::withMessages(['files' => 'ImgBB আপলোডে সমস্যা: '.$e->getMessage()]);
        }
    }

    /**
     * Upload multiple files
     */
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
