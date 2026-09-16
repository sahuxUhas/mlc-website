@extends('admin.layouts.master')
@section('title','মন্তব্য মডারেশন')
@section('content')
<x-admin.page-head title="মন্তব্য মডারেশন" subtitle="অনুমোদন / বাতিল / স্প্যাম — অ্যাকাউন্ট ছাড়াই ভিজিটররা মন্তব্য করে" />

{{-- স্ট্যাটাস ট্যাব (কাউন্ট সহ) --}}
<div class="mb-4 flex flex-wrap gap-2">
    @foreach(['all'=>'সব','pending'=>'অপেক্ষমাণ','approved'=>'অনুমোদিত','rejected'=>'বাতিল','spam'=>'স্প্যাম','reported'=>'রিপোর্ট করা'] as $key=>$lbl)
        @php $n = $counts[$key] ?? 0; $active = ($status === $key) || ($key==='all' && !$status); @endphp
        <a href="{{ route('admin.comments.index', $key==='all' ? [] : ['status'=>$key]) }}"
           class="rounded-full px-3 py-1.5 text-xs font-bold transition {{ $active ? 'bg-[#E21D2B] text-white' : 'bg-white text-slate-600 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300' }}">
            {{ $lbl }} <span class="{{ $active ? 'text-white/80' : 'text-slate-400' }}">{{ bn_count($n) }}</span>
        </a>
    @endforeach
</div>

<x-admin.filter-bar :action="route('admin.comments.index')" :fields="[
    ['name'=>'q','placeholder'=>'নাম বা মন্তব্য খুঁজুন…'],
    ['name'=>'status','type'=>'select','placeholder'=>'সব স্ট্যাটাস','options'=>['pending'=>'অপেক্ষমাণ','approved'=>'অনুমোদিত','rejected'=>'বাতিল','spam'=>'স্প্যাম','reported'=>'রিপোর্ট']]]" />

<form action="{{ route('admin.comments.bulk') }}" method="POST" data-confirm="নির্বাচিত মন্তব্যে এই অ্যাকশন প্রয়োগ করবেন?">
    @csrf
    <div class="mb-3 flex flex-wrap items-center gap-2">
        <select name="action" class="mc-input w-auto text-xs" required>
            <option value="">— বাল্ক অ্যাকশন —</option>
            <option value="approve">অনুমোদন</option><option value="reject">বাতিল</option>
            <option value="spam">স্প্যাম চিহ্নিত</option><option value="delete">মুছে ফেলুন</option>
        </select>
        <button type="submit" class="mc-btn mc-btn-primary text-xs"><i class="ph ph-check-square"></i> প্রয়োগ করুন</button>
        <span class="text-[11px] text-slate-500">নির্বাচিত: <span id="selCount" class="font-bold text-[#E21D2B]">০</span></span>
    </div>

    <div class="mc-card overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-[11px] uppercase text-slate-500 dark:bg-slate-800/60 dark:text-slate-400"><tr>
            <th class="w-8 px-3 py-3"><input type="checkbox" data-check-all=".bulk-check" class="h-4 w-4 rounded border-slate-300 text-[#E21D2B]"></th>
            <th class="px-4 py-3">মন্তব্যকারী</th><th class="px-4 py-3">মন্তব্য</th><th class="px-4 py-3">সংবাদ</th>
            <th class="px-4 py-3 text-center">স্ট্যাটাস</th><th class="px-4 py-3 text-right">অ্যাকশন</th></tr></thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($comments as $c)
                @php $item = $c->commentable; @endphp
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                    <td class="px-3 py-2.5"><input type="checkbox" name="ids[]" value="{{ $c->id }}" class="bulk-check h-4 w-4 rounded border-slate-300 text-[#E21D2B]"></td>
                    <td class="px-4 py-2.5"><p class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $c->author_name }}</p>
                        <p class="text-[11px] text-slate-500">{{ bn_ago($c->created_at) }}</p>
                        @if($c->guest_email)<p class="truncate text-[10px] text-slate-400">{{ $c->guest_email }}</p>@endif</td>
                    <td class="px-4 py-2.5"><p class="max-w-xs line-clamp-2 text-xs text-slate-600 dark:text-slate-300">{{ $c->body }}</p>
                        @if($c->is_reported)<span class="mt-1 inline-flex items-center gap-1 text-[10px] font-bold text-amber-600"><i class="ph ph-flag"></i> রিপোর্ট করা</span>@endif</td>
                    <td class="px-4 py-2.5 text-[11px]">@if($item)<span class="line-clamp-2 font-bold text-slate-700 dark:text-slate-300">{{ $item->title ?? '—' }}</span>@else<span class="text-slate-400">মুছে গেছে</span>@endif</td>
                    <td class="px-4 py-2.5 text-center">@php $cs=['pending'=>'bg-amber-500','approved'=>'bg-emerald-500','rejected'=>'bg-slate-500','spam'=>'bg-red-600'][$c->status]??'bg-slate-500'; @endphp
                        <span class="mc-badge {{ $cs }} text-white">{{ \App\Models\Comment::STATUSES[$c->status] ?? $c->status }}</span></td>
                    <td class="px-4 py-2.5"><div class="flex flex-wrap items-center justify-end gap-1">
                        @foreach(['approved'=>'ph-check-square text-emerald-600','rejected'=>'ph-x-square text-slate-500','spam'=>'ph-bug text-red-600'] as $st=>$ic)
                            @if($c->status !== $st)
                                <form action="{{ route('admin.comments.status',[$c,$st]) }}" method="POST" class="inline">@csrf
                                    <button type="submit" title="{{ \App\Models\Comment::STATUSES[$st] }}" class="rounded border border-slate-300 p-1.5 hover:bg-slate-100 dark:border-slate-700"><i class="ph {{ $ic }} text-xs"></i></button></form>
                            @endif
                        @endforeach
                        <form action="{{ route('admin.comments.destroy',$c) }}" method="POST" class="inline" data-confirm="মন্তব্যটি স্থায়ীভাবে মুছে ফেলবেন?">@csrf @method('DELETE')
                            <button type="submit" title="মুছুন" class="rounded border border-red-300 p-1.5 text-red-600 hover:bg-red-50 dark:border-red-800"><i class="ph ph-trash-simple text-xs"></i></button></form>
                    </div></td></tr>
            @empty <x-admin.empty-state colspan="6" icon="ph-chat-circle-dots" message="কোনো মন্তব্য নেই" hint="এই ফিল্টারে কোনো মন্তব্য পাওয়া যায়নি।" /> @endforelse
        </tbody></table></div></div>
    {{ $comments->links() }}
</form>
@endsection
@push('scripts')
<script>document.addEventListener('change',function(e){if(e.target.matches('.bulk-check,[data-check-all]')){var n=document.querySelectorAll('.bulk-check:checked').length;var el=document.getElementById('selCount');if(el)el.textContent=n.toLocaleString('bn-BD')}});
document.addEventListener('DOMContentLoaded',function(){var el=document.getElementById('selCount');if(el)el.textContent=document.querySelectorAll('.bulk-check:checked').length.toLocaleString('bn-BD')});</script>
@endpush
