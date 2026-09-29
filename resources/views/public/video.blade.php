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

    <div class="mc-video-feed" data-mc-video-feed>
        <article class="mc-video-item">
            @include('partials.video-player', ['video' => $video])
            <h1 class="mc-video-title">{{ $video->title }}</h1>
            @if($video->description)
                <div class="mc-video-desc is-full">{!! nl2br(e($video->description)) !!}</div>
            @endif
            <p class="mc-video-meta">
                <span><i class="ph ph-calendar-blank"></i> {{ bn_date($video->published_at, false) }}</span>
                <span><i class="ph ph-eye"></i> <span data-mc-views="{{ $video->slug }}">{{ bn_count((int) $video->views) }}</span> বার দেখা হয়েছে</span>
            </p>
        </article>
    </div>

    @if($more->isNotEmpty())
        <aside class="mc-video-more">
            <h2 class="mb-3 flex items-center gap-2 border-b-2 border-[#D50E18] pb-2 font-serif text-base font-bold text-gray-900 dark:text-[#F1F5F9]">
                <span class="h-5 w-1.5 rounded-sm bg-[#E21D2B]"></span> আরও ভিডিও
            </h2>
            <ul class="space-y-2">
                @foreach($more as $item)
                    <li>
                        <a href="{{ route('videos.show', $item->slug) }}" class="block font-serif text-sm font-bold leading-snug text-gray-800 hover:text-[#E21D2B] dark:text-[#F1F5F9] dark:hover:text-[#22C55E]">{{ $item->title }}</a>
                    </li>
                @endforeach
            </ul>
        </aside>
    @endif
</div>
@endsection
