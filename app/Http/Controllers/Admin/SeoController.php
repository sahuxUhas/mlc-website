<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ActivityLogger;
use App\Services\MediaUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/** গ্লোবাল SEO সেটিংস — Meta, OG, Sitemap ও robots.txt নিয়ন্ত্রণ */
class SeoController extends Controller
{
    public function __construct(private MediaUploader $uploader) {}

    public const FIELDS = [
        ['key' => 'seo_title',            'label' => 'ডিফল্ট মেটা টাইটেল',       'type' => 'text'],
        ['key' => 'seo_description',      'label' => 'ডিফল্ট মেটা বিবরণ',        'type' => 'textarea'],
        ['key' => 'seo_keywords',         'label' => 'ডিফল্ট কীওয়ার্ড',           'type' => 'text'],
        ['key' => 'seo_og_title',         'label' => 'OG টাইটেল',                'type' => 'text'],
        ['key' => 'seo_og_description',   'label' => 'OG বিবরণ',                 'type' => 'textarea'],
        ['key' => 'seo_canonical',        'label' => 'ডিফল্ট Canonical URL',      'type' => 'text'],
        ['key' => 'seo_robots',           'label' => 'ডিফল্ট Robots নির্দেশনা',    'type' => 'text', 'hint' => 'যেমন: index, follow'],
        ['key' => 'robots_txt',           'label' => 'robots.txt কনটেন্ট',        'type' => 'textarea'],
        ['key' => 'seo_sitemap_enabled',  'label' => 'XML Sitemap চালু',          'type' => 'bool'],
        ['key' => 'seo_schema_enabled',   'label' => 'Article Structured Data',   'type' => 'bool'],
        ['key' => 'seo_verification_google','label'=> 'Google Search Console কোড', 'type' => 'text'],
        ['key' => 'seo_verification_fb',  'label' => 'Facebook ডোমেইন যাচাই',      'type' => 'text'],
    ];

    public function edit()
    {
        return view('admin.settings.seo', [
            'settings' => Setting::all_settings(),
            'fields'   => self::FIELDS,
        ]);
    }

    public function update(Request $request)
    {
        $rules = [];
        foreach (self::FIELDS as $field) {
            $rules[$field['key']] = match ($field['type']) {
                'bool'     => ['nullable', 'boolean'],
                'textarea' => ['nullable', 'string', 'max:8000'],
                default    => ['nullable', 'string', 'max:600'],
            };
        }

        $validated = $request->validate($rules);

        foreach (self::FIELDS as $field) {
            $key = $field['key'];
            $value = $field['type'] === 'bool' ? ($request->boolean($key) ? '1' : '0') : ($validated[$key] ?? '');
            Setting::put($key, $value, $field['type'], 'seo');
        }

        if ($request->hasFile('seo_og_image')) {
            Setting::put('seo_og_image', $this->uploader->store($request->file('seo_og_image'), 'settings/seo')->path, 'image', 'seo');
        }

        Setting::flushCache();
        Cache::forget('site.sitemap.xml');

        ActivityLogger::log('updated', 'seo', 'SEO সেটিংস হালনাগাদ করা হয়েছে');

        return back()->with('success', 'SEO সেটিংস সংরক্ষিত হয়েছে।');
    }
}
