@include('public.listing', [
    'title'    => 'সর্বশেষ সংবাদ',
    'subtitle' => 'মোট '.bn_count($posts->total()).'টি সংবাদ প্রকাশিত হয়েছে',
    'posts'    => $posts,
    'canonical'=> route('latest'),
    'description' => 'দৈনিক মহালছড়ি নিউজের সর্বশেষ প্রকাশিত সংবাদের তালিকা।',
])
