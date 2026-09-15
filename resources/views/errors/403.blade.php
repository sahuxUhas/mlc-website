@include('errors._layout', [
    'code'    => '403',
    'title'   => 'অনুমতি নেই',
    'icon'    => 'ph-lock-key',
    'message' => 'দুঃখিত, এই পেজ বা কাজটি করার অনুমতি আপনার নেই। প্রয়োজন হলে অ্যাডমিনের সাথে যোগাযোগ করুন।',
    'suggestions' => [
        'সর্বশেষ সংবাদ'  => route('latest'),
        'ভিডিও গ্যালারি' => route('videos.index'),
        'সার্চ করুন'     => route('search'),
    ],
])
