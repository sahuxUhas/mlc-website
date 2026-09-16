@props(['name','label','checked'=>false,'hint'=>null])
<label class="flex cursor-pointer items-start gap-2.5 rounded-lg border border-slate-200 bg-slate-50 p-3 hover:border-[#E21D2B]/40 dark:border-slate-800 dark:bg-slate-800/40">
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" value="1" @checked(filter_var(old($name, $checked), FILTER_VALIDATE_BOOLEAN))
           class="mt-0.5 h-4 w-4 rounded border-slate-300 text-[#E21D2B] focus:ring-[#E21D2B]">
    <span class="min-w-0">
        <span class="block text-xs font-bold text-slate-800 dark:text-slate-200">{{ $label }}</span>
        @if($hint)<span class="mt-0.5 block text-[11px] font-normal text-slate-500">{{ $hint }}</span>@endif
    </span>
</label>
