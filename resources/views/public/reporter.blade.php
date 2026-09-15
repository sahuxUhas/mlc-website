@extends('layouts.public')
@section('title', $reporter->name.' | '.site_setting('site_name'))
@section('content')
<div class="container mx-auto max-w-6xl px-3 py-6 sm:px-4 md:py-8">
    <div class="mb-6 flex items-start gap-4 rounded-2xl border border-gray-200 bg-white p-5 dark:border-[#263246] dark:bg-[#182233]">
        @if($reporter->photo)<img src="{{ asset('uploads/'.$reporter->photo) }}" alt="{{ $reporter->name }}" class="h-20 w-20 rounded-full object-cover ring-2 ring-[#C5E7C8]">@else<i class="ph-fill ph-user-circle text-6xl text-gray-300"></i>@endif
        <div>
            <h1 class="font-serif text-xl font-bold text-gray-900 dark:text-[#F1F5F9] sm:text-2xl">{{ $reporter->name }}</h1>
            @if($reporter->designation)<p class="text-sm font-semibold text-[#1F7A3D] dark:text-[#22C55E]">{{ $reporter->designation }}</p>@endif
            @if($reporter->bio)<p class="mt-2 max-w-2xl text-sm leading-relaxed text-gray-600 dark:text-[#94A3B8]">{{ $reporter->bio }}</p>@endif
            <div class="mt-2 flex flex-wrap gap-3 text-[11px] text-gray-500 dark:text-[#94A3B8]">
                @if($reporter->email)<span><i class="ph ph-envelope-simple"></i> {{ $reporter->email }}</span>@endif
                @if($reporter->phone)<span dir="ltr"><i class="ph ph-phone"></i> {{ $reporter->phone }}</span>@endif
                @if($reporter->facebook)<a href="{{ $reporter->facebook }}" target="_blank" rel="noopener" class="hover:text-[#D50E18]"><i class="ph ph-facebook-logo"></i> ফেসবুক</a>@endif
            </div>
        </div>
    </div>
    <h2 class="mb-4 flex items-center gap-2 border-b-2 border-[#D50E18] pb-2 font-serif text-lg font-bold text-gray-900 dark:text-[#F1F5F9]"><span class="h-5 w-1.5 rounded-sm bg-[#E21D2B]"></span> প্রকাশিত সংবাদ ({{ bn_count($posts->total()) }})</h2>
    <div class="space-y-4">
        @forelse($posts as $post)<div>@include('partials.news-card',['post'=>$post])</div>
        @empty<p class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500 dark:border-[#263246] dark:bg-[#182233]">এই রিপোর্টারের কোনো প্রকাশিত সংবাদ নেই।</p>@endforelse
    </div>
    <div class="mt-8">{{ $posts->links() }}</div>
</div>
@endsection
