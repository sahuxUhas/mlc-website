<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    public const LOCATIONS = [
        'main'          => 'প্রধান মেনু',
        'top_bar'       => 'টপ বার',
        'more'          => '"আরও" ড্রপডাউন',
        'footer'        => 'ফুটার',
        'mobile_bottom' => 'মোবাইল বটম নেভিগেশন',
    ];

    protected $fillable = [
        'location', 'parent_id', 'label', 'icon', 'link_type', 'url',
        'reference_id', 'is_enabled', 'open_in_new_tab', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['is_enabled' => 'boolean', 'open_in_new_tab' => 'boolean', 'sort_order' => 'integer'];
    }

    public function children() { return $this->hasMany(Menu::class, 'parent_id')->orderBy('sort_order'); }
    public function parent()   { return $this->belongsTo(Menu::class, 'parent_id'); }

    public function scopeInLocation($q, string $location)
    {
        return $q->where('location', $location)->where('is_enabled', true)
                 ->whereNull('parent_id')->orderBy('sort_order');
    }

    /** link_type অনুযায়ী চূড়ান্ত URL */
    public function getHrefAttribute(): string
    {
        switch ($this->link_type) {
            case 'external': return $this->url ?: '#';
            case 'category':
                $cat = $this->reference_id ? Category::find($this->reference_id) : null;
                return $cat ? route('category.show', $cat->slug) : ($this->url ?: '#');
            case 'page':
                $page = $this->reference_id ? Page::find($this->reference_id) : null;
                return $page ? route('page.show', $page->slug) : ($this->url ?: '#');
            default:         return $this->url ?: route('home');
        }
    }
}
