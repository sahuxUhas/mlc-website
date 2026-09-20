@extends('admin.layouts.master')
@section('title','সংবাদ ব্যবস্থাপনা')
@section('content')
@php
$tabs = [['all','সব','bg-slate-100 text-slate-700'],['published','প্রকাশিত','bg-emerald-50 text-emerald-700'],['draft','খসড়া','bg-slate-100 text-slate-700'],['pending','রিভিউতে','bg-amber-50 text-amber-700'],['scheduled','নির্ধারিত','bg-indigo-50 text-indigo-700'],['archived','আর্কাইভ','bg-violet-50 text-violet-700'],['trash','ট্র্যাশ','bg-red-50 text-red-700']];
@endphp

<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex flex-wrap gap-1.5">
        @foreach($tabs as [$key,$label,$tone])
            <a href="{{ route('admin.news.index', array_merge($filters, ['status'=>$key])) }}"
               class="rounded-lg px-3 py-1.5 text-xs font-bold transition-colors {{ $status===$key ? 'bg-[#E21D2B] text-white' : $tone.' hover:opacity-80 dark:bg-slate-800 dark:text-slate-300' }}">
                {{ $label }} <span class="mr-1 opacity-70">{{ bn_num($counts[$key] ?? 0) }}</span>
            </a>
        @endforeach
    </div>
    @if(auth()->user()->can_manage('news.create'))
        <a href="{{ route('admin.news.create') }}" class="rounded-lg bg-[#E21D2B] px-4 py-2 text-xs font-bold text-white hover:bg-[#B9121E]"><i class="ph ph-plus"></i> নতুন সংবাদ</a>
    @endif
</div>

{{-- ফিল্টার + সার্চ --}}
<form method="GET" class="mb-4 grid grid-cols-2 gap-2 rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-[#0F172A] md:grid-cols-6">
    <input type="hidden" name="status" value="{{ $status }}">
    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="শিরোনাম খুঁজুন…" class="col-span-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs focus:border-[#E21D2B] dark:border-slate-700 dark:bg-[#0B1120] dark:text-white">
    <select name="category" class="rounded-lg border border-slate-300 bg-white px-2 py-2 text-xs dark:border-slate-700 dark:bg-[#0B1120] dark:text-white">
        <option value="">সব বিভাগ</option>
        @foreach($categories as $c)<option value="{{ $c->id }}" @selected(($filters['category'] ?? '')==$c->id)>{{ $c->name }}</option>@endforeach
    </select>
    <select name="reporter" class="rounded-lg border border-slate-300 bg-white px-2 py-2 text-xs dark:border-slate-700 dark:bg-[#0B1120] dark:text-white">
        <option value="">সব রিপোর্টার</option>
        @foreach($reporters as $r)<option value="{{ $r->id }}" @selected(($filters['reporter'] ?? '')==$r->id)>{{ $r->name }}</option>@endforeach
    </select>
    <select name="sort" class="rounded-lg border border-slate-300 bg-white px-2 py-2 text-xs dark:border-slate-700 dark:bg-[#0B1120] dark:text-white">
        @foreach(['newest'=>'নতুন আগে','oldest'=>'পুরোনো আগে','views'=>'সর্বাধিক ভিউ','title'=>'শিরোনাম (ক-হ)','status'=>'স্ট্যাটাস'] as $k=>$l)
            <option value="{{ $k }}" @selected(($filters['sort'] ?? 'newest')===$k)>{{ $l }}</option>
        @endforeach
    </select>
    <button type="submit" class="rounded-lg bg-slate-800 px-3 py-2 text-xs font-bold text-white hover:bg-[#E21D2B] dark:bg-slate-700"><i class="ph ph-funnel"></i> ফিল্টার</button>
</form>

{{-- বাল্ক অ্যাকশন + টেবিল --}}
<form method="POST" action="{{ route('admin.news.bulk') }}" id="bulkForm">
    @csrf
    <div class="mb-3 flex flex-wrap items-center gap-2">
        <select name="action" class="rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs dark:border-slate-700 dark:bg-[#0F172A] dark:text-white">
            <option value="">বাল্ক অ্যাকশন…</option>
            <option value="publish">প্রকাশ করুন</option>
            <option value="unpublish">আনপাবলিশ (খসড়া)</option>
            <option value="archive">আর্কাইভ</option>
            @if($status!=='trash')<option value="trash">ট্র্যাশে পাঠান</option>@endif
            @if($status==='trash')<option value="restore">পুনরুদ্ধার</option><option value="delete">স্থায়ীভাবে মুছুন</option>@endif
        </select>
        <button type="submit" class="rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-bold text-white hover:bg-[#E21D2B] dark:bg-slate-700">প্রয়োগ করুন</button>
        <span class="text-[11px] text-slate-500">মোট {{ bn_count($posts->total()) }}টি</span>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-[#0F172A]">
        <table class="w-full text-right text-xs">
            <thead class="border-b border-slate-200 bg-slate-50 text-[11px] uppercase text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                <tr>
                    <th class="px-3 py-2.5"><input type="checkbox" id="checkAll" data-check-all=".bulk-check" class="h-4 w-4 rounded border-slate-300 text-[#E21D2B]"></th>
                    <th class="px-3 py-2.5">শিরোনাম</th>
                    <th class="px-3 py-2.5">বিভাগ</th>
                    <th class="px-3 py-2.5">রিপোর্টার</th>
                    <th class="px-3 py-2.5">স্ট্যাটাস</th>
                    <th class="px-3 py-2.5">ভিউ</th>
                    <th class="px-3 py-2.5">তারিখ</th>
                    <th class="px-3 py-2.5">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($posts as $post)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                        <td class="px-3 py-2.5"><input type="checkbox" name="ids[]" value="{{ $post->id }}" class="h-4 w-4 rounded border-slate-300 text-[#E21D2B] bulk-check"></td>
                        <td class="max-w-[300px] px-3 py-2.5">
                            <div class="flex items-start gap-2">
                                {{-- থাম্বনেইল (raw URL নয় — signed proxy URL) --}}
                                <img src="{{ $post->featured_thumb }}" alt="{{ $post->title }}" loading="lazy"
                                     class="hidden h-10 w-14 shrink-0 rounded-md border border-slate-200 object-cover dark:border-slate-700 sm:block"
                                     onerror="this.onerror=null;this.src='{{ mc_placeholder_svg() }}'">
                                <div class="min-w-0">
                                    <a href="{{ route('admin.news.edit',$post) }}" class="line-clamp-2 font-bold text-slate-800 hover:text-[#E21D2B] dark:text-slate-200">{{ $post->title }}</a>
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @if($post->is_featured)<span class="rounded bg-amber-100 px-1 text-[9px] font-bold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">হোমপেজ</span>@endif
                                        @if($post->is_breaking)<span class="rounded bg-red-100 px-1 text-[9px] font-bold text-red-700 dark:bg-red-900/40 dark:text-red-300">ব্রেকিং</span>@endif
                                        @if($post->images_count ?? false)<span class="rounded bg-slate-100 px-1 text-[9px] font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ bn_num($post->images_count) }} ছবি</span>@endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400">{{ $post->category?->name ?? '—' }}</td>
                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400">{{ $post->reporter?->name ?? '—' }}</td>
                        <td class="px-3 py-2.5"><span class="mc-status-{{ $post->status }}">{{ \App\Models\Post::STATUSES[$post->status] }}</span></td>
                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400">{{ bn_count($post->views) }}</td>
                        <td class="px-3 py-2.5 text-slate-500">{{ bn_date($post->published_at ?? $post->created_at, false) }}</td>
                        <td class="px-3 py-2.5">
                            <div class="flex items-center gap-1">
                                @if($status==='trash')
                                    <button type="button" onclick="document.getElementById('restore-{{ $post->id }}').submit()" class="rounded border border-emerald-300 p-1.5 text-emerald-600 hover:bg-emerald-50 dark:border-emerald-800" title="পুনরুদ্ধার"><i class="ph ph-arrow-counter-clockwise"></i></button>
                                @else
                                    <a href="{{ route('admin.news.edit',$post) }}" class="rounded border border-slate-300 p-1.5 text-slate-600 hover:border-[#E21D2B] hover:text-[#E21D2B] dark:border-slate-700 dark:text-slate-300" title="এডিট"><i class="ph ph-pencil-simple"></i></a>
                                    @if($post->status!=='published' && auth()->user()->can_manage('news.publish'))
                                        <button type="button" onclick="document.getElementById('pub-{{ $post->id }}').submit()" class="rounded border border-emerald-300 p-1.5 text-emerald-600 hover:bg-emerald-50 dark:border-emerald-800" title="প্রকাশ"><i class="ph ph-paper-plane-tilt"></i></button>
                                    @elseif($post->status==='published' && auth()->user()->can_manage('news.publish'))
                                        <button type="button" onclick="document.getElementById('unpub-{{ $post->id }}').submit()" class="rounded border border-amber-300 p-1.5 text-amber-600 hover:bg-amber-50 dark:border-amber-800" title="আনপাবলিশ"><i class="ph ph-eye-slash"></i></button>
                                    @endif
                                    <a href="{{ route('admin.news.preview',$post) }}" target="_blank" rel="noopener" class="rounded border border-slate-300 p-1.5 text-slate-600 hover:border-amber-500 hover:text-amber-600 dark:border-slate-700 dark:text-slate-300" title="প্রাকদর্শন"><i class="ph ph-eye"></i></a>
                                    <a href="{{ route('news.show',$post->slug) }}" target="_blank" rel="noopener" class="rounded border border-slate-300 p-1.5 text-slate-600 hover:border-blue-500 hover:text-blue-600 dark:border-slate-700 dark:text-slate-300" title="লাইভ পেজ"><i class="ph ph-arrow-square-out"></i></a>
                                    @if(auth()->user()->can_manage('news.delete'))
                                        <button type="button" onclick="if(confirm('ট্র্যাশে পাঠাবেন?'))document.getElementById('del-{{ $post->id }}').submit()" class="rounded border border-red-300 p-1.5 text-red-600 hover:bg-red-50 dark:border-red-800" title="ট্র্যাশ"><i class="ph ph-trash-simple"></i></button>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    {{-- লুকানো ফর্ম (স্ট্যাটাস পরিবর্তন/ট্র্যাশ/রিস্টোর) --}}
                    @if($status!=='trash')
                        <form id="del-{{ $post->id }}" action="{{ route('admin.news.destroy',$post) }}" method="POST" class="hidden">@csrf @method('DELETE')</form>
                        @if(auth()->user()->can_manage('news.publish'))
                            <form id="pub-{{ $post->id }}" action="{{ route('admin.news.status',[$post,'published']) }}" method="POST" class="hidden">@csrf</form>
                            <form id="unpub-{{ $post->id }}" action="{{ route('admin.news.status',[$post,'draft']) }}" method="POST" class="hidden">@csrf</form>
                        @endif
                    @else
                        <form id="restore-{{ $post->id }}" action="{{ route('admin.trash.restore',['news',$post->id]) }}" method="POST" class="hidden">@csrf</form>
                    @endif
                @empty
                    <tr><td colspan="8" class="px-3 py-12 text-center text-sm text-slate-500">
                        <i class="ph ph-newspaper-clipping text-3xl"></i><p class="mt-2 font-bold">কোনো সংবাদ পাওয়া যায়নি</p>
                        @if(auth()->user()->can_manage('news.create'))<a href="{{ route('admin.news.create') }}" class="mt-3 inline-block rounded-lg bg-[#E21D2B] px-4 py-2 text-xs font-bold text-white">প্রথম সংবাদ যোগ করুন</a>@endif
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</form>
<div class="mt-4">{{ $posts->links() }}</div>
@endsection
