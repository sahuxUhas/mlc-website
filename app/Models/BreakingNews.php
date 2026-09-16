<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BreakingNews extends Model
{
    protected $table = 'breaking_news';

    protected $fillable = [
        'post_id', 'title', 'url', 'is_enabled', 'priority',
        'sort_order', 'starts_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at'  => 'datetime',
            'ends_at'    => 'datetime',
            'is_enabled' => 'boolean',
        ];
    }

    public function post() { return $this->belongsTo(Post::class); }

    /** হেডারের Breaking Bar এ যা দেখবে */
    public function scopeActive(Builder $q): Builder
    {
        $now = now();

        return $q->where('is_enabled', true)
                 ->where(fn (Builder $w) => $w->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
                 ->where(fn (Builder $w) => $w->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
                 ->orderByDesc('priority')
                 ->orderBy('sort_order');
    }

    public function getLinkAttribute(): string
    {
        if (! empty($this->url)) { return $this->url; }
        return $this->post ? route('news.show', $this->post->slug) : '#';
    }
}
