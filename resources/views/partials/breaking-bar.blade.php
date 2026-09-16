{{-- ===== ব্রেকিং নিউজ বার (অ্যাডমিন → ব্রেকিং নিউজ থেকে নিয়ন্ত্রিত) ===== --}}
@php $items = ($breakingItems ?? collect())->filter(fn($b) => $b->is_enabled); @endphp
@if($items->isNotEmpty())
<div class="ticker-wrap border-b border-red-100 bg-white dark:border-[#263246] dark:bg-[#182233]">
    <div class="container mx-auto flex items-stretch gap-0 px-0 sm:px-4">
        <div class="flex shrink-0 items-center gap-1.5 bg-[#D50E18] px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-white sm:px-4">
            <span class="relative flex h-2 w-2">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-white opacity-75"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-white"></span>
            </span>
            ব্রেকিং
        </div>
        <div class="ticker flex items-center py-1.5 text-[13px] font-semibold text-gray-800 dark:text-[#F1F5F9]">
            @foreach($items as $item)
                <a href="{{ $item->link }}" class="mx-4 inline-flex items-center gap-1.5 whitespace-nowrap hover:text-[#D50E18] dark:hover:text-[#22C55E]">
                    <i class="ph-fill ph-lightning text-[#D50E18] text-xs"></i>{{ $item->title }}
                </a>
            @endforeach
        </div>
    </div>
</div>
@endif
