<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ActivityLogger;
use App\Services\MediaUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/** ওয়েবসাইট সেটিংস — Name, Logo, Tagline, Favicon, Contact, Social Links, Footer */
class SettingController extends Controller
{
    public function __construct(private MediaUploader $uploader) {}

    /** সেটিংস ফর্মে যেসব ফিল্ড থাকবে — গ্রুপ অনুযায়ী সাজানো */
    public const FIELDS = [
        'general' => [
            ['key' => 'site_name',      'label' => 'ওয়েবসাইটের নাম',   'type' => 'text'],
            ['key' => 'site_prefix',    'label' => 'নামের পূর্বপদ',    'type' => 'text',   'hint' => 'যেমন: দৈনিক'],
            ['key' => 'site_tagline',   'label' => 'ট্যাগলাইন',        'type' => 'text'],
            ['key' => 'site_domain',    'label' => 'ডোমেইন',           'type' => 'text'],
            ['key' => 'site_logo',      'label' => 'লোগো',             'type' => 'image'],
            ['key' => 'site_favicon',   'label' => 'ফেভিকন',            'type' => 'image'],
            ['key' => 'site_location',  'label' => 'অবস্থান',           'type' => 'text'],
            ['key' => 'timezone',       'label' => 'টাইমজোন',          'type' => 'text'],
        ],
        'contact' => [
            ['key' => 'site_email',     'label' => 'ইমেইল',            'type' => 'text'],
            ['key' => 'site_phone',     'label' => 'ফোন',              'type' => 'text'],
            ['key' => 'site_address',   'label' => 'ঠিকানা',           'type' => 'textarea'],
            ['key' => 'newsroom_email', 'label' => 'নিউজরুম ইমেইল',     'type' => 'text'],
        ],
        'social' => [
            ['key' => 'social_facebook','label' => 'ফেসবুক URL',        'type' => 'text'],
            ['key' => 'social_youtube', 'label' => 'ইউটিউব URL',        'type' => 'text'],
            ['key' => 'social_twitter', 'label' => 'টুইটার / X URL',    'type' => 'text'],
            ['key' => 'social_instagram','label'=> 'ইনস্টাগ্রাম URL',   'type' => 'text'],
            ['key' => 'social_whatsapp','label' => 'হোয়াটসঅ্যাপ নম্বর',  'type' => 'text'],
        ],
        'footer' => [
            ['key' => 'footer_text',    'label' => 'ফুটার টেক্সট',       'type' => 'textarea'],
            ['key' => 'copyright_text', 'label' => 'কপিরাইট',          'type' => 'text'],
            ['key' => 'footer_about',   'label' => 'ফুটার পরিচিতি',     'type' => 'textarea'],
        ],
        'behavior' => [
            ['key' => 'posts_per_page',      'label' => 'প্রতি পেজে সংবাদ', 'type' => 'text'],
            ['key' => 'comments_auto_approve','label'=> 'মন্তব্য অটো-অনুমোদন', 'type' => 'bool'],
            ['key' => 'comments_enabled',    'label' => 'মন্তব্য চালু',       'type' => 'bool'],
            ['key' => 'site_maintenance',    'label' => 'মেইনটেন্যান্স মোড',   'type' => 'bool'],
        ],
        'integrations' => [
            ['key' => 'analytics_code', 'label' => 'Google Analytics কোড', 'type' => 'textarea'],
            ['key' => 'custom_head_html','label'=> 'কাস্টম Head HTML',     'type' => 'textarea'],
            ['key' => 'custom_body_html','label'=> 'কাস্টম Body HTML',     'type' => 'textarea'],
        ],
    ];

    public function edit()
    {
        return view('admin.settings.edit', [
            'settings' => Setting::all_settings(),
            'groups'   => self::FIELDS,
        ]);
    }

    public function update(Request $request)
    {
        $rules = [];
        $images = [];

        foreach (self::FIELDS as $group => $fields) {
            foreach ($fields as $field) {
                $key = $field['key'];

                $rules[$key] = match ($field['type']) {
                    'bool'     => ['nullable', 'boolean'],
                    'image'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,ico', 'max:2048'],
                    'textarea' => ['nullable', 'string', 'max:8000'],
                    default    => ['nullable', 'string', 'max:600'],
                };

                if ($field['type'] === 'image') { $images[] = $key; }
            }
        }

        $validated = $request->validate($rules);

        foreach (self::FIELDS as $group => $fields) {
            foreach ($fields as $field) {
                $key  = $field['key'];
                $type = $field['type'];

                if ($type === 'image') {
                    if ($request->hasFile($key)) {
                        Setting::put($key, $this->uploader->store($request->file($key), 'settings')->path, 'image', $group);
                    }
                    continue;
                }

                $value = $type === 'bool' ? ($request->boolean($key) ? '1' : '0') : ($validated[$key] ?? '');
                Setting::put($key, $value, $type, $group);
            }
        }

        Setting::flushCache();
        Cache::flush();

        ActivityLogger::log('updated', 'settings', 'সাইট সেটিংস হালনাগাদ করা হয়েছে');

        return back()->with('success', 'সাইট সেটিংস সংরক্ষিত হয়েছে।');
    }
}
