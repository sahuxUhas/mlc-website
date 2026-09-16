<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group'];

    public const CACHE_KEY = 'site.settings.all';

    /** সব সেটিংস কী=>ভ্যালু (ক্যাশেড) */
    public static function all_settings(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return static::query()->pluck('value', 'key')->all();
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::all_settings()[$key] ?? $default;
    }

    public static function put(string $key, mixed $value, string $type = 'string', string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value, 'type' => $type, 'group' => $group]
        );
        static::flushCache();
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget('site.menus.*');
        Cache::forget('site.ads.*');
    }

    public function getCastedValueAttribute(): mixed
    {
        return match ($this->type) {
            'bool'  => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json'  => json_decode((string) $this->value, true),
            'int'   => (int) $this->value,
            default => $this->value,
        };
    }
}
