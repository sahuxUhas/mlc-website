<?php

return [
    'postmark' => ['token' => env('POSTMARK_TOKEN')],
    'ses' => ['key' => env('AWS_ACCESS_KEY_ID'), 'secret' => env('AWS_SECRET_ACCESS_KEY'), 'region' => env('AWS_DEFAULT_REGION', 'us-east-1')],
    'slack' => ['notifications' => ['bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'), 'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL')]],

    /*
    |--------------------------------------------------------------------------
    | ImgBB — শুধুমাত্র .env থেকে পড়া হয় (কোডে কোনো API Key লেখা থাকবে না)
    |--------------------------------------------------------------------------
    | বিস্তারিত কনফিগ দেখুন: config/images.php
    | API Key কখনো Frontend/Blade/JS-এ পাঠানো হয় না।
    */
    'imgbb' => [
        'key' => env('IMGBB_API_KEY'),
        'enabled' => env('IMGBB_ENABLED', true),
    ],
];
