<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Album extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'description', 'cover_image', 'is_visible',
        'photos_count', 'sort_order', 'published_at', 'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'is_visible' => 'boolean', 'photos_count' => 'integer'];
    }

    public function getRouteKeyName(): string { return 'slug'; }

    public function photos() { return $this->hasMany(AlbumPhoto::class)->orderBy('sort_order'); }

    public function scopeVisible($q) { return $q->where('is_visible', true); }
    public function scopeOrdered($q) { return $q->orderBy('sort_order')->orderByDesc('id'); }

    protected static function booted(): void
    {
        static::saving(function (Album $a) {
            if (empty($a->slug)) { $a->slug = mc_slug($a->title); }
            $a->slug = mc_unique_slug($a, $a->slug);
        });
    }
}
