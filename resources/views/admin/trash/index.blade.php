@extends('admin.layouts.master')
@section('title','ট্র্যাশ')
@section('content')
<x-admin.page-head title="ট্র্যাশ / রিসাইকেল বিন" subtitle="মুছে ফেলা কনটেন্ট পুনরুদ্ধার বা স্থায়ীভাবে ধ্বংস করুন" />

<div class="mb-4 flex flex-wrap gap-2">
    @foreach($kinds as $key=>$kind)
        @php $n = $counts[$key] ?? 0; @endphp
        <a href="{{ route('admin.trash.index',['type'=>$key]) }}"
           class="rounded-full px-3 py-1.5 text-xs font-bold transition {{ $type===$key ? 'bg-[#E21D2B] text-white' : 'bg-white text-slate-600 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300' }}">
            {{ $kind['label'] }} <span class="{{ $type===$key ? 'text-white/80' : 'text-slate-400' }}">{{ bn_count($n) }}</span>
        </a>
    @endforeach
</div>

<div class="mb-4 flex flex-wrap items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5 dark:border-amber-900 dark:bg-amber-950/30">
    <i class="ph ph-warning-circle text-amber-600"></i>
    <p class="flex-1 text-[11px] font-semibold text-amber-800 dark:text-amber-200">
        <strong>{{ $kinds[$type]['label'] ?? $type }}</strong> — মোট {{ bn_count($items->total()) }}টি আইটেম ট্র্যাশে আছে। “স্থায়ীভাবে মুছুন” দিলে আর ফেরানো যাবে না।
    </p>
    @if($items->total() > 0)
        <form action="{{ route('admin.trash.empty',$type) }}" method="POST" data-confirm="{{ $kinds[$type]['label'] ?? '' }} এর পুরো ট্র্যাশ স্থায়ীভাবে খালি করবেন? এটি ফেরানো যাবে না!">
            @csrf @method('DELETE')
            <button type="submit" class="mc-btn mc-btn-danger text-xs"><i class="ph ph-fire"></i> ট্র্যাশ খালি করুন</button>
        </form>
    @endif
</div>

<div class="mc-card overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left text-sm">
    <thead class="bg-slate-50 text-[11px] uppercase text-slate-500 dark:bg-slate-800/60 dark:text-slate-400"><tr>
        <th class="px-4 py-3">শিরোনাম / নাম</th><th class="px-4 py-3">স্লাগ</th><th class="px-4 py-3">মুছে ফেলার তারিখ</th><th class="px-4 py-3 text-right">অ্যাকশন</th></tr></thead>
    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        @forelse($items as $item)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                <td class="px-4 py-2.5"><p class="max-w-sm truncate font-serif text-sm font-bold text-slate-900 dark:text-white">{{ $item->title ?? $item->name ?? $item->file_name ?? '—' }}</p>
                    @if($item->excerpt ?? false)<p class="line-clamp-1 text-[11px] text-slate-500">{{ mc_excerpt($item->excerpt,60) }}</p>@endif</td>
                <td class="px-4 py-2.5"><code class="text-[11px] text-slate-500" dir="ltr">{{ $item->slug ?? $item->path ?? '—' }}</code></td>
                <td class="px-4 py-2.5 text-xs text-slate-600 dark:text-slate-300">{{ $item->deleted_at ? bn_date($item->deleted_at).' · '.bn_num(\Illuminate\Support\Carbon::parse($item->deleted_at)->format('h:i A')) : '—' }}</td>
                <td class="px-4 py-2.5"><div class="flex items-center justify-end gap-1">
                    <form action="{{ route('admin.trash.restore',[$type,$item->id]) }}" method="POST" class="inline">@csrf
                        <button type="submit" class="mc-btn mc-btn-ghost text-xs"><i class="ph ph-arrow-counter-clockwise"></i> পুনরুদ্ধার</button></form>
                    <form action="{{ url('admin/trash/'.$type.'/'.$item->id) }}" method="POST" class="inline" data-confirm="স্থায়ীভাবে মুছে ফেলবেন? এটি ফেরানো যাবে না।">@csrf @method('DELETE')
                        <button type="submit" class="mc-btn mc-btn-danger text-xs"><i class="ph-fill ph-fire"></i> স্থায়ীভাবে মুছুন</button></form>
                </div></td></tr>
        @empty <x-admin.empty-state colspan="4" icon="ph-trash" message="ট্র্যাশ খালি" hint="এই ক্যাটাগরিতে মুছে ফেলা কোনো আইটেম নেই।" /> @endforelse
    </tbody></table></div></div>
{{ $items->links() }}
@endsection
