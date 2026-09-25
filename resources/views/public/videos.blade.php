@extends('layouts.public')
@section('title', 'ভিডিও | '.site_setting('site_name', config('app.name')))
@section('content')
<div class="container mx-auto max-w-6xl px-3 py-6 sm:px-4 md:py-8">
    @ad('top_header')
    <div class="mb-6 flex items-center gap-2.5 border-b-2 border-[#D50E18] pb-3">
        <span class="h-6 w-2 rounded-sm bg-[#E21D2B]"></span>
        <h1 class="font-serif text-xl font-bold text-gray-900 dark:text-[#F1F5F9] sm:text-2xl">ভিডিও</h1>
        <span class="mr-auto text-xs text-gray-500 dark:text-[#94A3B8]">{{ bn_count($videos->total()) }}টি ভিডিও</span>
    </div>

    <div class="mc-video-feed" data-mc-video-feed>
        @forelse($videos as $video)
            <article class="mc-video-item" id="video-{{ $video->id }}">
                @include('partials.video-player', ['video' => $video])
                <h2 class="mc-video-title">{{ $video->title }}</h2>
                @if($video->description)
                    <p class="mc-video-desc">{{ mc_excerpt($video->description, 220) }}</p>
                @endif
                <p class="mc-video-meta">
                    <span><i class="ph ph-calendar-blank"></i> {{ bn_date($video->published_at, false) }}</span>
                    <span><i class="ph ph-eye"></i> <span data-mc-views="{{ $video->slug }}">{{ bn_count((int) $video->views) }}</span> বার দেখা হয়েছে</span>
                </p>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center dark:border-[#263246] dark:bg-[#182233]">
                <i class="ph ph-video-camera text-4xl text-gray-300"></i>
                <p class="mt-3 font-serif font-bold text-gray-700 dark:text-gray-300">এখনও কোনো ভিডিও প্রকাশিত হয়নি</p>
            </div>
        @endforelse
    </div>
    <div class="mt-8">{{ $videos->links() }}</div>
</div>
@endsection
