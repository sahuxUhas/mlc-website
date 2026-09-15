@extends('admin.layouts.master')
@section('title','ড্যাশবোর্ড')
@section('content')
@php
$cards = [
  ['label'=>'মোট সংবাদ','value'=>$stats['total_news'],'icon'=>'ph-newspaper','tone'=>'bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400'],
  ['label'=>'প্রকাশিত','value'=>$stats['published'],'icon'=>'ph-check-circle','tone'=>'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400'],
  ['label'=>'খসড়া','value'=>$stats['draft'],'icon'=>'ph-pencil-simple','tone'=>'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'],
  ['label'=>'রিভিউতে','value'=>$stats['pending'],'icon'=>'ph-hourglass-medium','tone'=>'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400'],
  ['label'=>'নির্ধারিত','value'=>$stats['scheduled'],'icon'=>'ph-clock-countdown','tone'=>'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400'],
  ['label'=>'ট্র্যাশ','value'=>$stats['trash'],'icon'=>'ph-trash-simple','tone'=>'bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400'],
  ['label'=>'মোট ভিউ','value'=>$stats['total_views'],'icon'=>'ph-eye','tone'=>'bg-violet-50 text-violet-600 dark:bg-violet-950/40 dark:text-violet-400'],
  ['label'=>'মন্তব্য (অপেক্ষমাণ)','value'=>$stats['comments_pending'],'icon'=>'ph-chats-circle','tone'=>'bg-pink-50 text-pink-600 dark:bg-pink-950/40 dark:text-pink-400','link'=>route('admin.comments.index').'?status=pending'],
  ['label'=>'ভিডিও','value'=>$stats['videos'],'icon'=>'ph-monitor-play','tone'=>'bg-cyan-50 text-cyan-600 dark:bg-cyan-950/40 dark:text-cyan-400'],
  ['label'=>'ঘোষণা','value'=>$stats['announcements'],'icon'=>'ph-megaphone','tone'=>'bg-teal-50 text-teal-600 dark:bg-teal-950/40 dark:text-teal-400'],
  ['label'=>'ক্যাটাগরি','value'=>$stats['categories'],'icon'=>'ph-list-dashes','tone'=>'bg-orange-50 text-orange-600 dark:bg-orange-950/40 dark:text-orange-400'],
  ['label'=>'রিপোর্টার','value'=>$stats['reporters'],'icon'=>'ph-user-focus','tone'=>'bg-lime-50 text-lime-600 dark:bg-lime-950/40 dark:text-lime-400'],
];
@endphp

{{-- Quick Actions --}}
<div class="mb-6 flex flex-wrap items-center gap-2">
    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">দ্রুত অ্যাকশন:</span>
    @if(auth()->user()->can_manage('news.create'))<a href="{{ route('admin.news.create') }}" class="mc-chip dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"><i class="ph ph-plus"></i> নতুন সংবাদ</a>@endif
    @if(auth()->user()->can_manage('categories.manage'))<a href="{{ route('admin.categories.index') }}" class="mc-chip dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"><i class="ph ph-list-dashes"></i> ক্যাটাগরি</a>@endif
    @if(auth()->user()->can_manage('media.upload'))<a href="{{ route('admin.media.index') }}" class="mc-chip dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"><i class="ph ph-image-square"></i> মিডিয়া</a>@endif
    @if(auth()->user()->can_manage('comments.moderate'))<a href="{{ route('admin.comments.index') }}" class="mc-chip dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"><i class="ph ph-chats-circle"></i> মন্তব্য</a>@endif
    @if(auth()->user()->can_manage('settings.manage'))<a href="{{ route('admin.settings.edit') }}" class="mc-chip dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"><i class="ph ph-gear"></i> সেটিংস</a>@endif
</div>

{{-- স্ট্যাট কার্ড --}}
<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
    @foreach($cards as $c)
        @php $url = $c['link'] ?? null; @endphp
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-[#0F172A]">
            <div class="flex items-center justify-between">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg {{ $c['tone'] }}"><i class="ph-fill {{ $c['icon'] }} text-lg"></i></span>
                <span class="font-serif text-xl font-black text-slate-900 dark:text-white">{{ bn_count($c['value']) }}</span>
            </div>
            <p class="mt-2 text-[11px] font-bold text-slate-500 dark:text-slate-400">
                @if($url)<a href="{{ $url }}" class="hover:text-[#E21D2B]">{{ $c['label'] }}</a>@else{{ $c['label'] }}@endif
            </p>
        </div>
    @endforeach
</div>

<div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    {{-- সাম্প্রতিক সংবাদ --}}
    <div class="xl:col-span-2 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-[#0F172A]">
        <div class="mb-3 flex items-center justify-between border-b border-slate-200 pb-2 dark:border-slate-800">
            <h2 class="font-serif text-sm font-bold dark:text-white"><i class="ph ph-newspaper text-[#E21D2B]"></i> সাম্প্রতিক সংবাদ</h2>
            <a href="{{ route('admin.news.index') }}" class="text-[11px] font-bold text-[#E21D2B] hover:underline">সব দেখুন →</a>
        </div>
        <div class="space-y-2">
            @forelse($recentNews as $p)
                <div class="flex items-center gap-3 rounded-lg p-2 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    @if($p->featured_image)<img src="{{ str_starts_with($p->featured_image,'http') ? $p->featured_image : asset('uploads/'.$p->featured_image) }}" class="h-11 w-16 shrink-0 rounded object-cover" alt="">@else<span class="flex h-11 w-16 shrink-0 items-center justify-center rounded bg-slate-100 dark:bg-slate-800"><i class="ph ph-image text-slate-400"></i></span>@endif
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('admin.news.edit',$p) }}" class="line-clamp-1 text-[13px] font-bold text-slate-800 hover:text-[#E21D2B] dark:text-slate-200">{{ $p->title }}</a>
                        <p class="mt-0.5 flex items-center gap-2 text-[10px] text-slate-500">
                            <span class="rounded bg-slate-100 px-1.5 py-0.5 font-bold dark:bg-slate-800">{{ $p->category?->name ?? '—' }}</span>
                            <span class="mc-status-{{ $p->status }}">{{ \App\Models\Post::STATUSES[$p->status] ?? $p->status }}</span>
                            <span><i class="ph ph-eye"></i> {{ bn_count($p->views) }}</span>
                            <span>{{ bn_ago($p->created_at) }}</span>
                        </p>
                    </div>
                </div>
            @empty<p class="py-6 text-center text-sm text-slate-500">এখনও কোনো সংবাদ নেই।</p>@endforelse
        </div>
    </div>

    <div class="space-y-5">
        {{-- নির্ধারিত সংবাদ --}}
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-[#0F172A]">
            <h2 class="mb-3 border-b border-slate-200 pb-2 font-serif text-sm font-bold dark:border-slate-800 dark:text-white"><i class="ph ph-clock-countdown text-indigo-500"></i> নির্ধারিত সংবাদ</h2>
            <ul class="space-y-2">
                @forelse($scheduledNews as $p)
                    <li><a href="{{ route('admin.news.edit',$p) }}" class="block rounded-lg bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-indigo-50 dark:bg-slate-800 dark:text-slate-300">
                        <span class="line-clamp-1">{{ $p->title }}</span>
                        <span class="mt-0.5 block text-[10px] font-normal text-indigo-600 dark:text-indigo-400"><i class="ph ph-calendar"></i> {{ bn_date($p->scheduled_at) }}</span>
                    </a></li>
                @empty<li class="py-3 text-center text-xs text-slate-500">কোনো নির্ধারিত সংবাদ নেই।</li>@endforelse
            </ul>
        </div>

        {{-- সাম্প্রতিক মন্তব্য --}}
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-[#0F172A]">
            <div class="mb-3 flex items-center justify-between border-b border-slate-200 pb-2 dark:border-slate-800">
                <h2 class="font-serif text-sm font-bold dark:text-white"><i class="ph ph-chats-circle text-pink-500"></i> সাম্প্রতিক মন্তব্য</h2>
                <a href="{{ route('admin.comments.index') }}" class="text-[11px] font-bold text-[#E21D2B] hover:underline">সব →</a>
            </div>
            <ul class="space-y-2">
                @forelse($recentComments as $c)
                    <li class="rounded-lg bg-slate-50 px-3 py-2 text-xs dark:bg-slate-800">
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $c->guest_name }}</span>
                        <span class="mc-status-{{ $c->status }} mr-1 rounded px-1 text-[9px] font-black">{{ \App\Models\Comment::STATUSES[$c->status] }}</span>
                        <p class="mt-0.5 line-clamp-2 text-slate-600 dark:text-slate-400">{{ $c->body }}</p>
                    </li>
                @empty<li class="py-3 text-center text-xs text-slate-500">কোনো মন্তব্য নেই।</li>@endforelse
            </ul>
        </div>

        {{-- সর্বাধিক পঠিত --}}
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-[#0F172A]">
            <h2 class="mb-3 border-b border-slate-200 pb-2 font-serif text-sm font-bold dark:border-slate-800 dark:text-white"><i class="ph ph-fire text-orange-500"></i> সর্বাধিক পঠিত</h2>
            <ol class="space-y-2">
                @forelse($mostRead as $i => $p)
                    <li class="flex items-start gap-2 text-xs">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded bg-[#E21D2B] text-[10px] font-black text-white">{{ bn_num($i+1) }}</span>
                        <a href="{{ route('news.show',$p->slug) }}" target="_blank" class="line-clamp-2 font-semibold text-slate-700 hover:text-[#E21D2B] dark:text-slate-300">{{ $p->title }}</a>
                        <span class="mr-auto shrink-0 text-[10px] text-slate-400">{{ bn_count($p->views) }}</span>
                    </li>
                @empty<li class="py-3 text-center text-xs text-slate-500">কোনো তথ্য নেই।</li>@endforelse
            </ol>
        </div>

        {{-- অ্যাক্টিভিটি --}}
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-[#0F172A]">
            <div class="mb-3 flex items-center justify-between border-b border-slate-200 pb-2 dark:border-slate-800">
                <h2 class="font-serif text-sm font-bold dark:text-white"><i class="ph ph-clock-counter-clockwise text-slate-500"></i> সাম্প্রতিক অ্যাক্টিভিটি</h2>
                <a href="{{ route('admin.activity.index') }}" class="text-[11px] font-bold text-[#E21D2B] hover:underline">সব →</a>
            </div>
            <ul class="space-y-1.5">
                @forelse($activities as $a)
                    <li class="text-[11px] text-slate-600 dark:text-slate-400">
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $a->user_name }}</span>
                        — {{ $a->description ?: $a->action.' ('.$a->module.')' }}
                        <span class="block text-[10px] text-slate-400">{{ bn_ago($a->created_at) }}</span>
                    </li>
                @empty<li class="py-3 text-center text-xs text-slate-500">কোনো লগ নেই।</li>@endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
