@extends('layouts.public')
@section('title', $video->meta_title ?: $video->title.' | '.site_setting('site_name'))
@section('meta_description', $video->meta_description ?: mc_excerpt($video->description, 240))
@section('content')
<div class="container mx-auto max-w-6xl px-3 py-6 sm:px-4 md:py-8">
    <nav class="mb-4 flex items-center gap-1.5 text-[11px] font-semibold text-gray-500 dark:text-[#94A3B8]">
        <a href="{{ route('home') }}" class="hover:text-[#D50E18]">হোম</a><i class="ph ph-caret-left text-[9px]"></i>
        <a href="{{ route('videos.index') }}" class="hover:text-[#D50E18]">ভিডিও</a><i class="ph ph-caret-left text-[9px]"></i>
        <span class="line-clamp-1 text-gray-800 dark:text-[#F1F5F9]">{{ $video->title }}</span>
    </nav>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
        <div class="lg:col-span-8">
            <div class="overflow-hidden rounded-2xl bg-black">
                <div class="relative aspect-video">
                    <iframe src="{{ $video->embedUrl() }}" class="absolute inset-0 h-full w-full" allowfullscreen allow="autoplay; encrypted-media; picture-in-picture" loading="lazy" title="{{ $video->title }}"></iframe>
                </div>
            </div>
            <h1 class="mt-4 font-serif text-xl font-bold leading-snug text-gray-900 dark:text-[#F1F5F9] sm:text-2xl">{{ $video->title }}</h1>
            <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-[#94A3B8]">
                <span><i class="ph ph-calendar-blank text-[#D50E18]"></i> {{ bn_date($video->published_at) }}</span>
                <span><i class="ph ph-eye text-[#D50E18]"></i> {{ bn_count($video->views) }} বার দেখা হয়েছে</span>
                @if($video->video_url)
                    <a href="{{ $video->video_url }}" target="_blank" rel="noopener" class="font-bold text-[#1F7A3D] hover:underline dark:text-[#22C55E]"><i class="ph ph-arrow-square-out"></i> মূল লিংক</a>
                @endif
            </div>
            @if($video->description)
                <div class="mt-4 rounded-xl border border-gray-200 bg-white p-4 text-sm leading-relaxed text-gray-700 dark:border-[#263246] dark:bg-[#182233] dark:text-gray-300">
                    {!! nl2br(e($video->description)) !!}
                </div>
            @endif
        </div>

        <aside class="lg:col-span-4">
            <h3 class="mb-3 flex items-center gap-2 border-b-2 border-[#D50E18] pb-2 font-serif text-base font-bold text-gray-900 dark:text-[#F1F5F9]">
                <span class="h-5 w-1.5 rounded-sm bg-[#E21D2B]"></span> আরও ভিডিও
            </h3>
            <ul class="space-y-3">
                @foreach($more as $item)
                    <li>
                        <a href="{{ route('videos.show', $item->slug) }}" class="group flex items-start gap-3">
                            <div class="relative h-14 w-20 shrink-0 overflow-hidden rounded-lg bg-gray-900">
                                @if($item->thumbnail)<img src="{{ mc_image($item->thumbnail) }}" alt="{{ $item->title }}" loading="lazy" class="h-full w-full object-cover">@endif
                                <span class="absolute inset-0 flex items-center justify-center"><i class="ph-fill ph-play text-lg text-white/90"></i></span>
                            </div>
                            <span class="line-clamp-2 text-[13px] font-bold leading-snug text-gray-800 group-hover:text-[#E21D2B] dark:text-[#F1F5F9] dark:group-hover:text-[#22C55E]">{{ $item->title }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </aside>
    </div>
</div>
@endsection
