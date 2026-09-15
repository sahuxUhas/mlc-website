@extends('layouts.public')
@section('title','ফটো গ্যালারি | '.site_setting('site_name'))
@section('content')
<div class="container mx-auto max-w-6xl px-3 py-6 sm:px-4 md:py-8">
    <div class="mb-6 flex items-center gap-2.5 border-b-2 border-[#D50E18] pb-3">
        <span class="h-6 w-2 rounded-sm bg-[#E21D2B]"></span>
        <h1 class="font-serif text-xl font-bold text-gray-900 dark:text-[#F1F5F9] sm:text-2xl">ফটো গ্যালারি</h1>
    </div>
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($albums as $album)
            <a href="{{ route('gallery.show', $album->slug) }}" class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all hover:shadow-md dark:border-[#263246] dark:bg-[#182233]">
                <div class="relative aspect-video overflow-hidden bg-gray-100 dark:bg-gray-800">
                    <img src="{{ $album->cover_image ? asset('uploads/'.$album->cover_image) : ($album->photos->first() ? asset('uploads/'.$album->photos->first()->path) : mc_placeholder_svg()) }}" alt="{{ $album->title }}" loading="lazy" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                    <span class="absolute bottom-2 left-2 flex items-center gap-1 rounded bg-black/70 px-2 py-0.5 text-[10px] font-bold text-white"><i class="ph ph-images"></i> {{ bn_count($album->photos_count) }}টি ছবি</span>
                </div>
                <div class="p-4">
                    <h2 class="line-clamp-2 font-serif text-sm font-bold text-gray-900 group-hover:text-[#E21D2B] dark:text-[#F1F5F9] dark:group-hover:text-[#22C55E]">{{ $album->title }}</h2>
                    @if($album->description)<p class="mt-1.5 line-clamp-2 text-xs text-gray-600 dark:text-[#94A3B8]">{{ mc_excerpt($album->description,120) }}</p>@endif
                </div>
            </a>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center dark:border-[#263246] dark:bg-[#182233]">
                <i class="ph ph-images text-4xl text-gray-300"></i><p class="mt-3 font-serif font-bold text-gray-700 dark:text-gray-300">এখনও কোনো অ্যালবাম নেই</p>
            </div>
        @endforelse
    </div>
    <div class="mt-8">{{ $albums->links() }}</div>
</div>
@endsection
