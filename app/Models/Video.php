<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'video_url', 'embed_type', 'thumbnail', 'description',
        'duration', 'is_reel', 'status', 'is_visible', 'published_at',
        // 'views' fillable নয় — শুধু বাস্তব ভিজিট থেকে গোনা হয় (ViewCounter)
        'scheduled_at', 'sort_order', 'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'is_reel'      => 'boolean',
            'is_visible'   => 'boolean',
            'views'        => 'integer',
        ];
    }

    public function getRouteKeyName(): string { return 'slug'; }

    public function comments() { return $this->morphMany(Comment::class, 'commentable'); }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', 'published')->where('is_visible', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderByDesc('published_at')->orderByDesc('id');
    }

    protected static function booted(): void
    {
        static::saving(function (Video $v) {
            if (empty($v->slug)) { $v->slug = mc_slug($v->title); }
            $v->slug = mc_unique_slug($v, $v->slug);
            $v->embed_type = $v->detectEmbedType();
        });
    }

    /** URL দেখে Facebook / YouTube / ফাইল শনাক্ত করে */
    public function detectEmbedType(): string
    {
        $url = (string) $this->video_url;
        if ($url === '') { return 'file'; }
        if (str_contains($url, 'facebook.com') || str_contains($url, 'fb.watch') || str_contains($url, 'fb.com')) { return 'facebook'; }
        if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) { return 'youtube'; }
        return 'file';
    }

    /** Embed-safe URL (iframe/plug-in এর জন্য) */
    public function embedUrl(): string
    {
        $url = (string) $this->video_url;

        if ($this->embed_type === 'youtube') {
            if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([\w-]{6,})~', $url, $m)) {
                return 'https://www.youtube-nocookie.com/embed/'.$m[1];
            }
            return $url;
        }

        if ($this->embed_type === 'facebook') {
            return 'https://www.facebook.com/plugins/video.php?href='.rawurlencode($url).'&show_text=false&width=560';
        }

        return $url;
    }
}
