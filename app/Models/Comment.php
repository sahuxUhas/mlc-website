<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUSES = [
        'pending'  => 'অপেক্ষমাণ',
        'approved' => 'অনুমোদিত',
        'rejected' => 'প্রত্যাখ্যাত',
        'spam'     => 'স্প্যাম',
    ];

    /** অ্যাডমিন ডেমো থেকে সংরক্ষিত নিষিদ্ধ শব্দ তালিকা (Anti-Spam) */
    public const BAD_WORDS = ['ধত্ত', 'হারামি', 'শালা', 'বদমাশ', 'idiot', 'fuck', 'shit'];

    protected $fillable = [
        'commentable_type', 'commentable_id', 'parent_id', 'user_id',
        'guest_name', 'guest_email', 'body', 'status', 'report_count',
        'is_reported', 'ip_address', 'user_agent',
    ];

    protected function casts(): array
    {
        return ['is_reported' => 'boolean', 'report_count' => 'integer'];
    }

    public function commentable() { return $this->morphTo(); }
    public function parent()      { return $this->belongsTo(Comment::class, 'parent_id'); }
    public function replies()     { return $this->hasMany(Comment::class, 'parent_id'); }
    public function user()        { return $this->belongsTo(User::class); }

    public function scopeApproved(Builder $q): Builder  { return $q->where('status', 'approved'); }
    public function scopePending(Builder $q): Builder   { return $q->where('status', 'pending'); }
    public function scopeReported(Builder $q): Builder  { return $q->where('is_reported', true); }
    public function scopeTopLevel(Builder $q): Builder  { return $q->whereNull('parent_id'); }
    public function scopeNewest(Builder $q): Builder    { return $q->orderByDesc('created_at'); }

    public function getAuthorNameAttribute(): string
    {
        return $this->user?->name ?: ($this->guest_name ?: 'অতিথি');
    }

    /** স্প্যাম শব্দ আছে কিনা */
    public function containsBadWords(): bool
    {
        $body = mb_strtolower($this->body ?? '');
        foreach (self::BAD_WORDS as $word) {
            if ($word !== '' && str_contains($body, mb_strtolower($word))) {
                return true;
            }
        }
        return false;
    }
}
