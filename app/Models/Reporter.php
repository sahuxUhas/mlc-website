<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reporter extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'slug', 'designation', 'photo', 'bio',
        'email', 'phone', 'address', 'facebook', 'is_visible',
        'sort_order', 'posts_count',
    ];

    protected function casts(): array
    {
        return ['is_visible' => 'boolean', 'posts_count' => 'integer'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function scopeVisible($q)
    {
        return $q->where('is_visible', true);
    }

    public function scopeOrdered($q)
    {
        return $q->orderBy('sort_order')->orderBy('name');
    }

    /** Published News Count — রিয়েল টাইম */
    public function publishedPostsCount(): int
    {
        return $this->posts()->where('status', 'published')->count();
    }
}
