@extends('admin.layouts.master')
@section('title','SEO সেটিংস')
@section('content')
<x-admin.page-head title="SEO ও সার্চ ইঞ্জিন সেটিংস" subtitle="মেটা ট্যাগ, robots.txt, XML sitemap ও Article structured data" />
<form action="{{ route('admin.seo.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
    @csrf @method('PUT')
    <div class="mc-card p-4 sm:p-5">
        <h2 class="mb-4 flex items-center gap-2 font-serif text-sm font-bold text-slate-900 dark:text-white"><i class="ph ph-magnifying-glass text-[#E21D2B]"></i> ডিফল্ট মেটা তথ্য</h2>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @foreach($fields as $field)
                @if(in_array($field['type'] ?? 'text', ['textarea','bool','image'], true))
                    <div class="md:col-span-2"><x-admin.dynamic-field :field="$field" :settings="$settings" /></div>
                @else
                    <x-admin.dynamic-field :field="$field" :settings="$settings" />
                @endif
            @endforeach
        </div>
    </div>

    {{-- লাইভ যাচাই লিংক --}}
    <div class="mc-card p-4">
        <h2 class="mb-3 flex items-center gap-2 font-serif text-sm font-bold text-slate-900 dark:text-white"><i class="ph ph-plugs-connected text-[#E21D2B]"></i> SEO ফাইল যাচাই</h2>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('sitemap') }}" target="_blank" class="mc-btn mc-btn-ghost text-xs"><i class="ph ph-globe"></i> sitemap.xml</a>
            <a href="{{ route('robots') }}" target="_blank" class="mc-btn mc-btn-ghost text-xs"><i class="ph ph-robot"></i> robots.txt</a>
            <a href="{{ route('feed') }}" target="_blank" class="mc-btn mc-btn-ghost text-xs"><i class="ph ph-rss"></i> RSS feed</a>
        </div>
        <p class="mt-3 text-[11px] text-slate-500">
            প্রতিটি সংবাদ, ক্যাটাগরি, ভিডিও ও অ্যালবামে নিজস্ব মেটা টাইটেল/বিবরণ/OG ট্যাগ ও canonical URL সেট করা যায়।
            Article JSON-LD structured data সংবাদ পেজে স্বয়ংক্রিয়ভাবে যুক্ত হয় (উপরে চালু/বন্ধ করা যায়)।
        </p>
    </div>

    <div class="flex gap-2"><button type="submit" class="mc-btn mc-btn-primary"><i class="ph-fill ph-floppy-disk"></i> SEO সেটিংস সংরক্ষণ</button></div>
</form>
@endsection
