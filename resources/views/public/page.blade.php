@extends('layouts.public')
@section('title', ($page->meta_title ?: $page->title).' | '.site_setting('site_name'))
@section('meta_description', $page->meta_description ?: mc_excerpt($page->excerpt ?: $page->content, 240))
@section('meta_keywords', $page->meta_keywords)
@section('og_image', $page->og_image ? asset($page->og_image) : ($page->featured_image ? mc_image($page->featured_image) : ''))
@section('canonical', $page->canonical_url ?: route('page.show',$page->slug))
@section('content')
<div class="container mx-auto max-w-4xl px-3 py-6 sm:px-4 md:py-8">
    <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-[#263246] dark:bg-[#182233] sm:p-8">
        <h1 class="border-b-2 border-[#D50E18] pb-3 font-serif text-2xl font-bold text-gray-900 dark:text-[#F1F5F9] sm:text-3xl">{{ $page->title }}</h1>
        @if($page->featured_image)<img src="{{ mc_image($page->featured_image) }}" alt="{{ $page->title }}" class="mt-5 w-full rounded-xl object-cover" loading="lazy">@endif
        @if($page->excerpt)<p class="mt-4 border-r-4 border-[#E21D2B] bg-gray-50 px-4 py-3 text-sm font-medium text-gray-700 dark:bg-[#0D1422] dark:text-gray-300">{{ $page->excerpt }}</p>@endif
        <div class="article-body mt-5 font-serif text-[1.02rem] leading-[2] text-gray-800 dark:text-gray-200">{!! $page->content !!}</div>

        @if($page->slug === 'contact')
            <div class="mt-8 rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-[#263246] dark:bg-[#0D1422]">
                <h2 class="mb-4 font-serif text-lg font-bold text-gray-900 dark:text-[#F1F5F9]">যোগাযোগ ফর্ম</h2>
                @if($errors->any())<div class="mb-3 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300"><ul class="list-inside list-disc">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
                <form action="{{ route('contact.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="আপনার নাম *" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-[#D50E18] focus:ring-2 focus:ring-[#D50E18]/30 dark:border-gray-600 dark:bg-[#0D1422] dark:text-white">
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="ইমেইল *" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-[#D50E18] focus:ring-2 focus:ring-[#D50E18]/30 dark:border-gray-600 dark:bg-[#0D1422] dark:text-white">
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="ফোন (ঐচ্ছিক)" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-[#D50E18] focus:ring-2 focus:ring-[#D50E18]/30 dark:border-gray-600 dark:bg-[#0D1422] dark:text-white">
                        <input type="text" name="subject" value="{{ old('subject') }}" placeholder="বিষয় (ঐচ্ছিক)" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-[#D50E18] focus:ring-2 focus:ring-[#D50E18]/30 dark:border-gray-600 dark:bg-[#0D1422] dark:text-white">
                    </div>
                    <textarea name="message" rows="5" required placeholder="আপনার বার্তা *" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-[#D50E18] focus:ring-2 focus:ring-[#D50E18]/30 dark:border-gray-600 dark:bg-[#0D1422] dark:text-white">{{ old('message') }}</textarea>
                    <button type="submit" class="rounded-lg bg-[#D50E18] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#B9121E]"><i class="ph ph-paper-plane-tilt"></i> পাঠান</button>
                </form>
            </div>
        @endif
    </article>
</div>
@endsection
