{{-- ===== বিজ্ঞাপন স্লট (অ্যাডমিন → বিজ্ঞাপন ম্যানেজার থেকে নিয়ন্ত্রিত) ===== --}}
@php
    $ad = mc_ad($position ?? 'homepage');
    $slotClass = match($position ?? '') {
        'top_header' => 'flex justify-center overflow-hidden mb-6 md:mb-8',
        'sidebar'    => 'mb-5',
        default      => 'my-6 flex justify-center overflow-hidden',
    };
@endphp

@if($ad)
    <div class="{{ $slotClass }}" data-ad-position="{{ $position }}">
        @if($ad->type === 'html' && $ad->html_code)
            <div class="w-full overflow-hidden text-center">{!! $ad->html_code !!}</div>
        @else
            @php $tag = '<img src="'.e(mc_image($ad->image)).'" alt="'.e($ad->title).'" class="mx-auto max-w-full h-auto rounded" loading="lazy" decoding="async">'; @endphp
            @if($ad->link)
                <a href="{{ route('ad.click', $ad) }}" target="{{ $ad->link_target }}" rel="noopener sponsored nofollow" title="{{ $ad->title }}">{!! $tag !!}</a>
            @else
                {!! $tag !!}
            @endif
        @endif
    </div>
@elseif(($position ?? '') === 'top_header')
    {{-- বিজ্ঞাপন না থাকলে ডেমোর মতো প্লেসহোল্ডার (728x90) --}}
    <div class="{{ $slotClass }}">
        <div class="flex h-[90px] w-full max-w-[728px] items-center justify-center rounded border border-gray-300 bg-gray-200 text-xs font-medium uppercase tracking-widest text-gray-400 dark:border-[#263246] dark:bg-[#182233] dark:text-gray-600">
            Advertisement ({{ $position }})
        </div>
    </div>
@endif
