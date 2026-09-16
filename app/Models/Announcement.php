<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use SoftDeletes;

    public const TYPES = [
        'notice'  => 'নোটিশ',
        'warning' => 'সতর্কবার্তা',
        'event'   => 'অনুষ্ঠান',
        'job'     => 'নিয়োগ',
        'general' => 'সাধারণ',
    ];

    protected $fillable = [
        'title', 'slug', 'body', 'image', 'link', 'link_text', 'type',
        'priority', 'status', 'is_visible', 'starts_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at'  => 'datetime',
            'expires_at' => 'datetime',
            'is_visible' => 'boolean',
            'priority'   => 'integer',
        ];
    }

    public function getRouteKeyName(): string { return 'slug'; }

    /** সক্রিয়: প্রকাশিত + দৃশ্যমান + সময়সীমার ভেতরে */
    public function scopeActive(Builder $q): Builder
    {
        $now = now();

        return $q->where('status', 'published')
                 ->where('is_visible', true)
                 ->where(fn (Builder $w) => $w->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
                 ->where(fn (Builder $w) => $w->whereNull('expires_at')->orWhere('expires_at', '>=', $now));
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderByDesc('priority')->orderByDesc('created_at');
    }

    public function getIsActiveAttribute(): bool
    {
        $now = now();
        return $this->status === 'published'
            && $this->is_visible
            && ($this->starts_at === null || $this->starts_at->lte($now))
            && ($this->expires_at === null || $this->expires_at->gte($now));
    }

    protected static function booted(): void
    {
        static::saving(function (Announcement $a) {
            if (empty($a->slug)) { $a->slug = mc_slug($a->title); }
            $a->slug = mc_unique_slug($a, $a->slug);
        });
    }
}
