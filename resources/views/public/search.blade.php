@extends('layouts.public')
@section('title', ($q !== '' ? 'সার্চ: '.$q : 'সার্চ').' | '.site_setting('site_name', config('app.name')))
@section('robots', 'noindex, follow')

@section('content')
<div class="container mx-auto max-w-6xl px-3 py-6 sm:px-4 md:py-8">
    <div class="mb-6 border-b-2 border-[#D50E18] pb-3">
        <h1 class="flex items-center gap-2.5 font-serif text-xl font-bold text-gray-900 dark:text-[#F1F5F9] sm:text-2xl">
            <span class="h-6 w-2 rounded-sm bg-[#E21D2B]"></span> সংবাদ খুঁজুন
        </h1>
    </div>

    {{-- সার্চ ফর্ম + ফিল্টার --}}
    <form action="{{ route('search') }}" method="GET" class="mb-6 rounded-2xl border border-gray-200 bg-white p-4 dark:border-[#263246] dark:bg-[#182233] sm:p-5">
        <div class="flex flex-col gap-3 sm:flex-row">
            <div class="relative flex-1">
                <i class="ph ph-magnifying-glass pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-lg text-gray-400"></i>
                <input type="search" name="q" value="{{ $q }}" placeholder="কীওয়ার্ড লিখুন…"
                       class="w-full rounded-xl border border-gray-300 bg-white py-2.5 pl-3 pr-10 text-sm focus:border-[#D50E18] focus:ring-2 focus:ring-[#D50E18]/30 dark:border-[#263246] dark:bg-[#0D1422] dark:text-white">
            </div>
            <select name="category" class="rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-[#263246] dark:bg-[#0D1422] dark:text-white">
                <option value="">সব বিভাগ</option>
                @foreach($navCategories ?? [] as $c)
                    <option value="{{ $c->id }}" @selected($categoryId === $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            <select name="sort" class="rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-[#263246] dark:bg-[#0D1422] dark:text-white">
                <option value="latest" @selected($sort === 'latest')>সর্বশেষ</option>
                <option value="popular" @selected($sort === 'popular')>সর্বাধিক পঠিত</option>
                <option value="oldest" @selected($sort === 'oldest')>পুরোনো আগে</option>
            </select>
            <button type="submit" class="rounded-xl bg-[#D50E18] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#B9121E]">খুঁজুন</button>
        </div>
    </form>

    <p class="mb-4 text-sm text-gray-600 dark:text-[#94A3B8]">
        @if($q !== '')
            "<strong class="text-[#D50E18] dark:text-[#22C55E]">{{ $q }}</strong>" — {{ bn_count($posts->total()) }}টি ফলাফল পাওয়া গেছে
        @else
            মোট {{ bn_count($posts->total()) }}টি সংবাদ
        @endif
    </p>

    @forelse($posts as $post)
        <div class="mb-4">@include('partials.news-card', ['post' => $post])</div>
    @empty
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center dark:border-[#263246] dark:bg-[#182233]">
            <i class="ph ph-binoculars text-4xl text-gray-300 dark:text-gray-600"></i>
            <p class="mt-3 font-serif text-lg font-bold text-gray-700 dark:text-gray-300">কোনো ফলাফল পাওয়া যায়নি</p>
            <p class="mt-1 text-sm text-gray-500">অন্য কীওয়ার্ড দিয়ে চেষ্টা করুন।</p>
        </div>
    @endforelse

    <div class="mt-8">{{ $posts->links() }}</div>
</div>
@endsection
