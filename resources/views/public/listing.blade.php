{{-- ===== সাধারণ তালিকা পেজ (সর্বশেষ / ক্যাটাগরি / ট্যাগ / সার্চ) ===== --}}
@extends('layouts.public')

@section('title', $title.' | '.site_setting('site_name', config('app.name')))
@section('meta_description', $description ?? '')
@section('canonical', $canonical ?? url()->current())

@section('content')
<div class="container mx-auto max-w-6xl px-3 py-6 sm:px-4 md:py-8">
    @ad('top_header')

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 border-b-2 border-[#D50E18] pb-3">
        <h1 class="flex items-center gap-2.5 font-serif text-xl font-bold text-gray-900 dark:text-[#F1F5F9] sm:text-2xl">
            <span class="h-6 w-2 rounded-sm bg-[#E21D2B]"></span>{{ $title }}
        </h1>
        @if(isset($subtitle) && $subtitle)
            <p class="text-xs text-gray-500 dark:text-[#94A3B8]">{{ $subtitle }}</p>
        @endif
    </div>

    @if(isset($extra))
        {!! $extra !!}
    @endif

    @forelse($posts as $post)
        <div class="mb-4">
            @include('partials.news-card', ['post' => $post])
        </div>
    @empty
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center dark:border-[#263246] dark:bg-[#182233]">
            <i class="ph ph-magnifying-glass text-4xl text-gray-300 dark:text-gray-600"></i>
            <p class="mt-3 font-serif text-lg font-bold text-gray-700 dark:text-gray-300">{{ $emptyMessage ?? 'কোনো সংবাদ পাওয়া যায়নি' }}</p>
        </div>
    @endforelse

    <div class="mt-8">{{ $posts->links() }}</div>
</div>
@endsection
