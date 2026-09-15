@include('errors._layout', [
    'code'    => '404',
    'title'   => 'পেজটি পাওয়া যায়নি',
    'icon'    => 'ph-magnifying-glass',
    'message' => 'আপনি যে পেজটি খুঁজছেন সেটি সরিয়ে ফেলা হয়েছে, ঠিকানা বদলেছে অথবা কখনো বিদ্যমান ছিল না।',
    'suggestions' => [
        'সর্বশেষ সংবাদ'  => route('latest'),
        'ভিডিও গ্যালারি' => route('videos.index'),
        'সার্চ করুন'     => route('search'),
    ],
])
