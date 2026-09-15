@extends('layouts.public')
@section('title', 'ঘোষণা | '.site_setting('site_name'))
@section('content')
<div class="container mx-auto max-w-6xl px-3 py-6 sm:px-4 md:py-8">
    <div class="mb-6 flex items-center gap-2.5 border-b-2 border-[#D50E18] pb-3">
        <span class="h-6 w-2 rounded-sm bg-[#E21D2B]"></span>
        <h1 class="font-serif text-xl font-bold text-gray-900 dark:text-[#F1F5F9] sm:text-2xl">ঘোষণা ও নোটিশ</h1>
    </div>

    <div class="space-y-4">
        @forelse($announcements as $announcement)
            @php
                $tone = match($announcement->type) {
                    'warning' => ['border-amber-300 bg-amber-50 dark:bg-amber-950/30', 'ph-warning-circle text-amber-600'],
                    'event'   => ['border-[#C5E7C8] bg-[#EAF7EC] dark:bg-[#182233]', 'ph-calendar-check text-[#1F7A3D]'],
                    'job'     => ['border-blue-200 bg-blue-50 dark:bg-blue-950/30', 'ph-briefcase text-blue-600'],
                    'notice'  => ['border-gray-200 bg-white dark:bg-[#182233]', 'ph-megaphone text-[#D50E18]'],
                    default   => ['border-gray-200 bg-white dark:bg-[#182233]', 'ph-info text-gray-500'],
                };
            @endphp
            <article class="rounded-2xl border p-4 shadow-sm transition-all hover:shadow-md sm:p-5 {{ $tone[0] }}">
                <div class="flex items-start gap-3">
                    <i class="ph-fill {{ $tone[1] }} mt-1 text-2xl"></i>
                    <div class="min-w-0 flex-1">
                        <div class="mb-1 flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-black/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide">{{ \App\Models\Announcement::TYPES[$announcement->type] ?? $announcement->type }}</span>
                            @if($announcement->priority > 0)
                                <span class="rounded-full bg-[#D50E18] px-2 py-0.5 text-[10px] font-bold text-white">অগ্রাধিকার {{ bn_num($announcement->priority) }}</span>
                            @endif
                            @if($announcement->expires_at)
                                <span class="text-[10px] text-gray-500 dark:text-[#94A3B8]">মেয়াদ: {{ bn_date($announcement->expires_at, false) }}</span>
                            @endif
                        </div>
                        <h2 class="font-serif text-base font-bold leading-snug text-gray-900 dark:text-[#F1F5F9] sm:text-lg">
                            <a href="{{ route('announcements.show', $announcement->slug) }}" class="hover:text-[#D50E18] dark:hover:text-[#22C55E]">{{ $announcement->title }}</a>
                        </h2>
                        @if($announcement->body)
                            <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-gray-700 dark:text-gray-300">{{ mc_excerpt($announcement->body, 240) }}</p>
                        @endif
                        <div class="mt-3 flex flex-wrap items-center gap-3 text-[11px] text-gray-500 dark:text-[#94A3B8]">
                            <span><i class="ph ph-calendar-blank"></i> {{ bn_date($announcement->created_at) }}</span>
                            <a href="{{ route('announcements.show', $announcement->slug) }}" class="font-bold text-[#D50E18] hover:underline dark:text-[#22C55E]">বিস্তারিত →</a>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center dark:border-[#263246] dark:bg-[#182233]">
                <i class="ph ph-megaphone text-4xl text-gray-300"></i>
                <p class="mt-3 font-serif font-bold text-gray-700 dark:text-gray-300">এই মুহূর্তে কোনো সক্রিয় ঘোষণা নেই</p>
            </div>
        @endforelse
    </div>
    <div class="mt-8">{{ $announcements->links() }}</div>
</div>
@endsection
