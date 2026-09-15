@extends('layouts.public')
@section('title','রিপোর্টার ও লেখক | '.site_setting('site_name'))
@section('content')
<div class="container mx-auto max-w-6xl px-3 py-6 sm:px-4 md:py-8">
    <div class="mb-6 flex items-center gap-2.5 border-b-2 border-[#D50E18] pb-3">
        <span class="h-6 w-2 rounded-sm bg-[#E21D2B]"></span><h1 class="font-serif text-xl font-bold text-gray-900 dark:text-[#F1F5F9] sm:text-2xl">রিপোর্টার ও লেখক</h1>
    </div>
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        @forelse($reporters as $reporter)
            <a href="{{ route('reporters.show',$reporter->slug) }}" class="group rounded-2xl border border-gray-200 bg-white p-4 text-center shadow-sm transition-all hover:shadow-md dark:border-[#263246] dark:bg-[#182233]">
                @if($reporter->photo)<img src="{{ asset('uploads/'.$reporter->photo) }}" alt="{{ $reporter->name }}" loading="lazy" class="mx-auto h-20 w-20 rounded-full object-cover ring-2 ring-[#C5E7C8] dark:ring-[#263246]">
                @else<i class="ph-fill ph-user-circle text-6xl text-gray-300 dark:text-gray-600"></i>@endif
                <h2 class="mt-3 font-serif text-sm font-bold text-gray-900 group-hover:text-[#E21D2B] dark:text-[#F1F5F9] dark:group-hover:text-[#22C55E]">{{ $reporter->name }}</h2>
                @if($reporter->designation)<p class="mt-0.5 text-[11px] text-gray-500 dark:text-[#94A3B8]">{{ $reporter->designation }}</p>@endif
                <p class="mt-1.5 text-[10px] font-bold text-[#1F7A3D] dark:text-[#22C55E]">{{ bn_count($reporter->publishedPostsCount()) }}টি সংবাদ</p>
            </a>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center dark:border-[#263246] dark:bg-[#182233]"><i class="ph ph-user-focus text-4xl text-gray-300"></i><p class="mt-3 font-serif font-bold text-gray-700 dark:text-gray-300">কোনো রিপোর্টার যোগ করা হয়নি</p></div>
        @endforelse
    </div>
    <div class="mt-8">{{ $reporters->links() }}</div>
</div>
@endsection
