<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    protected $fillable = ['email', 'is_active', 'token', 'subscribed_at'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'subscribed_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (NewsletterSubscriber $s) {
            $s->token = $s->token ?: Str::random(48);
            $s->subscribed_at = $s->subscribed_at ?: now();
        });
    }
}
