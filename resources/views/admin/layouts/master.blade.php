<!DOCTYPE html>
<html lang="bn" class="h-full">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','ড্যাশবোর্ড') | অ্যাডমিন | {{ site_setting('site_name','দৈনিক মহালছড়ি নিউজ') }}</title>
    <link rel="icon" href="{{ site_setting('site_favicon') ? mc_image(site_setting('site_favicon')) : mc_placeholder_svg() }}">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700;800&family=Tiro+Bangla:ital@0;1&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="h-full bg-slate-50 font-sans text-slate-900 antialiased dark:bg-[#0B1120] dark:text-slate-100">
<div class="mc-admin-shell flex min-h-full" id="adminShell">
    @include('admin.partials.sidebar')
    <div class="mr-0 flex-1 md:mr-64">
        @include('admin.partials.topbar')
        <main class="p-4 sm:p-6">
            @if(session('success'))<div class="mc-toast mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300"><i class="ph-fill ph-check-circle"></i> {{ session('success') }}</div>@endif
            @if(session('error'))<div class="mc-toast mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300"><i class="ph-fill ph-warning-circle"></i> {{ session('error') }}</div>@endif
            @if($errors->any())<div class="mc-toast mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300"><ul class="list-inside list-disc space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </main>
    </div>
</div>
<script src="{{ asset('js/admin.js') }}" defer></script>
@stack('scripts')
</body>
</html>
