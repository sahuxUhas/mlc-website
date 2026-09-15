<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

/**
 * ডাটাবেসের Website Settings অ্যাপ কনফিগ ও ভিউয়ে যুক্ত করে।
 * অ্যাডমিন প্যানেল থেকে বদলালেই সারা সাইটে প্রতিফলিত হয়।
 */
class SettingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ইনস্টলেশনের আগে (টেবিল না থাকলে) সাইট থামবে না
        if (! app()->runningInConsole() || app()->runningUnitTests()) {
            $this->applySettings();
        }

        try {
            if (! \Schema::hasTable('settings')) {
                return;
            }
            $this->applySettings();
        } catch (\Throwable $e) {
            // ডাটাবেস সংযোগ না থাকলে ডিফল্ট কনফিগই চালু থাকবে
        }
    }

    private function applySettings(): void
    {
        try {
            $settings = Setting::all_settings();
        } catch (\Throwable $e) {
            return;
        }

        if (empty($settings)) {
            return;
        }

        if (! empty($settings['site_name'])) {
            Config::set('app.name', $settings['site_name']);
        }

        if (! empty($settings['site_email'])) {
            Config::set('mail.from.address', $settings['site_email']);
        }

        if (! empty($settings['timezone'])) {
            Config::set('app.timezone', $settings['timezone']);
        }
    }
}
