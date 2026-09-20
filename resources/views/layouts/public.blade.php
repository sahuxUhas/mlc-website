<!DOCTYPE html>
<html lang="bn" class="{{ isset($_COOKIE['mc_theme']) && $_COOKIE['mc_theme'] === 'dark' ? 'dark' : 'light' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ===== SEO (অ্যাডমিন প্যানেল থেকে নিয়ন্ত্রিত) ===== --}}
    <title>@yield('title', site_setting('seo_title', site_setting('site_name', config('app.name')).' | '.site_setting('site_tagline', 'পাহাড়ের কথা বলে')))</title>
    <meta name="description" content="@yield('meta_description', site_setting('seo_description', 'দৈনিক মহালছড়ি নিউজ — খাগড়াছড়ি জেলার মহালছড়ি উপজেলা, পার্বত্য চট্টগ্রাম ও সারাদেশের সর্বশেষ সংবাদ।'))">
    <meta name="keywords" content="@yield('meta_keywords', site_setting('seo_keywords', 'মহালছড়ি, খাগড়াছড়ি, পার্বত্য চট্টগ্রাম, সংবাদ'))">
    <meta name="robots" content="@yield('robots', site_setting('seo_robots', 'index, follow'))">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta name="theme-color" content="#D50E18">

    {{-- Open Graph --}}
    <meta property="og:site_name" content="{{ site_setting('site_name', config('app.name')) }}">
    <meta property="og:title" content="@yield('og_title', $__env->yieldContent('title'))">
    <meta property="og:description" content="@yield('og_description', $__env->yieldContent('meta_description'))">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta property="og:image" content="@yield('og_image', site_setting('seo_og_image') ? mc_image(site_setting('seo_og_image')) : (site_setting('site_logo') ? mc_image(site_setting('site_logo')) : ''))">
    <meta property="og:locale" content="bn_BD">
    <meta name="twitter:card" content="summary_large_image">

    @if(site_setting('seo_verification_google'))
        <meta name="google-site-verification" content="{{ site_setting('seo_verification_google') }}">
    @endif
    @if(site_setting('seo_verification_fb'))
        <meta name="facebook-domain-verification" content="{{ site_setting('seo_verification_fb') }}">
    @endif

    <link rel="icon" type="image/png" href="{{ site_setting('site_favicon') ? mc_image(site_setting('site_favicon')) : (site_setting('site_logo') ? mc_image(site_setting('site_logo')) : mc_placeholder_svg()) }}">
    <link rel="alternate" type="application/rss+xml" title="{{ site_setting('site_name') }} RSS" href="{{ route('feed') }}">

    {{-- Fonts (ডেমোর সাথে হুবহু এক) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+Bengali:wght@400;500;600;700&family=Noto+Serif+Bengali:wght@500;600;700;800&display=swap" rel="stylesheet">

    {{-- Phosphor Icons --}}
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    {{-- কম্পাইল করা Tailwind CSS (কোনো Node.js বিল্ড ছাড়াই cPanel এ চলবে) --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">

    @stack('head')
    {!! site_setting('custom_head_html') !!}
    {!! site_setting('analytics_code') !!}
</head>
<body class="bg-gray-50 text-gray-900 dark:bg-[#0D1422] dark:text-[#F1F5F9] transition-colors duration-200 mc-body-pad">
    {!! site_setting('custom_body_html') !!}

    @include('partials.header')

    {{-- ব্রেকিং নিউজ বার --}}
    @include('partials.breaking-bar')

    {{-- তারিখ / অবস্থান / লাইভ সময় --}}
    @include('partials.date-strip')

    <main class="min-h-[60vh]">
        @if(session('success'))
            <div class="container mx-auto mt-4 max-w-6xl px-3 sm:px-4">
                <div class="animate-fade-in-up rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800 dark:border-green-900 dark:bg-green-950/40 dark:text-green-300">
                    <i class="ph-fill ph-check-circle mr-1"></i>{{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="container mx-auto mt-4 max-w-6xl px-3 sm:px-4">
                <div class="animate-fade-in-up rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
                    <i class="ph-fill ph-warning-circle mr-1"></i>{{ session('error') }}
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.mobile-nav')
    @include('partials.search-overlay')

    <script src="{{ asset('js/site.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
