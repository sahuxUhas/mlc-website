<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUSES = [
        'draft'     => 'খসড়া',
        'pending'   => 'রিভিউতে',
        'published' => 'প্রকাশিত',
        'scheduled' => 'নির্ধারিত',
        'archived'  => 'আর্কাইভড',
    ];

    protected $fillable = [
        'title', 'slug', 'category_id', 'subcategory_id', 'reporter_id', 'author_id',
        'featured_image', 'featured_media_id', 'image_caption', 'image_credit', 'excerpt', 'content',
        'video_url', 'location', 'status', 'is_featured', 'is_breaking', 'allow_comments',
        'published_at', 'scheduled_at', 'views', 'comments_count', 'sort_order',
        'meta_title', 'meta_description', 'meta_keywords', 'og_title', 'og_description',
        'og_image', 'og_media_id', 'canonical_url',
    ];

    protected function casts(): array
    {
        return [
            'published_at'    => 'datetime',
            'scheduled_at'    => 'datetime',
            'is_featured'     => 'boolean',
            'is_breaking'     => 'boolean',
            'allow_comments'  => 'boolean',
            'views'           => 'integer',
            'comments_count'  => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /* ---------------- Relations ---------------- */

    public function category()      { return $this->belongsTo(Category::class); }
    public function subcategory()   { return $this->belongsTo(Category::class, 'subcategory_id'); }
    public function reporter()      { return $this->belongsTo(Reporter::class); }
    public function author()        { return $this->belongsTo(User::class, 'author_id'); }
    public function images()        { return $this->hasMany(PostImage::class)->orderBy('sort_order'); }
    public function featuredMedia() { return $this->belongsTo(Media::class, 'featured_media_id'); }
    public function ogMedia()       { return $this->belongsTo(Media::class, 'og_media_id'); }
    public function comments()      { return $this->morphMany(Comment::class, 'commentable'); }
    public function breakingEntry() { return $this->hasOne(BreakingNews::class); }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    /* ---------------- Scopes ---------------- */

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', 'published')
                 ->whereNotNull('published_at')
                 ->where('published_at', '<=', now());
    }

    /** প্রকাশিত + ভবিষ্যতের তারিখ নয় — পাবলিক সাইটে দেখানো যাবে এমন সংবাদ */
    public function scopeVisible(Builder $q): Builder
    {
        return $q->published();
    }

    public function scopeFeatured(Builder $q): Builder
    {
        return $q->where('is_featured', true);
    }

    public function scopeBreaking(Builder $q): Builder
    {
        return $q->where('is_breaking', true);
    }

    /** সময় অনুযায়ী নির্ধারিত (scheduled) নিউজ অটো-পাবলিশ করার জন্য */
    public function scopeDueScheduled(Builder $q): Builder
    {
        return $q->where('status', 'scheduled')
                 ->whereNotNull('scheduled_at')
                 ->where('scheduled_at', '<=', now());
    }

    public function scopeLatestFirst(Builder $q): Builder
    {
        return $q->orderByDesc('published_at')->orderByDesc('id');
    }

    public function scopePopular(Builder $q): Builder
    {
        return $q->orderByDesc('views');
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $q;
        }

        return $q->where(function (Builder $inner) use ($term) {
            $like = '%'.Str::lower($term).'%';
            $inner->whereRaw('LOWER(title) LIKE ?', [$like])
                  ->orWhereRaw('LOWER(excerpt) LIKE ?', [$like])
                  ->orWhereRaw('LOWER(content) LIKE ?', [$like]);
        });
    }

    public function scopeForCategory(Builder $q, Category $category): Builder
    {
        $ids = $category->children()->pluck('id')->push($category->id)->all();

        return $q->whereIn('category_id', $ids);
    }

    /* ---------------- Accessors ---------------- */

    public function getIsLiveAttribute(): bool
    {
        return $this->status === 'published'
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }

    /**
     * Featured Image URL — remote হলে signed proxy URL (raw hosting URL নয়)।
     * path আকারে থাকলে সেটিই ব্যবহৃত হয় ⇒ তালিকা পেজে অতিরিক্ত কুয়েরি হয় না।
     */
    public function getFeaturedImageUrlAttribute(): string
    {
        if (trim((string) $this->featured_image) !== '') {
            return mc_image($this->featured_image);
        }

        if ($this->featured_media_id) {
            return mc_image($this->featuredMedia?->path);
        }

        return mc_image(null);
    }

    /** অ্যাডমিন প্রিভিউ থাম্বনেইল (হোস্টিংয়ের ছোট সংস্করণ থাকলে সেটি) */
    public function getFeaturedThumbAttribute(): string
    {
        if ($this->featured_media_id) {
            $media = $this->relationLoaded('featuredMedia') ? $this->featuredMedia : null;

            if ($media) {
                return mc_image($media->thumb_url ?: $media->path);
            }
        }

        return mc_image($this->featured_image);
    }

    /** OG Image URL (সোশ্যাল শেয়ার) */
    public function getOgImageUrlAttribute(): string
    {
        if (trim((string) $this->og_image) !== '') {
            return mc_image($this->og_image);
        }

        if ($this->og_media_id) {
            return mc_image($this->ogMedia?->path);
        }

        return trim((string) $this->featured_image) !== '' || $this->featured_media_id
            ? $this->featured_image_url
            : '';
    }

    /** সম্পূর্ণ কনটেন্ট render (ছবির রেফারেন্স → signed URL) */
    public function renderedContent(): string
    {
        return mc_content($this->content);
    }

    public function getReadingTimeAttribute(): int
    {
        $words = str_word_count(strip_tags((string) $this->content));
        // বাংলা টেক্সটে শবদ গণনা আনুমানিক — প্রতি মিনিটে ~১৮০ শব্দ
        return max(1, (int) ceil($words / 180));
    }

    /* ---------------- Mutators ---------------- */

    protected static function booted(): void
    {
        static::creating(function (Post $post) {
            if (empty($post->slug)) {
                $post->slug = mc_slug($post->title);
            }
            $post->slug = mc_unique_slug($post, $post->slug);
        });

        static::updating(function (Post $post) {
            if ($post->isDirty('title') && empty($post->getOriginal('slug'))) {
                $post->slug = mc_slug($post->title);
            }
            if ($post->isDirty('slug')) {
                $post->slug = mc_unique_slug($post, $post->slug);
            }
        });

        // ক্যাটাগরির প্রকাশিত সংবাদ-সংখ্যা সঠিকভাবে পুনর্গণনা + পাবলিক ক্যাশ রিফ্রেশ
        // (অ্যাডমিন থেকে সংবাদ বদলালে সাথে সাথে Public Website-এ দেখা যাবে)
        static::saved(function (Post $post) {
            static::refreshCategoryCount($post->category_id);

            if ($post->isDirty('category_id') && $post->getOriginal('category_id')) {
                static::refreshCategoryCount($post->getOriginal('category_id'));
            }

            \App\Support\PublicCache::flushForContent();
        });

        static::deleted(function (Post $post) {
            static::refreshCategoryCount($post->category_id);
            \App\Support\PublicCache::flushForContent();
        });

        static::restored(function (Post $post) {
            static::refreshCategoryCount($post->category_id);
            \App\Support\PublicCache::flushForContent();
        });

        // স্থায়ীভাবে মুছলে গ্যালারির মিডিয়া রেফারেন্সও পরিষ্কার করা হয়
        // (forceDeleting — DB cascade delete হওয়ার আগেই ছবির তথ্য নেওয়া হয়)
        static::forceDeleting(function (Post $post) {
            \App\Support\PublicCache::flushForContent();

            $post->images()->get()->each(function (PostImage $image) {
                try {
                    $media = $image->media;

                    // নিজের রেফারেন্স বাদ দিয়ে দেখা হয় — অন্য কোথাও ব্যবহৃত না হলে মিডিয়াও মোছে
                    if ($media && ! $media->isReferenced($post->id)) {
                        $media->deleteFile();
                        $media->forceDelete();
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::info('Post image cleanup skipped', [
                        'post' => $post->id, 'error' => $e->getMessage(),
                    ]);
                }
            });
        });
    }

    /** নির্দিষ্ট ক্যাটাগরির posts_count পুনর্গণনা (ডেনormalized কাউন্টার সঠিক রাখতে) */
    public static function refreshCategoryCount(?int $categoryId): void
    {
        if (! $categoryId) {
            return;
        }

        $count = static::withTrashed()->where('category_id', $categoryId)->count();

        Category::where('id', $categoryId)->update(['posts_count' => $count]);
    }

    public function incrementViews(): void
    {
        static::withoutTimestamps(fn () => $this->increment('views'));
    }

    /** সম্পর্কিত সংবাদ — একই ক্যাটাগরি, বর্তমানটি বাদে */
    public function related(int $limit = 4)
    {
        return static::published()
            ->where('category_id', $this->category_id)
            ->whereKeyNot($this->getKey())
            ->latestFirst()
            ->limit($limit)
            ->get();
    }
}
