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

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($videos as $video)
            <a href="{{ route('videos.show', $video->slug) }}" class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all hover:shadow-md dark:border-[#263246] dark:bg-[#182233]">
                <div class="relative aspect-video overflow-hidden bg-gray-900">
                    @if($video->thumbnail)
                        <img src="{{ mc_image($video->thumbnail) }}" alt="{{ $video->title }}" loading="lazy" class="h-full w-full object-cover opacity-90 transition-transform duration-500 group-hover:scale-105">
                    @else
                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#0B0B0B] to-[#1F7A3D]/40"></div>
                    @endif
                    <span class="absolute inset-0 flex items-center justify-center">
                        <span class="flex h-14 w-14 items-center justify-center rounded-full bg-[#D50E18]/90 text-white shadow-lg transition-transform group-hover:scale-110">
                            <i class="ph-fill ph-play text-2xl"></i>
                        </span>
                    </span>
                    @if($video->duration)
                        <span class="absolute bottom-2 left-2 rounded bg-black/75 px-1.5 py-0.5 text-[10px] font-bold text-white">{{ $video->duration }}</span>
                    @endif
                    @if($video->is_reel)
                        <span class="absolute left-2 top-2 rounded bg-[#1F7A3D] px-1.5 py-0.5 text-[10px] font-bold text-white">রিল</span>
                    @endif
                </div>
                <div class="p-4">
                    <h2 class="line-clamp-2 font-serif text-sm font-bold leading-snug text-gray-900 group-hover:text-[#E21D2B] dark:text-[#F1F5F9] dark:group-hover:text-[#22C55E]">{{ $video->title }}</h2>
                    <p class="mt-2 flex items-center gap-2 text-[11px] text-gray-500 dark:text-[#94A3B8]">
                        <span><i class="ph ph-calendar-blank"></i> {{ bn_date($video->published_at, false) }}</span>
                        <span><i class="ph ph-eye"></i> {{ bn_count($video->views) }}</span>
                    </p>
                </div>
            </a>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center dark:border-[#263246] dark:bg-[#182233]">
                <i class="ph ph-video-camera text-4xl text-gray-300"></i>
                <p class="mt-3 font-serif font-bold text-gray-700 dark:text-gray-300">এখনও কোনো ভিডিও প্রকাশিত হয়নি</p>
            </div>
        @endforelse
    </div>
    <div class="mt-8">{{ $videos->links() }}</div>
</div>
@endsection
