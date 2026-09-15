@extends('admin.layouts.master')
@section('title','যোগাযোগ বার্তা')
@section('content')
<x-admin.page-head title="যোগাযোগ বার্তা" subtitle="ওয়েবসাইটের যোগাযোগ ফর্ম থেকে আসা বার্তা" />
<div class="mb-4 flex items-center gap-2">
    <span class="mc-badge bg-[#E21D2B] text-white">অপঠিত {{ bn_count($unread) }}</span>
    <span class="text-[11px] text-slate-500">মোট {{ bn_count($messages->total()) }}টি বার্তা</span>
</div>
<x-admin.filter-bar :action="route('admin.messages.index')" :fields="[
    ['name'=>'q','placeholder'=>'নাম, ইমেইল বা বিষয় খুঁজুন…'],
    ['name'=>'read','type'=>'select','placeholder'=>'সব বার্তা','options'=>['0'=>'অপঠিত','1'=>'পঠিত']]]" />
<div class="space-y-3">
    @forelse($messages as $m)
        <div class="mc-card p-4 {{ $m->is_read ? '' : 'border-l-4 border-l-[#E21D2B]' }}">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="font-serif text-sm font-bold text-slate-900 dark:text-white">{{ $m->name }}</p>
                        @if(!$m->is_read)<span class="mc-badge bg-[#E21D2B] text-white">নতুন</span>@endif
                        <code class="text-[11px] text-slate-500">{{ $m->email }}</code>
                        @if($m->phone)<code class="text-[11px] text-slate-500" dir="ltr">{{ $m->phone }}</code>@endif
                    </div>
                    @if($m->subject)<p class="mt-1 text-xs font-bold text-slate-700 dark:text-slate-300">বিষয়: {{ $m->subject }}</p>@endif
                    <p class="mt-1.5 text-xs leading-relaxed text-slate-600 dark:text-slate-300">{{ $m->message }}</p>
                    <p class="mt-2 text-[11px] text-slate-400"><i class="ph ph-clock"></i> {{ bn_date($m->created_at) }} · {{ bn_ago($m->created_at) }} · IP <code dir="ltr">{{ $m->ip_address }}</code></p>
                </div>
                <div class="flex shrink-0 gap-1">
                    @if($m->email)<a href="mailto:{{ $m->email }}?subject=Re: {{ urlencode($m->subject ?: 'যোগাযোগ') }}" class="mc-btn mc-btn-ghost text-xs"><i class="ph ph-reply"></i> উত্তর</a>@endif
                    <form action="{{ route('admin.messages.destroy',$m) }}" method="POST" data-confirm="বার্তাটি মুছে ফেলবেন?">@csrf @method('DELETE')
                        <button type="submit" class="rounded border border-red-300 p-1.5 text-red-600 hover:bg-red-50 dark:border-red-800" title="মুছুন"><i class="ph ph-trash-simple text-xs"></i></button></form>
                </div>
            </div>
        </div>
    @empty <x-admin.empty-state icon="ph-envelope-simple" message="কোনো বার্তা নেই" hint="যোগাযোগ ফর্ম থেকে বার্তা এলে এখানে দেখা যাবে।" /> @endforelse
</div>
{{ $messages->links() }}
@endsection
