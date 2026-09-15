<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অনুরোধ সীমিত | {{ site_setting('site_name', config('app.name')) }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-sm">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-50">
            <span class="text-2xl">⏳</span>
        </div>
        <h1 class="font-serif text-2xl font-bold text-gray-900">৪২৯ — অনেক বেশি অনুরোধ</h1>
        <p class="mt-3 text-sm leading-relaxed text-gray-600">{{ $message ?? 'সাময়িকভাবে আবার চেষ্টা করুন।' }}</p>
        <a href="{{ route('home') }}" class="mt-6 inline-block rounded-lg bg-[#D50E18] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#B9121E]">হোমপেজে ফিরুন</a>
    </div>
</body>
</html>
