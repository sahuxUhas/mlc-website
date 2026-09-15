{{-- ===== মোবাইল বটম নেভিগেশন (মেনু ম্যানেজার থেকে নিয়ন্ত্রিত, ডিজাইন অক্ষুণ্ণ) ===== --}}
@php
    $bottomItems = ($menu_mobile_bottom ?? collect());
    $defaults = [
        ['label' => 'হোম',     'icon' => 'ph-house',                   'href' => route('home'),                'active' => request()->routeIs('home')],
        ['label' => 'সর্বশেষ', 'icon' => 'ph-clock-counter-clockwise', 'href' => route('latest'),              'active' => request()->routeIs('latest')],
        ['label' => 'ভিডিও',   'icon' => 'ph-play-circle',             'href' => route('videos.index'),        'active' => request()->routeIs('videos.*')],
        ['label' => 'ঘোষণা',   'icon' => 'ph-megaphone',               'href' => route('announcements.index'), 'active' => request()->routeIs('announcements.*')],
    ];
@endphp

<nav class="mc-bottomnav md:hidden" aria-label="মোবাইল নেভিগেশন">
    <ul>
        @if($bottomItems->isNotEmpty())
            @foreach($bottomItems as $item)
                <li>
                    <a href="{{ $item->href }}" class="{{ request()->fullUrlIs($item->href) ? 'is-active' : '' }}">
                        <i class="mc-bn-ico ph {{ $item->icon ?: 'ph-circle' }}"></i>
                        <span class="mc-bn-txt">{{ $item->label }}</span>
                    </a>
                </li>
            @endforeach
        @else
            @foreach($defaults as $item)
                <li>
                    <a href="{{ $item['href'] }}" class="{{ $item['active'] ? 'is-active' : '' }}">
                        <i class="mc-bn-ico ph {{ $item['icon'] }}"></i>
                        <span class="mc-bn-txt">{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach
        @endif
    </ul>
</nav>
