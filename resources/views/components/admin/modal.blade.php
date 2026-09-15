{{-- জেনেরিক মোডাল — Create/Edit ফর্মের জন্য (id দিয়ে খোলা/বন্ধ করা হয়) --}}
@props(['id', 'title', 'size' => 'md'])
@php $w = ['sm'=>'max-w-md','md'=>'max-w-2xl','lg'=>'max-w-4xl','xl'=>'max-w-6xl'][$size] ?? 'max-w-2xl'; @endphp
<div id="{{ $id }}" class="fixed inset-0 z-[100] hidden items-start justify-center overflow-y-auto bg-black/60 p-4 backdrop-blur-sm" data-mc-modal="{{ $id }}">
    <div class="mc-drop my-8 w-full {{ $w }} rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-[#0F172A]">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3.5 dark:border-slate-800">
            <h2 class="font-serif text-base font-bold text-slate-900 dark:text-white">{{ $title }}</h2>
            <button type="button" data-mc-modal-close="{{ $id }}" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-white" aria-label="বন্ধ">
                <i class="ph-bold ph-x text-lg"></i>
            </button>
        </div>
        <div class="max-h-[72vh] overflow-y-auto px-5 py-4 mc-scroll">{{ $slot }}</div>
    </div>
</div>
