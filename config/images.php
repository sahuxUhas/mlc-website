<?php

/**
 * ==========================================================================
 * Image System Configuration — দৈনিক মহালছড়ি নিউজ
 * ==========================================================================
 * সব ছবি (News featured / gallery / editor image / media library) এখানকার
 * কনফিগ অনুযায়ী Image Hosting Provider-এ আপলোড হয় এবং DB-তে শুধু URL/ID
 * রেফারেন্স সংরক্ষিত হয় (কখনো BLOB নয়)।
 *
 * নিরাপত্তা (গুরুত্বপূর্ণ):
 *  - API Key শুধু .env (server-side) তে থাকে। config/ কোডে কোনো চাবি লেখা হয় না।
 *  - ফ্রন্টএন্ড/JS/Blade-এ API Key কখনো pass করা হয় না (MediaController/Setting UI থেকেও সরানো হয়েছে)।
 *  - raw provider URL কখনো UI-তে দেখানো বা src হিসেবে ব্যবহার করা হয় না —
 *    `mc_image()` সব remote ছবিকে নিজের ডোমেইনের signed proxy URL-এ রূপান্তর করে।
 *
 * ভবিষ্যতে Image System পরিবর্তন করতে চাইলে শুধু:
 *  'provider' => 'imgbb' | 'local'  বদলান, এবং প্রয়োজনে নতুন driver যুক্ত করুন।
 */

return [

    /*
    |---------------------------------------------------------------------------
    | Active provider — 'imgbb' (remote hosting) অথবা 'local' (public/uploads)
    |---------------------------------------------------------------------------
    */
    'provider' => env('IMAGE_PROVIDER', 'imgbb'),

    /*
    |---------------------------------------------------------------------------
    | আপলোড সীমা ও অনুমোদিত ধরন (নিরাপত্তা)
    |---------------------------------------------------------------------------
    */
    'max_kb'          => (int) env('MC_UPLOAD_MAX_KB', 4096),
    'max_files'       => (int) env('MC_UPLOAD_MAX_FILES', 20),
    'allowed_mimes'   => ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'],
    'news_mimes'      => ['jpg', 'jpeg', 'png', 'webp'],

    /*
    |---------------------------------------------------------------------------
    | Provider চালু থাকলেও ব্যর্থ হলে লোকাল ডিস্কে ফলব্যাক করা হবে কি?
    | (টেস্টিং/লোকাল ডেভে true — ImgBB down হলে ছবি হারায় না)
    |---------------------------------------------------------------------------
    */
    'local_fallback'  => env('IMAGE_LOCAL_FALLBACK', true),

    /* লোকাল ফলব্যাক কোন ডিস্কে যাবে (config/filesystems.php) */
    'local_disk'      => env('IMAGE_LOCAL_DISK', 'uploads'),

    /*
    |---------------------------------------------------------------------------
    | Providers — প্রতিটি driver এর কনফিগ (সব মান .env থেকে)
    |---------------------------------------------------------------------------
    */
    'providers' => [
        'imgbb' => [
            'key'        => env('IMGBB_API_KEY'),
            'enabled'    => env('IMGBB_ENABLED', true),
            'endpoint'   => env('IMGBB_ENDPOINT', 'https://api.imgbb.com/1/upload'),
            'expiration' => (int) env('IMGBB_EXPIRATION', 0),   // 0 = কখনো expire হবে না
            'timeout'    => (int) env('IMGBB_TIMEOUT', 30),
        ],
    ],

    /*
    |---------------------------------------------------------------------------
    | Remote URL Proxy — ব্রাউজারে raw hosting URL প্রকাশ না করার ব্যবস্থা
    |---------------------------------------------------------------------------
    | enabled = true হলে mc_image() প্রতিটি remote ছবির জন্য নিজের ডোমেইনের
    | signed URL (/img/xxxx?s=signature) তৈরি করে; raw URL কোথাও দেখা যায় না।
    */
    'proxy' => [
        'enabled'     => env('IMAGE_PROXY_ENABLED', true),
        'ttl_minutes' => (int) env('IMAGE_PROXY_TTL', 10080),        // ডিফল্ট ৭ দিন
        'max_kb'      => (int) env('IMAGE_PROXY_MAX_KB', 12288),     // proxy করা সর্বোচ্চ সাইজ
        'timeout'     => (int) env('IMAGE_PROXY_TIMEOUT', 20),

        /*
        | যে হোস্টগুলো proxy করা যাবে (SSRF প্রতিরোধে allowlist)।
        | list এ না থাকা পুরোনো/বাহ্যিক ছবির URL আগের মতোই ব্যবহার হয় (কিছুই ভাঙে না)।
        | সব http(s) হোস্ট proxy করতে চাইলে IMAGE_PROXY_ALL=true করুন।
        */
        'hosts'       => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('IMAGE_PROXY_HOSTS', 'i.ibb.co,ibb.co,imgbb.com,i.ibb.co.com'))
        ))),
        'all_hosts'   => env('IMAGE_PROXY_ALL', false),
    ],
];
