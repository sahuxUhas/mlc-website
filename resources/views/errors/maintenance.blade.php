{{--
    মেইনটেন্যান্স পেজ — অ্যাডমিন প্যানেল → সাইট সেটিংস → আচরণ → "মেইনটেন্যান্স মোড"
    বার্তাটিও সেখান থেকেই বদলানো যায় (maintenance_message)।
--}}
@php
    $defaultMessage = 'আমরা এই মুহূর্তে ওয়েবসাইটের রক্ষণাবেক্ষণ চালাচ্ছি। খুব শীঘ্রই ফিরছি — একটু অপেক্ষা করুন।';
    $message = \App\Models\Setting::get('maintenance_message') ?: $defaultMessage;
@endphp
@include('errors._layout', [
    'code'    => 503,
    'title'   => 'সাময়িকভাবে বন্ধ',
    // errors._layout এ Phosphor আইকন লোড হয় না, তাই ইমোজি ব্যবহার করা হয়েছে
    'icon'    => '🛠️',
    'message' => $message,
])
