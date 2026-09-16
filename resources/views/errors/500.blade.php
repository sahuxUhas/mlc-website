@include('errors._layout', [
    'code'    => '500',
    'title'   => 'সার্ভারে সমস্যা',
    'icon'    => 'ph-warning-octagon',
    'message' => 'দুঃখিত, আমাদের সার্ভারে একটি অপ্রত্যাশিত সমস্যা হয়েছে। টিম ইতিমধ্যে অবগত হয়েছে — কিছুক্ষণ পর আবার চেষ্টা করুন।',
    'suggestions' => [
        'সর্বশেষ সংবাদ'  => route('latest'),
        'ভিডিও গ্যালারি' => route('videos.index'),
        'সার্চ করুন'     => route('search'),
    ],
])
