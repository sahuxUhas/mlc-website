@extends('layouts.public')
@section('title', $album->title.' | '.site_setting('site_name'))
@section('content')
<div class="container mx-auto max-w-6xl px-3 py-6 sm:px-4 md:py-8">
    <div class="mb-6 border-b-2 border-[#D50E18] pb-3">
        <h1 class="font-serif text-xl font-bold text-gray-900 dark:text-[#F1F5F9] sm:text-2xl">{{ $album->title }}</h1>
        @if($album->description)<p class="mt-2 text-sm text-gray-600 dark:text-[#94A3B8]">{{ $album->description }}</p>@endif
    </div>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
        @foreach($album->photos as $photo)
            <figure class="group overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-800">
                <img src="{{ asset('uploads/'.$photo->path) }}" alt="{{ $photo->caption ?: $album->title }}" loading="lazy" class="aspect-square w-full cursor-zoom-in object-cover transition-transform duration-500 group-hover:scale-105" data-mc-lightbox="{{ asset('uploads/'.$photo->path) }}">
                @if($photo->caption)<figcaption class="px-2 py-1.5 text-[11px] text-gray-600 dark:text-[#94A3B8]">{{ $photo->caption }}</figcaption>@endif
            </figure>
        @endforeach
    </div>
</div>
<div id="mc-lightbox" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/90 p-4">
    <button type="button" class="absolute left-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white" data-mc-lightbox-close><i class="ph-bold ph-x text-xl"></i></button>
    <img id="mc-lightbox-img" src="" alt="" class="max-h-[88vh] max-w-full rounded-lg object-contain">
</div>
@endsection
