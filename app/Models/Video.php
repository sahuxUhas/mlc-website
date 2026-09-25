<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use SoftDeletes;

    /** সিড করা ফেসবুক শেয়ার কোড → আসল রিল (এমবেডের জন্য, UI-তে দেখানো হয় না) */
    private const SHARE_TO_REEL = [
        '19Xg5B3P78' => '1083023034090121',
        '1Ber5oogFd' => '1133409922345993',
        '14mVcr6iSDs' => '1429431282481078',
        '1CY2kvyRSY' => '1389678412664466',
        '1BGLr3jiCs' => '2906303706397929',
        '19RNHFTGiQ' => '1616410413485433',
    ];

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
        if ($this->isDirectMedia($url)) { return 'file'; }
        if (str_contains($url, 'facebook.com') || str_contains($url, 'fb.watch') || str_contains($url, 'fb.com')) { return 'facebook'; }
        if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) { return 'youtube'; }
        return 'file';
    }

    /**
     * পাবলিক প্লেয়ার।
     * সরাসরি ফাইল → HTML5 (নিজস্ব Play/Pause, Volume, Fullscreen)।
     * YouTube/Facebook → প্রোভাইডার প্লেয়ার, autoplay বন্ধ।
     * Raw URL কখনো টেক্সট হিসেবে রেন্ডার করা হয় না।
     *
     * @return array{kind:string,src?:string,mime?:string}
     */
    public function publicPlayer(): array
    {
        $url = trim((string) $this->video_url);
        if ($url === '') {
            return ['kind' => 'none'];
        }

        if ($this->isDirectMedia($url)) {
            return [
                'kind' => 'file',
                'src'  => $url,
                'mime' => $this->mediaMime($url),
            ];
        }

        $type = $this->embed_type ?: $this->detectEmbedType();
        if (in_array($type, ['youtube', 'facebook'], true)) {
            return ['kind' => 'embed', 'src' => $this->embedUrl()];
        }

        // অজানা লিংক <video src>-এ দিলে ব্রাউজার HTML5 এরর দেখায় — তাই এমবেড করা হয় না
        return ['kind' => 'none'];
    }

    public function isDirectMedia(?string $url = null): bool
    {
        $path = strtolower((string) parse_url($url ?? (string) $this->video_url, PHP_URL_PATH));

        return (bool) preg_match('/\.(mp4|webm|ogg|ogv|m4v|mov)$/', $path);
    }

    /** Embed-safe URL — autoplay বন্ধ, ক্যাপশন টেক্সট বন্ধ */
    public function embedUrl(): string
    {
        $type = $this->embed_type ?: $this->detectEmbedType();
        $url = (string) $this->video_url;

        if ($type === 'youtube') {
            if (preg_match('~(?:youtube\\.com/(?:watch\\?v=|embed/|shorts/)|youtu\\.be/)([\\w-]{6,})~', $url, $m)) {
                return 'https://www.youtube-nocookie.com/embed/'.$m[1].'?rel=0&modestbranding=1&playsinline=1&autoplay=0';
            }

            return $url;
        }

        if ($type === 'facebook') {
            return 'https://www.facebook.com/plugins/video.php?href='.rawurlencode($this->preferredFacebookHref())
                .'&show_text=false&autoplay=false&width=1280';
        }

        return $url;
    }

    /** শেয়ার লিংক এমবেডে প্রায়ই ভাঙে — জানা রিল থাকলে সেটি (পেজে দেখানো হয় না) */
    public function preferredFacebookHref(): string
    {
        $url = (string) $this->video_url;
        if (preg_match('~facebook\\.com/share/(?:v|r)/([A-Za-z0-9]+)~', $url, $m)) {
            $reel = self::SHARE_TO_REEL[$m[1]] ?? null;
            if ($reel) {
                return 'https://www.facebook.com/reel/'.$reel.'/';
            }
        }

        return $url;
    }

    private function mediaMime(string $url): string
    {
        $path = strtolower((string) parse_url($url, PHP_URL_PATH));

        return match (true) {
            str_ends_with($path, '.webm') => 'video/webm',
            str_ends_with($path, '.ogg'), str_ends_with($path, '.ogv') => 'video/ogg',
            default => 'video/mp4',
        };
    }
}
