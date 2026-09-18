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
            ['key' => 'site_name_a',    'label' => 'লোগোর পাশের নাম — প্রথম অংশ', 'type' => 'text', 'hint' => 'হেডার/ফুটারে বড় করে দেখানো হয়। যেমন: মহালছড়ি'],
            ['key' => 'site_name_b',    'label' => 'লোগোর পাশের নাম — দ্বিতীয় অংশ', 'type' => 'text', 'hint' => 'এই অংশটি লাল রঙে দেখানো হয়। যেমন: নিউজ'],
            ['key' => 'site_tagline',   'label' => 'ট্যাগলাইন',        'type' => 'text'],
            ['key' => 'site_domain',    'label' => 'ডোমেইন',           'type' => 'text',   'hint' => 'হেডারে ও ছবির প্লেসহোল্ডারে দেখানো হয়'],
            ['key' => 'site_logo',      'label' => 'লোগো',             'type' => 'image'],
            ['key' => 'site_favicon',   'label' => 'ফেভিকন',            'type' => 'image'],
            ['key' => 'site_location',  'label' => 'অবস্থান',           'type' => 'text',   'hint' => 'তারিখ-স্ট্রিপে দেখানো হয়। যেমন: মহালছড়ি, খাগড়াছড়ি'],
            ['key' => 'timezone',       'label' => 'টাইমজোন',          'type' => 'text',   'hint' => 'যেমন: Asia/Dhaka'],
        ],
        'contact' => [
            ['key' => 'site_email',     'label' => 'ইমেইল',            'type' => 'text'],
            ['key' => 'site_phone',     'label' => 'ফোন',              'type' => 'text'],
            ['key' => 'site_address',   'label' => 'ঠিকানা',           'type' => 'textarea'],
            ['key' => 'newsroom_email', 'label' => 'নিউজরুম ইমেইল',     'type' => 'text'],
        ],
        'social' => [
            ['key' => 'social_facebook','label' => 'ফেসবুক URL',        'type' => 'text', 'hint' => 'নিচের তালিকায় লিংক যোগ করলে এই ঘরগুলো আর ব্যবহার হবে না'],
            ['key' => 'social_youtube', 'label' => 'ইউটিউব URL',        'type' => 'text'],
            ['key' => 'social_twitter', 'label' => 'টুইটার / X URL',    'type' => 'text'],
            ['key' => 'social_instagram','label'=> 'ইনস্টাগ্রাম URL',   'type' => 'text'],
            ['key' => 'social_whatsapp','label' => 'হোয়াটসঅ্যাপ নম্বর',  'type' => 'text'],
            ['key' => 'social_linkedin','label' => 'লিংকডইন URL',       'type' => 'text'],
            ['key' => 'social_telegram','label' => 'টেলিগ্রাম URL',      'type' => 'text'],
            ['key' => 'social_tiktok',  'label' => 'টিকটক URL',         'type' => 'text'],
        ],
        'header' => [
            ['key' => 'header_sticky',            'label' => 'স্ক্রল করলে হেডার আটকে থাকবে',        'type' => 'bool'],
            ['key' => 'header_show_date',         'label' => 'হেডারে তারিখ দেখান',                   'type' => 'bool'],
            ['key' => 'header_show_clock',        'label' => 'হেডারে লাইভ সময় ("আপডেট") দেখান',     'type' => 'bool'],
            ['key' => 'header_show_theme_toggle', 'label' => 'ডার্ক/লাইট মোড বোতাম দেখান',           'type' => 'bool'],
            ['key' => 'header_show_search',       'label' => 'সার্চ বোতাম দেখান',                     'type' => 'bool'],
            ['key' => 'date_strip_enabled',       'label' => 'তারিখ / অবস্থান / সময়ের স্ট্রিপ দেখান', 'type' => 'bool'],
            ['key' => 'breaking_label',           'label' => 'ব্রেকিং নিউজ লেবেল',                   'type' => 'text', 'hint' => 'ডিফল্ট: ব্রেকিং'],
            ['key' => 'header_more_label',        'label' => '"আরও" ড্রপডাউনের লেবেল',                'type' => 'text', 'hint' => 'খালি রাখলে ড্রপডাউন লুকানো থাকবে'],
            ['key' => 'header_live_label',        'label' => 'হেডারের হাইলাইট বোতামের লেখা',          'type' => 'text', 'hint' => 'ডিফল্ট: লাইভ টিভি — খালি রাখলে বোতাম দেখাবে না'],
            ['key' => 'header_live_url',          'label' => 'হাইলাইট বোতামের লিংক',                  'type' => 'text', 'hint' => 'খালি রাখলে ভিডিও পেজে যাবে'],
        ],
        'footer' => [
            ['key' => 'footer_about',        'label' => 'ফুটার পরিচিতি',          'type' => 'textarea'],
            ['key' => 'footer_text',         'label' => 'ফুটার টেক্সট',            'type' => 'textarea'],
            ['key' => 'copyright_text',      'label' => 'কপিরাইট',               'type' => 'text'],
            ['key' => 'footer_show_about',   'label' => '"পরিচিতি" কলাম দেখান',    'type' => 'bool'],
            ['key' => 'footer_show_categories','label'=>'"বিভাগসমূহ" কলাম দেখান',  'type' => 'bool'],
            ['key' => 'footer_show_links',   'label' => '"গুরুত্বপূর্ণ লিংক" কলাম দেখান', 'type' => 'bool'],
            ['key' => 'footer_show_contact', 'label' => '"যোগাযোগ" কলাম দেখান',     'type' => 'bool'],
            ['key' => 'footer_show_social',  'label' => 'সোশ্যাল আইকন দেখান',       'type' => 'bool'],
            ['key' => 'footer_heading_links','label' => 'লিংক কলামের শিরোনাম',     'type' => 'text', 'hint' => 'ডিফল্ট: গুরুত্বপূর্ণ লিংক — লিংকগুলো মেনু ম্যানেজার → ফুটার থেকে আসে'],
        ],
        'behavior' => [
            ['key' => 'posts_per_page',      'label' => 'প্রতি পেজে সংবাদ', 'type' => 'number', 'hint' => 'সর্বশেষ / ক্যাটাগরি / ট্যাগ তালিকায় প্রযোজ্য (৪–৬০)'],
            ['key' => 'comments_auto_approve','label'=> 'মন্তব্য অটো-অনুমোদন', 'type' => 'bool'],
            ['key' => 'comments_enabled',    'label' => 'মন্তব্য চালু',       'type' => 'bool'],
            ['key' => 'site_maintenance',    'label' => 'মেইনটেন্যান্স মোড',   'type' => 'bool', 'hint' => 'চালু করলে শুধু অ্যাডমিন ওয়েবসাইট দেখতে পাবেন, বাকি সবাই মেইনটেন্যান্স পেজ দেখবেন'],
            ['key' => 'maintenance_message', 'label' => 'মেইনটেন্যান্স বার্তা', 'type' => 'textarea'],
        ],
        'texts' => [
            ['key' => 'search_placeholder',   'label' => 'সার্চ ঘরের লেখা',        'type' => 'text', 'hint' => 'ডিফল্ট: সংবাদ খুঁজুন…'],
            ['key' => 'search_button_label',  'label' => 'সার্চ বোতামের লেখা',     'type' => 'text', 'hint' => 'ডিফল্ট: খুঁজুন'],
            ['key' => 'search_popular_label', 'label' => 'সার্চের নিচের লেবেল',    'type' => 'text', 'hint' => 'ডিফল্ট: জনপ্রিয় বিভাগ:'],
            ['key' => 'home_highlight_title', 'label' => 'হোমের "সর্বশেষ সংবাদ" শিরোনাম', 'type' => 'text'],
        ],
        'media' => [
            ['key' => 'imgbb_enabled', 'label' => 'ImgBB আপলোড চালু (ছবি ImgBB তে যাবে)', 'type' => 'bool'],
            ['key' => 'imgbb_api_key', 'label' => 'ImgBB API Key', 'type' => 'text', 'hint' => 'আপনার API Key: 4bfac8cf6fa4714236c08292299d2862 - https://api.imgbb.com/ থেকে নেওয়া'],
            ['key' => 'imgbb_expiration', 'label' => 'ImgBB Expiration (সেকেন্ড, 0 = never)', 'type' => 'text', 'hint' => '0 রাখলে ছবি কখনো ডিলিট হবে না'],
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
            'socialLinks' => mc_social_links(),
        ]);
    }

    public function update(Request $request)
    {
        $rules = [];

        foreach (self::FIELDS as $group => $fields) {
            foreach ($fields as $field) {
                $key = $field['key'];

                $rules[$key] = match ($field['type']) {
                    'bool'     => ['nullable', 'boolean'],
                    'image'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,ico', 'max:2048'],
                    'textarea' => ['nullable', 'string', 'max:8000'],
                    'number'   => ['nullable', 'integer', 'min:0', 'max:100000'],
                    default    => ['nullable', 'string', 'max:600'],
                };

                if ($field['type'] === 'image') {
                    // "অথবা সরাসরি URL" ঘরটিও গ্রহণ করা হয়
                    $rules[$key.'_url'] = ['nullable', 'string', 'max:600'];
                }
            }
        }

        // সোশ্যাল মিডিয়া রিপিটার (label/icon/url/color)
        $rules['social'] = ['nullable', 'array'];
        $rules['social.label'] = ['sometimes', 'array'];
        $rules['social.label.*'] = ['nullable', 'string', 'max:60'];
        $rules['social.icon'] = ['sometimes', 'array'];
        $rules['social.icon.*'] = ['nullable', 'string', 'max:60'];
        $rules['social.url'] = ['sometimes', 'array'];
        $rules['social.url.*'] = ['nullable', 'string', 'max:600'];
        $rules['social.color'] = ['sometimes', 'array'];
        $rules['social.color.*'] = ['nullable', 'string', 'max:9'];

        $validated = $request->validate($rules);

        foreach (self::FIELDS as $group => $fields) {
            foreach ($fields as $field) {
                $key  = $field['key'];
                $type = $field['type'];

                if ($type === 'image') {
                    // ১) ফাইল আপলোড ২) না হলে URL ঘর ৩) দুটোই না হলে আগের মান রাখা
                    if ($request->hasFile($key)) {
                        Setting::put($key, $this->uploader->store($request->file($key), 'settings')->path, 'image', $group);
                    } elseif (trim((string) ($validated[$key.'_url'] ?? '')) !== '') {
                        Setting::put($key, trim((string) $validated[$key.'_url']), 'image', $group);
                    }
                    continue;
                }

                if ($type === 'bool') {
                    // অন/অফ ফিল্ড সাবমিটই হয়নি (যেমন গ্রুপ পেন লুকানো) → ডিফল্ট বজায় রাখা
                    if (! $request->has($key)) {
                        continue;
                    }

                    Setting::put($key, $request->boolean($key) ? '1' : '0', 'bool', $group);
                    continue;
                }

                $value = $validated[$key] ?? '';
                Setting::put($key, $type === 'number' ? (string) (int) $value : $value, $type, $group);
            }
        }

        $this->saveSocialLinks($request);

        Setting::flushCache();
        Cache::flush();

        ActivityLogger::log('updated', 'settings', 'সাইট সেটিংস হালনাগাদ করা হয়েছে');

        return back()->with('success', 'সাইট সেটিংস সংরক্ষিত হয়েছে।');
    }

    /** সোশ্যাল রিপিটারের সারিগুলো JSON আকারে সংরক্ষণ (খালি URL বাদে) */
    private function saveSocialLinks(Request $request): void
    {
        if (! $request->has('social')) {
            return;
        }

        $rows   = $request->input('social', []);
        $links  = [];

        foreach (array_keys((array) ($rows['url'] ?? [])) as $index) {
            $url = trim((string) ($rows['url'][$index] ?? ''));

            if ($url === '') {
                continue;
            }

            $color = (string) ($rows['color'][$index] ?? '');

            $links[] = [
                'label' => trim((string) ($rows['label'][$index] ?? '')) ?: $url,
                'icon'  => trim((string) ($rows['icon'][$index] ?? '')) ?: 'ph-globe-simple',
                'url'   => $url,
                'color' => preg_match('/^#[0-9A-Fa-f]{6}$/', $color) ? $color : null,
            ];
        }

        Setting::put('social_links', json_encode($links, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'json', 'social');
    }
}
