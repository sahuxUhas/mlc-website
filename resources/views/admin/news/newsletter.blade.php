@extends('admin.layouts.master')
@section('title','নিউজলেটার')
@section('content')
<x-admin.page-head title="নিউজলেটার সাবস্ক্রাইবার" subtitle="ইমেইল তালিকা — পাবলিক সাইটের নিউজলেটার ফর্ম থেকে যোগ হয়" />
<div class="mb-4 grid grid-cols-2 gap-3 sm:max-w-md">
    <div class="mc-card p-3 text-center"><i class="ph ph-users text-lg text-[#E21D2B]"></i><p class="mt-1 font-serif text-lg font-bold text-slate-900 dark:text-white">{{ bn_count($total) }}</p><p class="text-[11px] text-slate-500">মোট সাবস্ক্রাইবার</p></div>
    <div class="mc-card p-3 text-center"><i class="ph ph-check-circle text-lg text-emerald-600"></i><p class="mt-1 font-serif text-lg font-bold text-slate-900 dark:text-white">{{ bn_count($active) }}</p><p class="text-[11px] text-slate-500">সক্রিয়</p></div>
</div>
<x-admin.filter-bar :action="route('admin.newsletter.index')" :fields="[
    ['name'=>'q','placeholder'=>'ইমেইল খুঁজুন…'],
    ['name'=>'active','type'=>'select','placeholder'=>'সব অবস্থা','options'=>['1'=>'সক্রিয়','0'=>'বাতিল']]]" />
<div class="mc-card overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left text-sm">
    <thead class="bg-slate-50 text-[11px] uppercase text-slate-500 dark:bg-slate-800/60 dark:text-slate-400"><tr>
        <th class="px-4 py-3">ইমেইল</th><th class="px-4 py-3">সাবস্ক্রিপশন তারিখ</th><th class="px-4 py-3 text-center">স্ট্যাটাস</th><th class="px-4 py-3 text-right">অ্যাকশন</th></tr></thead>
    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        @forelse($subscribers as $s)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                <td class="px-4 py-2.5"><div class="flex items-center gap-2"><i class="ph ph-envelope-simple text-slate-400"></i><span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $s->email }}</span></div></td>
                <td class="px-4 py-2.5 text-[11px] text-slate-500">{{ bn_date($s->created_at) }} · {{ bn_ago($s->created_at) }}</td>
                <td class="px-4 py-2.5 text-center">@if($s->is_active)<span class="mc-badge bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">সক্রিয়</span>@else<span class="mc-badge bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300">বাতিল</span>@endif</td>
                <td class="px-4 py-2.5 text-right"><form action="{{ route('admin.newsletter.destroy',$s) }}" method="POST" data-confirm="‘{{ $s->email }}’ সাবস্ক্রিপশন মুছে ফেলবেন?">@csrf @method('DELETE')
                    <button type="submit" title="মুছুন" class="rounded border border-red-300 p-1.5 text-red-600 hover:bg-red-50 dark:border-red-800"><i class="ph ph-trash-simple text-xs"></i></button></form></td></tr>
        @empty <x-admin.empty-state colspan="4" icon="ph-envelope-open" message="কোনো সাবস্ক্রাইবার নেই" hint="পাবলিক সাইটের নিউজলেটার ফর্ম থেকে ইমেইল যোগ হলে এখানে দেখা যাবে।" /> @endforelse
    </tbody></table></div></div>
{{ $subscribers->links() }}
@endsection
