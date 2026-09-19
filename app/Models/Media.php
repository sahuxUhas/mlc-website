<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\Images\ImageManager;

class Media extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $table = 'media';

    public const PROVIDERS = [
        'imgbb' => 'ImgBB ক্লাউড হোস্টিং',
        'local' => 'সার্ভার স্টোরেজ',
    ];

    protected $fillable = [
        'file_name', 'disk', 'provider', 'provider_id', 'path', 'provider_url',
        'provider_delete_url', 'thumb_url', 'folder', 'mime_type', 'extension',
        'size', 'width', 'height', 'alt_text', 'caption', 'uploaded_by', 'used_in', 'used_id',
    ];

    /**
     * provider_delete_url / provider_url শুধুমাত্র Backend-এর জন্য —
     * কোনো API response, Blade বা JS-এ এগুলো পাঠানো হয় না।
     */
    protected $hidden = ['provider_url', 'provider_delete_url'];

    protected function casts(): array
    {
        return ['size' => 'integer', 'width' => 'integer', 'height' => 'integer'];
    }

    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }

    /** ছবির পাবলিক URL — remote হলে signed proxy URL (raw hosting URL নয়) */
    public function getUrlAttribute(): string
    {
        return mc_image($this->path);
    }

    /** অ্যাডমিন থাম্বনেইল (এখানেও raw URL যায় না) */
    public function getThumbAttribute(): string
    {
        return mc_image($this->thumb_url ?: $this->path);
    }

    public function getIsImgbbAttribute(): bool
    {
        return $this->provider === 'imgbb' || $this->disk === 'imgbb';
    }

    public function getIsRemoteAttribute(): bool
    {
        return $this->is_imgbb;
    }

    /** UI-তে দেখানোর নিরাপদ লেবেল (raw URL/Key নয়) */
    public function getProviderLabelAttribute(): string
    {
        return self::PROVIDERS[$this->provider] ?? self::PROVIDERS['local'];
    }

    /** UI-তে দেখানোর জন্য ফাইলনাম (raw URL নয়) */
    public function getDisplayNameAttribute(): string
    {
        return $this->file_name ?: \App\Support\ImageUrl::safeLabel($this->path);
    }

    public function getHumanSizeAttribute(): string
    {
        $b = (int) $this->size;
        foreach (['B', 'KB', 'MB', 'GB'] as $unit) {
            if ($b < 1024) { return round($b, 1).' '.$unit; }
            $b /= 1024;
        }
        return round($b, 1).' TB';
    }

    public function getIsImageAttribute(): bool
    {
        return in_array(strtolower((string) $this->extension), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'], true);
    }

    public function scopeImages($q)
    {
        return $q->whereIn('extension', ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif']);
    }

    public function scopeSearch($q, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') { return $q; }
        $like = '%'.$term.'%';
        return $q->where(fn ($w) => $w->where('file_name', 'like', $like)
                                     ->orWhere('alt_text', 'like', $like)
                                     ->orWhere('caption', 'like', $like));
    }

    /** এই মিডিয়া কি অন্য কোনো সংবাদে (featured/OG/গ্যালারি) ব্যবহৃত হচ্ছে? */
    public function isReferenced(?int $ignorePostId = null): bool
    {
        $featured = Post::withTrashed()->where('featured_media_id', $this->id)
            ->when($ignorePostId, fn ($q) => $q->whereKeyNot($ignorePostId))->exists();

        if ($featured) {
            return true;
        }

        if (Post::withTrashed()->where('og_media_id', $this->id)
            ->when($ignorePostId, fn ($q) => $q->whereKeyNot($ignorePostId))->exists()) {
            return true;
        }

        return PostImage::where('media_id', $this->id)
            ->when($ignorePostId, fn ($q) => $q->where('post_id', '!=', $ignorePostId))
            ->exists();
    }

    /** হোস্টিং/ডিস্ক থেকে ফাইল মোছে (best-effort) */
    public function deleteFile(): void
    {
        if ($this->is_remote) {
            app(ImageManager::class)->delete($this->provider, $this->provider_delete_url, $this->provider_id);
            return;
        }

        try {
            \Illuminate\Support\Facades\Storage::disk($this->disk ?: 'uploads')->delete($this->path);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::info('Media file delete skipped', ['id' => $this->id, 'error' => $e->getMessage()]);
        }
    }
}
