<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

/** অ্যাডমিনের গুরুত্বপূর্ণ অ্যাকশন লগ করার সুবিধাজনক র‍্যাপার */
class ActivityLogger
{
    public static function log(string $action, string $module, ?string $description = null, ?Model $subject = null, array $properties = []): void
    {
        ActivityLog::record($action, $module, $description, $subject, $properties);
    }

    public static function created(Model $subject, string $module, ?string $description = null): void
    {
        self::log('created', $module, $description, $subject);
    }

    public static function updated(Model $subject, string $module, ?string $description = null, array $changed = []): void
    {
        self::log('updated', $module, $description, $subject, ['changed_fields' => array_keys($changed)]);
    }

    public static function deleted(Model $subject, string $module, ?string $description = null): void
    {
        self::log('deleted', $module, $description, $subject);
    }

    public static function published(Model $subject, string $module = 'news', ?string $description = null): void
    {
        self::log('published', $module, $description, $subject);
    }
}
