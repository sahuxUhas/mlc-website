@include('errors._layout', [
    'code'    => '419',
    'title'   => 'সেশনের মেয়াদ শেষ',
    'icon'    => 'ph-clock-countdown',
    'message' => 'আপনার সেশনের মেয়াদ শেষ হয়ে গেছে বা টোকেন মিলছে না। পেজটি রিফ্রেশ করে আবার চেষ্টা করুন।',
    'suggestions' => [
        'সর্বশেষ সংবাদ'  => route('latest'),
        'ভিডিও গ্যালারি' => route('videos.index'),
        'সার্চ করুন'     => route('search'),
    ],
])
