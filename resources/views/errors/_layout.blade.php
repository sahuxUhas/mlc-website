{{-- এরর পেজ লেআউট — ডাটাবেস/সেটিংস ছাড়াই চলে (DB ডাউন থাকলেও রেন্ডার হবে) --}}
@php
    $code    = $code ?? 500;
    $title   = $title ?? 'কিছু একটা ভুল হয়েছে';
    $message = $message ?? 'দুঃখিত, আপনার অনুরোধটি প্রক্রিয়া করা যায়নি।';
    $icon    = $icon ?? 'ph-warning-circle';
    $siteName = config('app.name', 'দৈনিক মহালছড়ি নিউজ');
@endphp
<!DOCTYPE html>
<html lang="bn" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $code }} — {{ $title }} | {{ $siteName }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body{font-family:'Noto Sans Bengali','Inter',system-ui,sans-serif;margin:0;background:#EAF7EC;color:#0B0B0B;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
        .mc-err-card{max-width:32rem;width:100%;background:#fff;border:1px solid rgba(11,11,11,.08);border-radius:24px;padding:36px 28px;text-align:center;box-shadow:0 18px 45px rgba(31,122,61,.13)}
        .mc-err-icon{width:72px;height:72px;margin:0 auto 18px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:34px;background:#FDE8EA;color:#D50E18}
        .mc-err-code{font-family:'Inter',sans-serif;font-size:64px;line-height:1;font-weight:800;color:#E21D2B;letter-spacing:-2px}
        .mc-err-title{font-family:'Noto Serif Bengali',serif;font-size:20px;font-weight:700;margin:6px 0 10px}
        .mc-err-msg{font-size:14px;line-height:1.8;color:#475569;margin:0 auto;max-width:26rem}
        .mc-err-actions{display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin-top:26px}
        .mc-err-btn{display:inline-flex;align-items:center;gap:7px;padding:11px 20px;border-radius:12px;font-size:13px;font-weight:700;text-decoration:none;border:1px solid transparent;transition:.2s}
        .mc-err-btn-primary{background:#D50E18;color:#fff}.mc-err-btn-primary:hover{background:#B9121E}
        .mc-err-btn-ghost{background:#fff;color:#0B0B0B;border-color:#C5E7C8}.mc-err-btn-ghost:hover{border-color:#1F7A3D;color:#1F7A3D}
        .mc-err-foot{margin-top:24px;padding-top:18px;border-top:1px solid #EAF7EC;font-size:11px;color:#94A3B8}
        .mc-err-foot a{color:#1F7A3D;text-decoration:none;font-weight:700}.mc-err-foot a:hover{text-decoration:underline}
        .mc-err-sugg{margin-top:22px;text-align:left;background:#F7FBF8;border:1px solid #C5E7C8;border-radius:14px;padding:14px 16px}
        .mc-err-sugg p{font-size:11px;font-weight:700;color:#1F7A3D;margin:0 0 8px}
        .mc-err-sugg a{display:block;font-size:12px;color:#0B0B0B;text-decoration:none;padding:5px 0;border-bottom:1px dashed #C5E7C8}
        .mc-err-sugg a:last-child{border-bottom:0}.mc-err-sugg a:hover{color:#D50E18}
        @media (prefers-color-scheme:dark){body{background:#0D1422;color:#F1F5F9}.mc-err-card{background:#182233;border-color:#263246}.mc-err-title{color:#F1F5F9}.mc-err-msg{color:#94A3B8}.mc-err-btn-ghost{background:#0D1422;color:#F1F5F9;border-color:#263246}.mc-err-foot{border-color:#263246}.mc-err-sugg{background:#0D1422;border-color:#263246}.mc-err-sugg a{color:#F1F5F9}}
    </style>
</head>
<body>
    <div class="mc-err-card">
        {{-- $icon শুধু ভিউ থেকে আসে (কোনো ব্যবহারকারীর ইনপুট নয়) — তাই কাঁচা আউটপুট নিরাপদ --}}
        <div class="mc-err-icon">{!! $icon !!}</div>
        <div class="mc-err-code">{{ $code }}</div>
        <h1 class="mc-err-title">{{ $title }}</h1>
        <p class="mc-err-msg">{{ $message }}</p>

        @isset($suggestions)
            <div class="mc-err-sugg">
                <p>আপনি যেতে পারেন:</p>
                @foreach($suggestions as $label => $url)
                    <a href="{{ $url }}">{{ $label }}</a>
                @endforeach
            </div>
        @endisset

        <div class="mc-err-actions">
            <a href="{{ url('/') }}" class="mc-err-btn mc-err-btn-primary">← হোমপেজে ফিরুন</a>
            <a href="javascript:history.back()" class="mc-err-btn mc-err-btn-ghost">আগের পেজে যান</a>
        </div>

        <div class="mc-err-foot">
            {{ $siteName }} · <a href="{{ url('/contact') }}">যোগাযোগ করুন</a>
            @if(!app()->environment('production') && isset($exception) && $exception)
                <br><span title="{{ $exception->getMessage() }}">{{ \Illuminate\Support\Str::limit($exception->getMessage(), 70) }}</span>
            @endif
        </div>
    </div>
</body>
</html>
