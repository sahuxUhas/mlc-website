@include('errors._layout', [
    'code'    => '429',
    'title'   => 'অনেক বেশি অনুরোধ',
    'icon'    => 'ph-hourglass-medium',
    'message' => 'নিরাপত্তার জন্য সাময়িকভাবে অনুরোধ সীমিত করা হয়েছে। কয়েক মিনিট পর আবার চেষ্টা করুন।',
    'suggestions' => [
        'সর্বশেষ সংবাদ'  => route('latest'),
        'ভিডিও গ্যালারি' => route('videos.index'),
        'সার্চ করুন'     => route('search'),
    ],
])
