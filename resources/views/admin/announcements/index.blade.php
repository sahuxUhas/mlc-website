@extends('admin.layouts.master')
@section('title','ঘোষণা')
@section('content')
<x-admin.page-head title="ঘোষণা (Announcements)" subtitle="নোটিশ, সতর্কতা, ইভেন্ট ও চাকরির খবর — সময়সীমা সহ" action="addAnn" actionLabel="নতুন ঘোষণা" />
<x-admin.filter-bar :action="route('admin.announcements.index')" :fields="[
    ['name'=>'q','placeholder'=>'শিরোনাম খুঁজুন…'],
    ['name'=>'type','type'=>'select','placeholder'=>'সব ধরন','options'=>\App\Models\Announcement::TYPES],
    ['name'=>'status','type'=>'select','placeholder'=>'সব স্ট্যাটাস','options'=>['draft'=>'খসড়া','published'=>'প্রকাশিত','scheduled'=>'নির্ধারিত','expired'=>'মেয়াদোত্তীর্ণ']]]" />
<div class="mc-card overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left text-sm">
    <thead class="bg-slate-50 text-[11px] uppercase text-slate-500 dark:bg-slate-800/60 dark:text-slate-400"><tr>
        <th class="px-4 py-3">ঘোষণা</th><th class="px-4 py-3">ধরন</th><th class="px-4 py-3">সময়সীমা</th>
        <th class="px-4 py-3 text-center">অগ্রাধিকার</th><th class="px-4 py-3 text-center">স্ট্যাটাস</th><th class="px-4 py-3 text-right">অ্যাকশন</th></tr></thead>
    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        @forelse($announcements as $a)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40"><td class="px-4 py-2.5"><div class="flex items-start gap-2.5">
                @if($a->image)<img src="{{ mc_image($a->image) }}" alt="" class="h-9 w-14 shrink-0 rounded object-cover" loading="lazy">@endif
                <div class="min-w-0"><p class="truncate font-serif text-sm font-bold text-slate-900 dark:text-white">{{ $a->title }}</p>
                <p class="line-clamp-1 text-[11px] text-slate-500">{{ $a->body }}</p></div></div></td>
            <td class="px-4 py-2.5"><span class="mc-badge bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ \App\Models\Announcement::TYPES[$a->type] ?? $a->type }}</span></td>
            <td class="px-4 py-2.5 text-[11px] text-slate-600 dark:text-slate-300">{{ $a->starts_at ? bn_date($a->starts_at) : '—' }}<span class="block text-slate-400">→ {{ $a->expires_at ? bn_date($a->expires_at) : 'মেয়াদ অসীম' }}</span></td>
            <td class="px-4 py-2.5 text-center">{{ bn_num($a->priority) }}</td>
            <td class="px-4 py-2.5 text-center">@php $sc=['draft'=>'bg-slate-500','published'=>'bg-emerald-500','scheduled'=>'bg-blue-500','expired'=>'bg-slate-400'][$a->status]??'bg-slate-500'; @endphp
                <span class="mc-badge {{ $sc }} text-white">{{ \App\Models\Announcement::STATUSES[$a->status] ?? $a->status }}</span></td>
            <td class="px-4 py-2.5 text-right"><x-admin.action-buttons :edit="'editAnn-'.$a->id" :view="route('announcements.show',$a->slug)"
                :destroy="route('admin.announcements.destroy',$a)" :restore="route('admin.announcements.restore',$a)" /></td></tr>
        @empty <x-admin.empty-state colspan="6" icon="ph-megaphone" message="কোনো ঘোষণা নেই" hint="উপরের ‘নতুন ঘোষণা’ বাটনে ক্লিক করুন।" /> @endforelse
    </tbody></table></div></div>
{{ $announcements->links() }}
<x-admin.modal id="addAnn" title="নতুন ঘোষণা" size="lg">@include('admin.announcements._fields',['a'=>new \App\Models\Announcement(['status'=>'published','type'=>'notice','is_visible'=>true,'priority'=>0])])</x-admin.modal>
@foreach($announcements as $a)<x-admin.modal :id="'editAnn-'.$a->id" :title="'এডিট: '.$a->title">@include('admin.announcements._fields',['a'=>$a,'edit'=>true])</x-admin.modal>@endforeach
@endsection
