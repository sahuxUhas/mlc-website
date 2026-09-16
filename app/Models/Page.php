<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'content', 'excerpt', 'featured_image', 'template',
        'is_visible', 'show_in_footer', 'sort_order', 'meta_title',
        'meta_description', 'meta_keywords', 'og_image', 'canonical_url',
    ];

    protected function casts(): array
    {
        return ['is_visible' => 'boolean', 'show_in_footer' => 'boolean'];
    }

    public function getRouteKeyName(): string { return 'slug'; }

    public function scopeVisible($q) { return $q->where('is_visible', true); }

    protected static function booted(): void
    {
        static::saving(function (Page $p) {
            if (empty($p->slug)) { $p->slug = mc_slug($p->title); }
            $p->slug = mc_unique_slug($p, $p->slug);
        });
    }
}
