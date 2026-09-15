<li data-sortable-item data-sortable-id="{{ $m->id }}" class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-800 {{ $level ? 'ml-6 border-dashed' : '' }}">
    <div class="flex gap-1"><button type="button" data-move="up" title="উপরে" class="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-700"><i class="ph-bold ph-arrow-up text-xs"></i></button>
        <button type="button" data-move="down" title="নিচে" class="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-700"><i class="ph-bold ph-arrow-down text-xs"></i></button></div>
    @if($m->icon)<i class="ph {{ $m->icon }} text-sm text-[#E21D2B]"></i>@endif
    <div class="min-w-0 flex-1"><p class="truncate text-xs font-bold text-slate-800 dark:text-slate-200">{{ $m->label }}</p>
        <code class="truncate text-[10px] text-slate-400" dir="ltr">{{ $m->href }}</code></div>
    @if(!$m->is_enabled)<span class="mc-badge bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300">বন্ধ</span>@endif
    @if($m->open_in_new_tab)<i class="ph ph-arrow-square-out text-[11px] text-slate-400" title="নতুন ট্যাব"></i>@endif
    <div class="flex gap-1"><button type="button" data-mc-modal-open="editMenu-{{ $m->id }}" title="এডিট" class="rounded border border-slate-300 p-1 text-slate-500 hover:border-[#E21D2B] hover:text-[#E21D2B] dark:border-slate-700"><i class="ph ph-pencil-simple text-xs"></i></button>
        <form action="{{ route('admin.menus.destroy',$m) }}" method="POST" data-confirm="মেনু আইটেম ‘{{ $m->label }}’ মুছে ফেলবেন?">@csrf @method('DELETE')
            <button type="submit" title="মুছুন" class="rounded border border-red-300 p-1 text-red-600 hover:bg-red-50 dark:border-red-800"><i class="ph ph-trash-simple text-xs"></i></button></form></div>
</li>
