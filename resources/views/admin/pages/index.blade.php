@extends('admin.layouts.master')
@section('title','স্ট্যাটিক পেজ')
@section('content')
<x-admin.page-head title="স্ট্যাটিক পেজ" subtitle="আমাদের সম্পর্কে, যোগাযোগ, বিজ্ঞাপন নীতিমালা প্রভৃতি" actionLabel="নতুন পেজ" action="new" />
<a href="{{ route('admin.pages.create') }}" class="mc-btn mc-btn-primary mb-4 flex items-center gap-1.5"><i class="ph ph-plus"></i> নতুন পেজ তৈরি করুন</a>
<div class="mc-card overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left text-sm">
    <thead class="bg-slate-50 text-[11px] uppercase text-slate-500 dark:bg-slate-800/60 dark:text-slate-400"><tr>
        <th class="px-4 py-3">পেজ</th><th class="px-4 py-3">স্লাগ</th><th class="px-4 py-3">টেমপ্লেট</th>
        <th class="px-4 py-3 text-center">ক্রম</th><th class="px-4 py-3 text-center">স্ট্যাটাস</th><th class="px-4 py-3 text-right">অ্যাকশন</th></tr></thead>
    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        @forelse($pages as $p)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 {{ $p->trashed() ? 'opacity-60' : '' }}">
                <td class="px-4 py-2.5"><p class="font-serif text-sm font-bold text-slate-900 dark:text-white">{{ $p->title }}</p>
                    <p class="line-clamp-1 text-[11px] text-slate-500">{{ mc_excerpt($p->content, 70) }}</p></td>
                <td class="px-4 py-2.5"><code class="text-[11px] text-slate-500" dir="ltr">/{{ $p->slug }}</code></td>
                <td class="px-4 py-2.5"><span class="mc-badge bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ $p->template }}</span></td>
                <td class="px-4 py-2.5 text-center">{{ bn_num($p->sort_order) }}</td>
                <td class="px-4 py-2.5 text-center">@if($p->trashed())<span class="mc-badge bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300">ট্র্যাশে</span>
                    @elseif($p->is_visible)<span class="mc-badge bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">প্রকাশিত</span>
                    @else<span class="mc-badge bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300">লুকানো</span>@endif</td>
                <td class="px-4 py-2.5 text-right"><x-admin.action-buttons :edit="null" :view="route('page.show',$p->slug)"
                    :destroy="$p->trashed() ? null : route('admin.pages.destroy',$p)" /></td></tr>
        @empty <x-admin.empty-state colspan="6" icon="ph-file-text" message="কোনো স্ট্যাটিক পেজ নেই" hint="উপরের বাটন থেকে প্রথম পেজ তৈরি করুন।" /> @endforelse
    </tbody></table></div></div>
{{ $pages->links() }}
@endsection
