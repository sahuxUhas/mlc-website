{{-- ===== সার্চ ওভারলে (🔍) — লাইভ সাজেশন সহ ===== --}}
<div id="mc-search-overlay" class="fixed inset-0 z-[90] hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" data-mc-search-close></div>
    <div class="mc-drop relative mx-auto mt-[8vh] w-[94%] max-w-2xl rounded-2xl border border-gray-200 bg-white p-4 shadow-2xl dark:border-[#263246] dark:bg-[#182233] sm:p-5">
        <form action="{{ route('search') }}" method="GET" class="flex items-center gap-2">
            <div class="relative flex-1">
                <i class="ph ph-magnifying-glass pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-lg text-gray-400"></i>
                <input type="search" name="q" id="mc-search-input" autocomplete="off"
                       placeholder="সংবাদ খুঁজুন…"
                       class="w-full rounded-xl border border-gray-300 bg-white py-2.5 pl-3 pr-10 text-sm text-gray-900 outline-none focus:border-[#D50E18] focus:ring-2 focus:ring-[#D50E18]/30 dark:border-[#263246] dark:bg-[#0D1422] dark:text-[#F1F5F9]">
            </div>
            <button type="submit" class="rounded-xl bg-[#D50E18] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#B9121E]">খুঁজুন</button>
            <button type="button" data-mc-search-close class="mc-icon-btn dark:border-[#263246] dark:bg-[#182233] dark:text-[#F1F5F9]" aria-label="বন্ধ করুন">
                <i class="ph-bold ph-x text-[20px]"></i>
            </button>
        </form>

        <div id="mc-search-results" class="mt-3 max-h-[52vh] space-y-2 overflow-y-auto mc-scroll"></div>

        <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-gray-100 pt-3 dark:border-[#263246]">
            <span class="text-[11px] font-bold text-gray-500 dark:text-[#94A3B8]">জনপ্রিয় বিভাগ:</span>
            @foreach(($navCategories ?? collect())->take(6) as $category)
                <a href="{{ route('category.show', $category->slug) }}" class="mc-chip dark:border-gray-600 dark:bg-[#0D1422] dark:text-gray-300">{{ $category->name }}</a>
            @endforeach
        </div>
    </div>
</div>
