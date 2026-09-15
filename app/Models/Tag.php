<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['name', 'slug', 'posts_count'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function posts()
    {
        return $this->morphedByMany(Post::class, 'taggable');
    }

    protected static function booted(): void
    {
        static::creating(function (Tag $tag) {
            if (empty($tag->slug)) {
                $tag->slug = mc_slug($tag->name);
            }
        });
    }

    /** কমা-বিচ্ছিন্ন ট্যাগ স্ট্রিং থেকে sync */
    public static function syncFromString(Post $post, ?string $raw): void
    {
        $names = collect(preg_split('/[,،\n]+/u', (string) $raw))
            ->map(fn ($t) => trim($t))
            ->filter(fn ($t) => $t !== '')
            ->unique()
            ->values();

        $ids = $names->map(function (string $name) {
            return static::firstOrCreate(
                ['slug' => mc_slug($name)],
                ['name' => $name]
            )->id;
        });

        $post->tags()->sync($ids->all());
    }
}
