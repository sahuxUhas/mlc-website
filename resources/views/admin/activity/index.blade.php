@extends('admin.layouts.master')
@section('title','অ্যাক্টিভিটি লগ')
@section('content')
<x-admin.page-head title="অ্যাক্টিভিটি লগ" subtitle="লগইন, সংবাদ তৈরি/প্রকাশ/মুছে ফেলা, সেটিংস পরিবর্তন ও মন্তব্য মডারেশনের পূর্ণ ইতিহাস" />
<x-admin.filter-bar :action="route('admin.activity.index')" :fields="[
    ['name'=>'q','placeholder'=>'বিবরণ খুঁজুন…'],
    ['name'=>'module','type'=>'select','placeholder'=>'সব মডিউল','options'=>$modules->filter()->mapWithKeys(fn($m)=>[$m=>$m])->all()],
    ['name'=>'action','type'=>'select','placeholder'=>'সব অ্যাকশন','options'=>$actions->filter()->mapWithKeys(fn($a)=>[$a=>$a])->all()],
    ['name'=>'user_id','type'=>'select','placeholder'=>'সব ব্যবহারকারী','options'=>$users->pluck('name','id')->all()]]" />

@if(auth()->user()->isSuperAdmin())
    <form action="{{ route('admin.activity.clear') }}" method="POST" class="mb-4" data-confirm="পুরো অ্যাক্টিভিটি লগ স্থায়ীভাবে মুছে ফেলবেন? এটি ফেরানো যাবে না।">
        @csrf @method('DELETE')
        <button type="submit" class="mc-btn mc-btn-danger text-xs"><i class="ph ph-broom"></i> সব লগ মুছে ফেলুন (শুধু সুপার অ্যাডমিন)</button>
    </form>
@endif

<div class="mc-card overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left text-sm">
    <thead class="bg-slate-50 text-[11px] uppercase text-slate-500 dark:bg-slate-800/60 dark:text-slate-400"><tr>
        <th class="px-4 py-3">সময়</th><th class="px-4 py-3">ব্যবহারকারী</th><th class="px-4 py-3">অ্যাকশন</th>
        <th class="px-4 py-3">বিবরণ</th><th class="px-4 py-3">মডিউল</th><th class="px-4 py-3">IP</th></tr></thead>
    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        @forelse($logs as $log)
            @php
                $icon = ['login'=>'ph-sign-in text-emerald-600','logout'=>'ph-sign-out text-slate-500','created'=>'ph-plus-circle text-emerald-600',
                    'updated'=>'ph-pencil-simple text-blue-600','deleted'=>'ph-trash text-red-600','restored'=>'ph-arrow-counter-clockwise text-emerald-600',
                    'published'=>'ph-paper-plane-tilt text-emerald-600','settings'=>'ph-gear-six text-purple-600','moderate'=>'ph-shield-check text-amber-600'][$log->action] ?? 'ph-info text-slate-500';
            @endphp
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                <td class="whitespace-nowrap px-4 py-2.5 text-[11px] text-slate-500">{{ bn_date($log->created_at) }}<span class="block text-slate-400">{{ bn_num(\Illuminate\Support\Carbon::parse($log->created_at)->format('h:i A')) }}</span></td>
                <td class="px-4 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-200">{{ $log->user->name ?? 'সিস্টেম' }}</td>
                <td class="px-4 py-2.5"><span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-slate-600 dark:text-slate-300"><i class="ph {{ $icon }}"></i> {{ $log->action }}</span></td>
                <td class="px-4 py-2.5"><p class="max-w-md text-xs text-slate-600 dark:text-slate-300">{{ $log->description }}</p></td>
                <td class="px-4 py-2.5"><span class="mc-badge bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $log->module }}</span></td>
                <td class="px-4 py-2.5"><code class="text-[10px] text-slate-400" dir="ltr">{{ $log->ip_address }}</code></td></tr>
        @empty <x-admin.empty-state colspan="6" icon="ph-clock-counter-clockwise" message="কোনো অ্যাক্টিভিটি রেকর্ড নেই" hint="ফিল্টার পরিবর্তন করে আবার চেষ্টা করুন।" /> @endforelse
    </tbody></table></div></div>
{{ $logs->links() }}
@endsection
