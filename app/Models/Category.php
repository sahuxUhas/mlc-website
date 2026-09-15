<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'parent_id', 'name', 'slug', 'icon', 'color', 'union_name', 'description',
        'image', 'is_visible', 'show_on_home', 'show_in_menu', 'sort_order',
        'meta_title', 'meta_description', 'posts_count',
    ];

    protected function casts(): array
    {
        return [
            'is_visible'   => 'boolean',
            'show_on_home' => 'boolean',
            'show_in_menu' => 'boolean',
            'sort_order'   => 'integer',
            'posts_count'  => 'integer',
        ];
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function scopeVisible($q)
    {
        return $q->where('is_visible', true);
    }

    public function scopeRoot($q)
    {
        return $q->whereNull('parent_id');
    }

    public function scopeOrdered($q)
    {
        return $q->orderBy('sort_order')->orderBy('name');
    }

    public function scopeForMenu($q)
    {
        return $q->visible()->root()->where('show_in_menu', true)->ordered();
    }

    public function scopeForHome($q)
    {
        return $q->visible()->root()->where('show_on_home', true)->ordered();
    }

    public function isSubcategory(): bool
    {
        return $this->parent_id !== null;
    }
}
