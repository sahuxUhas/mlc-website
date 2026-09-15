<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'user_name', 'action', 'module', 'subject_type', 'subject_id',
        'description', 'properties', 'ip_address', 'user_agent', 'created_at',
    ];

    protected function casts(): array
    {
        return ['properties' => 'array', 'created_at' => 'datetime'];
    }

    public function user() { return $this->belongsTo(User::class); }

    /** অ্যাডমিনের গুরুত্বপূর্ণ অ্যাকশন লগ করার একক এন্ট্রি পয়েন্ট */
    public static function record(string $action, string $module, ?string $description = null, ?Model $subject = null, array $properties = []): void
    {
        static::create([
            'user_id'      => auth()->id(),
            'user_name'    => auth()->user()?->name ?? 'সিস্টেম',
            'action'       => $action,
            'module'       => $module,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject?->getKey(),
            'description'  => $description,
            'properties'   => $properties ?: null,
            'ip_address'   => request()->ip(),
            'user_agent'   => substr((string) request()->userAgent(), 0, 500),
            'created_at'   => now(),
        ]);
    }

    public function scopeNewest($q) { return $q->orderByDesc('created_at')->orderByDesc('id'); }
}
