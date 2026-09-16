@props(['icon'=>'ph-folder-open','message'=>'কোনো তথ্য পাওয়া যায়নি','hint'=>null,'colspan'=>null])
@if($colspan)
    <tr><td colspan="{{ $colspan }}" class="px-4 py-14 text-center">
        <i class="ph {{ $icon }} text-4xl text-slate-300 dark:text-slate-600"></i>
        <p class="mt-2 text-sm font-bold text-slate-600 dark:text-slate-300">{{ $message }}</p>
        @if($hint)<p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>@endif
        <div class="mt-3 flex justify-center">{{ $slot ?? '' }}</div>
    </td></tr>
@else
    <div class="rounded-xl border border-dashed border-slate-300 bg-white px-4 py-14 text-center dark:border-slate-700 dark:bg-[#0F172A]">
        <i class="ph {{ $icon }} text-4xl text-slate-300 dark:text-slate-600"></i>
        <p class="mt-2 text-sm font-bold text-slate-600 dark:text-slate-300">{{ $message }}</p>
        @if($hint)<p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>@endif
        <div class="mt-3 flex justify-center gap-2">{{ $slot ?? '' }}</div>
    </div>
@endif
