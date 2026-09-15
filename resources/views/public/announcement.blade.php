@extends('layouts.public')
@section('title', $announcement->title.' | '.site_setting('site_name'))
@section('content')
<div class="container mx-auto max-w-4xl px-3 py-6 sm:px-4 md:py-8">
    <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-[#263246] dark:bg-[#182233] sm:p-8">
        <span class="rounded-full bg-[#E21D2B] px-2.5 py-1 text-[10px] font-bold uppercase text-white">{{ \App\Models\Announcement::TYPES[$announcement->type] ?? $announcement->type }}</span>
        <h1 class="mt-3 font-serif text-2xl font-bold leading-snug text-gray-900 dark:text-[#F1F5F9]">{{ $announcement->title }}</h1>
        <p class="mt-2 text-xs text-gray-500 dark:text-[#94A3B8]">
            <i class="ph ph-calendar-blank"></i> {{ bn_date($announcement->created_at) }}
            @if($announcement->expires_at) &nbsp;•&nbsp; মেয়াদ: {{ bn_date($announcement->expires_at, false) }}@endif
        </p>
        @if($announcement->image)
            <img src="{{ mc_image($announcement->image) }}" alt="{{ $announcement->title }}" class="mt-4 w-full rounded-xl object-cover" loading="lazy">
        @endif
        @if($announcement->body)
            <div class="mt-5 whitespace-pre-line text-base leading-[2] text-gray-800 dark:text-gray-200">{{ $announcement->body }}</div>
        @endif
        @if($announcement->link)
            <a href="{{ $announcement->link }}" target="_blank" rel="noopener" class="mt-5 inline-block rounded-lg bg-[#D50E18] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#B9121E]">
                {{ $announcement->link_text ?: 'বিস্তারিত দেখুন' }} <i class="ph ph-arrow-square-out"></i>
            </a>
        @endif
    </article>

    @if($more->isNotEmpty())
        <h2 class="mt-8 mb-3 flex items-center gap-2 border-b-2 border-[#D50E18] pb-2 font-serif text-lg font-bold text-gray-900 dark:text-[#F1F5F9]">
            <span class="h-5 w-1.5 rounded-sm bg-[#E21D2B]"></span> অন্যান্য ঘোষণা
        </h2>
        <ul class="space-y-2">
            @foreach($more as $item)
                <li><a href="{{ route('announcements.show', $item->slug) }}" class="block rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-800 hover:border-[#D50E18] hover:text-[#D50E18] dark:border-[#263246] dark:bg-[#182233] dark:text-[#F1F5F9]">{{ $item->title }}</a></li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
