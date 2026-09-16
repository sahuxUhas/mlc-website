<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Advertisement extends Model
{
    use SoftDeletes;

    /** অ্যাডমিন থেকে নির্বাচনযোগ্য Position তালিকা */
    public const POSITIONS = [
        'top_header'             => 'টপ হেডার (728x90)',
        'homepage'               => 'হোমপেজ',
        'article_top'            => 'আর্টিকেল — উপরে',
        'article_middle'         => 'আর্টিকেল — মাঝে',
        'article_bottom'         => 'আর্টিকেল — নিচে',
        'sidebar'                => 'সাইডবার',
        'footer'                 => 'ফুটার',
        'after_latest_highlight' => 'হোম — সর্বশেষের পরে',
    ];

    protected $fillable = [
        'title', 'position', 'type', 'image', 'html_code', 'link', 'link_target',
        'is_enabled', 'priority', 'impressions', 'clicks', 'starts_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at'  => 'date',
            'ends_at'    => 'date',
            'is_enabled' => 'boolean',
            'impressions'=> 'integer',
            'clicks'     => 'integer',
        ];
    }

    /** নির্দিষ্ট position এর সক্রিয় বিজ্ঞাপন (ক্যাশ করা) */
    public function scopeForPosition(Builder $q, string $position): Builder
    {
        $today = now()->toDateString();

        return $q->where('position', $position)
                 ->where('is_enabled', true)
                 ->where(fn (Builder $w) => $w->whereNull('starts_at')->orWhere('starts_at', '<=', $today))
                 ->where(fn (Builder $w) => $w->whereNull('ends_at')->orWhere('ends_at', '>=', $today))
                 ->orderByDesc('priority')
                 ->orderByDesc('id');
    }

    public function positionLabel(): string
    {
        return self::POSITIONS[$this->position] ?? $this->position;
    }
}
