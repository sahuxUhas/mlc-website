{{-- সারির অ্যাকশন বাটন (এডিট/টগল/ডিলিট/রিস্টোর/ভিউ) --}}
@props(['edit'=>null,'view'=>null,'toggle'=>null,'toggleOn'=>false,'destroy'=>null,'restore'=>null,'forceDelete'=>null,'deleteConfirm'=>'মুছে ফেলবেন?'])
<div class="flex items-center gap-1">
    @if($view)<a href="{{ $view }}" target="_blank" title="ওয়েবসাইটে দেখুন" class="rounded border border-slate-300 p-1.5 text-slate-600 hover:border-blue-500 hover:text-blue-600 dark:border-slate-700 dark:text-slate-300"><i class="ph ph-arrow-square-out"></i></a>@endif
    @if($edit)<button type="button" data-mc-modal-open="{{ $edit }}" title="এডিট" class="rounded border border-slate-300 p-1.5 text-slate-600 hover:border-[#E21D2B] hover:text-[#E21D2B] dark:border-slate-700 dark:text-slate-300"><i class="ph ph-pencil-simple"></i></button>@endif
    @if($toggle)
        <form action="{{ $toggle }}" method="POST" class="inline">@csrf
            <button type="submit" title="{{ $toggleOn ? 'বন্ধ করুন' : 'চালু করুন' }}" class="rounded border p-1.5 {{ $toggleOn ? 'border-emerald-300 text-emerald-600 hover:bg-emerald-50 dark:border-emerald-800' : 'border-slate-300 text-slate-400 hover:bg-slate-100 dark:border-slate-700' }}">
                <i class="ph {{ $toggleOn ? 'ph-toggle-on' : 'ph-toggle-off' }}"></i>
            </button>
        </form>
    @endif
    @if($restore)
        <form action="{{ $restore }}" method="POST" class="inline">@csrf
            <button type="submit" title="পুনরুদ্ধার" class="rounded border border-emerald-300 p-1.5 text-emerald-600 hover:bg-emerald-50 dark:border-emerald-800"><i class="ph ph-arrow-counter-clockwise"></i></button>
        </form>
    @endif
    @if($destroy)
        <form action="{{ $destroy }}" method="POST" class="inline" data-confirm="{{ $deleteConfirm }}">@csrf @method('DELETE')
            <button type="submit" title="মুছে ফেলুন" class="rounded border border-red-300 p-1.5 text-red-600 hover:bg-red-50 dark:border-red-800"><i class="ph ph-trash-simple"></i></button>
        </form>
    @endif
    @if($forceDelete)
        <form action="{{ $forceDelete }}" method="POST" class="inline" data-confirm="স্থায়ীভাবে মুছে ফেলবেন? এটি ফেরানো যাবে না।">@csrf @method('DELETE')
            <button type="submit" title="স্থায়ীভাবে মুছুন" class="rounded border border-red-400 bg-red-50 p-1.5 text-red-700 hover:bg-red-100 dark:border-red-800 dark:bg-red-950/40"><i class="ph-fill ph-fire"></i></button>
        </form>
    @endif
</div>
