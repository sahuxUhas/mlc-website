@extends('admin.layouts.master')
@section('title','বিজ্ঞাপন')
@section('content')
<x-admin.page-head title="বিজ্ঞাপন ব্যবস্থাপনা" subtitle="পজিশন, মেয়াদ, অগ্রাধিকার ও লিংক সহ ব্যানার/HTML বিজ্ঞাপন" action="addAd" actionLabel="নতুন বিজ্ঞাপন" />
<x-admin.filter-bar :action="route('admin.ads.index')" :fields="[
    ['name'=>'q','placeholder'=>'শিরোনাম খুঁজুন…'],
    ['name'=>'position','type'=>'select','placeholder'=>'সব পজিশন','options'=>$positions],
    ['name'=>'status','type'=>'select','placeholder'=>'সব অবস্থা','options'=>['active'=>'সক্রিয়','inactive'=>'বন্ধ','expired'=>'মেয়াদোত্তীর্ণ','scheduled'=>'আসন্ন']]]" />
<div class="mc-card overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left text-sm">
    <thead class="bg-slate-50 text-[11px] uppercase text-slate-500 dark:bg-slate-800/60 dark:text-slate-400"><tr>
        <th class="px-4 py-3">বিজ্ঞাপন</th><th class="px-4 py-3">পজিশন</th><th class="px-4 py-3">মেয়াদ</th>
        <th class="px-4 py-3 text-center">ধরন</th><th class="px-4 py-3 text-center">প্রায়রিটি</th><th class="px-4 py-3 text-center">ক্লিক/ইম্প্রেশন</th><th class="px-4 py-3 text-center">সক্রিয়</th><th class="px-4 py-3 text-right">অ্যাকশন</th></tr></thead>
    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        @forelse($ads as $ad)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                <td class="px-4 py-2.5"><div class="flex items-center gap-2.5">@if($ad->image)<img src="{{ mc_image($ad->image) }}" alt="" class="h-9 w-20 shrink-0 rounded border border-slate-200 object-cover dark:border-slate-700" loading="lazy">@endif
                    <div class="min-w-0"><p class="truncate font-serif text-sm font-bold text-slate-900 dark:text-white">{{ $ad->title }}</p>
                    @if($ad->link)<code class="text-[10px] text-slate-400" dir="ltr">{{ Str::limit($ad->link,42) }}</code>@endif</div></div></td>
                <td class="px-4 py-2.5"><span class="mc-badge bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ $positions[$ad->position] ?? $ad->position }}</span></td>
                <td class="px-4 py-2.5 text-[11px] text-slate-600 dark:text-slate-300">{{ $ad->starts_at ? bn_date($ad->starts_at) : '—' }}<span class="block text-slate-400">→ {{ $ad->ends_at ? bn_date($ad->ends_at) : 'চিরকাল' }}</span></td>
                <td class="px-4 py-2.5 text-center"><span class="mc-badge {{ $ad->type==='html' ? 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300' }}">{{ $ad->type==='html' ? 'HTML' : 'ছবি' }}</span></td>
                <td class="px-4 py-2.5 text-center">{{ bn_num($ad->priority) }}</td>
                <td class="px-4 py-2.5 text-center text-[11px]">{{ bn_count($ad->clicks) }} / {{ bn_count($ad->impressions) }}</td>
                <td class="px-4 py-2.5 text-center">@if($ad->is_enabled)<span class="mc-badge bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">হ্যাঁ</span>@else<span class="mc-badge bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300">না</span>@endif</td>
                <td class="px-4 py-2.5 text-right"><x-admin.action-buttons :edit="'editAd-'.$ad->id" :view="$ad->link"
                    :toggle="route('admin.ads.toggle',$ad)" :toggleOn="(bool)$ad->is_enabled" :destroy="route('admin.ads.destroy',$ad)" /></td></tr>
        @empty <x-admin.empty-state colspan="8" icon="ph-megaphone-simple" message="কোনো বিজ্ঞাপন নেই" hint="উপরের ‘নতুন বিজ্ঞাপন’ বাটনে ক্লিক করুন।" /> @endforelse
    </tbody></table></div></div>
{{ $ads->links() }}
<x-admin.modal id="addAd" title="নতুন বিজ্ঞাপন" size="lg">@include('admin.ads._fields',['ad'=>new \App\Models\Advertisement(['type'=>'image','is_enabled'=>true,'priority'=>0,'position'=>'homepage','link_target'=>'_blank'])])</x-admin.modal>
@foreach($ads as $ad)<x-admin.modal :id="'editAd-'.$ad->id" :title="'এডিট: '.$ad->title">@include('admin.ads._fields',['ad'=>$ad,'edit'=>true])</x-admin.modal>@endforeach
@endsection
