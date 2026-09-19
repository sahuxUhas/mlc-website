<!DOCTYPE html>
<html lang="bn" class="h-full">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাডমিন লগইন | {{ site_setting('site_name','দৈনিক মহালছড়ি নিউজ') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Tiro+Bangla&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
</head>
<body class="flex min-h-full items-center justify-center bg-slate-100 p-4 font-sans dark:bg-[#0B1120]">
<div class="w-full max-w-md">
    <div class="mb-6 text-center">
        <div class="mx-auto mb-3 inline-flex h-16 w-16 items-center justify-center overflow-hidden rounded-2xl bg-[#C5E7C8] ring-1 ring-black/10">
            <img src="{{ site_setting('site_logo') ? mc_image(site_setting('site_logo')) : mc_placeholder_svg() }}" alt="" class="h-full w-full object-cover">
        </div>
        <h1 class="font-serif text-2xl font-black text-slate-900 dark:text-white">{{ site_setting('site_prefix','দৈনিক') }} <span class="text-[#E21D2B]">{{ site_setting('site_name','মহালছড়ি নিউজ') }}</span></h1>
        <p class="mt-1 text-xs font-semibold text-[#1F7A3D] dark:text-[#22C55E]">{{ site_setting('site_tagline','পাহাড়ের কথা বলে') }}</p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-[#0F172A] sm:p-8">
        <h2 class="mb-1 font-serif text-lg font-bold text-slate-900 dark:text-white">অ্যাডমিন প্যানেল</h2>
        <p class="mb-5 text-xs text-slate-500 dark:text-slate-400">প্রবেশের জন্য আপনার ইমেইল ও পাসওয়ার্ড দিন।</p>

        @if(session('success'))<div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300"><ul class="list-inside list-disc space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

        <form action="{{ route('admin.login.attempt') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="mb-1 block text-xs font-bold text-slate-700 dark:text-slate-300">ইমেইল</label>
                <div class="relative">
                    <i class="ph ph-envelope-simple pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" autofocus
                           class="w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-3 pr-10 text-sm outline-none focus:border-[#E21D2B] focus:ring-2 focus:ring-[#E21D2B]/25 dark:border-slate-700 dark:bg-[#0B1120] dark:text-white">
                </div>
            </div>
            <div>
                <label for="password" class="mb-1 block text-xs font-bold text-slate-700 dark:text-slate-300">পাসওয়ার্ড</label>
                <div class="relative">
                    <i class="ph ph-lock-key pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                           class="w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-3 pr-10 text-sm outline-none focus:border-[#E21D2B] focus:ring-2 focus:ring-[#E21D2B]/25 dark:border-slate-700 dark:bg-[#0B1120] dark:text-white">
                </div>
            </div>
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300">
                    <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-[#E21D2B] focus:ring-[#E21D2B]"> মনে রাখুন
                </label>
                <a href="{{ route('home') }}" class="text-xs font-bold text-[#E21D2B] hover:underline">← ওয়েবসাইটে ফিরুন</a>
            </div>
            <button type="submit" class="w-full rounded-lg bg-[#E21D2B] py-2.5 text-sm font-bold text-white transition-colors hover:bg-[#B9121E]">
                <i class="ph ph-sign-in"></i> লগইন করুন
            </button>
        </form>
        <p class="mt-4 text-center text-[11px] leading-relaxed text-slate-400">
            <i class="ph ph-shield-check"></i> এই লগইনে CSRF সুরক্ষা, রেট লিমিটিং ও সিকিউর পাসওয়ার্ড হ্যাশিং রয়েছে।
        </p>
    </div>
</div>
</body>
</html>
