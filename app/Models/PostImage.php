<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * সংবাদের গ্যালারি ছবি।
 * path = লোকাল পাথ অথবা হোস্টিং URL (Backend), media_id = মিডিয়া রেফারেন্স।
 */
class PostImage extends Model
{
    protected $fillable = ['post_id', 'media_id', 'path', 'caption', 'credit', 'alt_text', 'sort_order'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function media()
    {
        return $this->belongsTo(Media::class);
    }

    public function scopeOrdered($q)
    {
        return $q->orderBy('sort_order')->orderBy('id');
    }

    /** পাবলিক/অ্যাডমিন প্রিভিউ URL — remote হলে signed proxy URL */
    public function url(): string
    {
        return mc_image($this->thumbPath());
    }

    /** বড় ছবির URL (lightbox / আর্টিকেল পেজ) */
    public function fullUrl(): string
    {
        return mc_image($this->media?->path ?: $this->path);
    }

    /** ছোট প্রিভিউ (হোস্টিং thumb থাকলে সেটি, নইলে মূল ছবি) */
    public function thumbPath(): ?string
    {
        $media = $this->relationLoaded('media') ? $this->media : null;

        if ($media) {
            return $media->thumb_url ?: ($media->path ?: $this->path);
        }

        return $this->path;
    }

    /** UI-তে দেখানোর নিরাপদ লেবেল */
    public function label(): string
    {
        if ($this->caption) {
            return $this->caption;
        }

        if ($this->relationLoaded('media') && $this->media) {
            return $this->media->display_name;
        }

        return \App\Support\ImageUrl::safeLabel($this->path);
    }
}
