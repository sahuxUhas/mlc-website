@extends('layouts.public')
@section('title','ই-পেপার | '.site_setting('site_name'))
@section('content')
<div class="container mx-auto max-w-6xl px-3 py-6 sm:px-4 md:py-8">
    <div class="mb-6 flex items-center gap-2.5 border-b-2 border-[#D50E18] pb-3">
        <span class="h-6 w-2 rounded-sm bg-[#E21D2B]"></span>
        <h1 class="font-serif text-xl font-bold text-gray-900 dark:text-[#F1F5F9] sm:text-2xl">ই-পেপার</h1>
    </div>
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @forelse($epapers as $epaper)
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-[#263246] dark:bg-[#182233]">
                <div class="aspect-[3/4] overflow-hidden bg-gray-100 dark:bg-gray-800">
                    <img src="{{ $epaper->cover_image ? mc_image($epaper->cover_image) : mc_placeholder_svg() }}" alt="{{ $epaper->title }}" loading="lazy" class="h-full w-full object-cover">
                </div>
                <div class="p-3">
                    <h2 class="line-clamp-1 font-serif text-sm font-bold text-gray-900 dark:text-[#F1F5F9]">{{ $epaper->title }}</h2>
                    <p class="mt-1 text-[11px] text-gray-500 dark:text-[#94A3B8]"><i class="ph ph-calendar-blank"></i> {{ bn_date($epaper->issue_date,false) }} @if($epaper->size_label) • {{ $epaper->size_label }}@endif</p>
                    <a href="{{ route('epaper.download',$epaper) }}" class="mt-2 block rounded-lg bg-[#D50E18] px-3 py-1.5 text-center text-xs font-bold text-white hover:bg-[#B9121E]"><i class="ph ph-download-simple"></i> ডাউনলোড</a>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center dark:border-[#263246] dark:bg-[#182233]">
                <i class="ph ph-newspaper text-4xl text-gray-300"></i><p class="mt-3 font-serif font-bold text-gray-700 dark:text-gray-300">এখনও কোনো ই-পেপার প্রকাশিত হয়নি</p>
            </div>
        @endforelse
    </div>
    <div class="mt-8">{{ $epapers->links() }}</div>
</div>
@endsection
