<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Post extends Model
{
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
        'featured_image', 'image_caption', 'image_credit', 'excerpt', 'content',
        'video_url', 'location', 'status', 'is_featured', 'is_breaking', 'allow_comments',
        'published_at', 'scheduled_at', 'views', 'comments_count', 'sort_order',
        'meta_title', 'meta_description', 'meta_keywords', 'og_title', 'og_description',
        'og_image', 'canonical_url',
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

    public function scopeVisible(Builder $q): Builder
    {
        return $q->published()->withTrashed() === $q ? $q : $q->published();
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

        // ক্যাটাগরির সংবাদ-সংখ্যা হালনাগাদ
        static::saved(function (Post $post) {
            $post->category?->decrement('posts_count');
            $post->category?->increment('posts_count');
        });
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
