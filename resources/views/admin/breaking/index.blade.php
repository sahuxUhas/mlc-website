@extends('admin.layouts.master')
@section('title','ব্রেকিং নিউজ')
@section('content')
<x-admin.page-head title="ব্রেকিং নিউজ" subtitle="হেডারের স্ক্রলিং বার — শুধু সক্রিয় ও মেয়াদের ভেতরের আইটেম দেখায়" action="addBrk" actionLabel="নতুন ব্রেকিং" />
<div class="mc-card overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left text-sm">
    <thead class="bg-slate-50 text-[11px] uppercase text-slate-500 dark:bg-slate-800/60 dark:text-slate-400"><tr>
        <th class="px-4 py-3">শিরোনাম</th><th class="px-4 py-3">সংযুক্ত সংবাদ</th><th class="px-4 py-3">মেয়াদ</th>
        <th class="px-4 py-3 text-center">অগ্রাধিকার</th><th class="px-4 py-3 text-center">ক্রম</th><th class="px-4 py-3 text-center">সক্রিয়</th><th class="px-4 py-3 text-right">অ্যাকশন</th></tr></thead>
    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        @forelse($items as $b)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40"><td class="px-4 py-2.5"><p class="max-w-xs truncate font-serif text-sm font-bold text-slate-900 dark:text-white">{{ $b->title }}</p>
                @if($b->url)<code class="text-[10px] text-slate-400" dir="ltr">{{ Str::limit($b->url,40) }}</code>@endif</td>
            <td class="px-4 py-2.5 text-xs">{{ $b->post ? Str::limit($b->post->title,45) : '—' }}</td>
            <td class="px-4 py-2.5 text-[11px] text-slate-600 dark:text-slate-300">{{ $b->starts_at ? bn_date($b->starts_at) : '—' }}<span class="block text-slate-400">→ {{ $b->ends_at ? bn_date($b->ends_at) : 'চিরকাল' }}</span></td>
            <td class="px-4 py-2.5 text-center">{{ bn_num($b->priority) }}</td><td class="px-4 py-2.5 text-center">{{ bn_num($b->sort_order) }}</td>
            <td class="px-4 py-2.5 text-center">@if($b->is_enabled)<span class="mc-badge bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">হ্যাঁ</span>@else<span class="mc-badge bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300">না</span>@endif</td>
            <td class="px-4 py-2.5 text-right"><x-admin.action-buttons :edit="'editBrk-'.$b->id" :view="$b->post ? route('news.show',$b->post->slug) : ($b->url ?: null)"
                :toggle="route('admin.breaking.toggle',$b)" :toggleOn="(bool)$b->is_enabled" :destroy="route('admin.breaking.destroy',$b)" /></td></tr>
        @empty <x-admin.empty-state colspan="7" icon="ph-lightning" message="কোনো ব্রেকিং নিউজ নেই" hint="উপরের বাটন থেকে যোগ করুন।" /> @endforelse
    </tbody></table></div></div>
{{ $items->links() }}
<x-admin.modal id="addBrk" title="নতুন ব্রেকিং নিউজ">@include('admin.breaking._fields',['b'=>new \App\Models\BreakingNews(['is_enabled'=>true,'priority'=>0,'sort_order'=>0])])</x-admin.modal>
@foreach($items as $b)<x-admin.modal :id="'editBrk-'.$b->id" :title="'এডিট: '.Str::limit($b->title,40)">@include('admin.breaking._fields',['b'=>$b,'edit'=>true])</x-admin.modal>@endforeach
@endsection
