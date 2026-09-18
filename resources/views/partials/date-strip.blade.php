{{-- ===== তারিখ / অবস্থান / লাইভ সময় (অ্যাডমিন → সাইট সেটিংস → হেডার থেকে চালু/বন্ধ) ===== --}}
@if(mc_flag('date_strip_enabled'))
<div class="border-b border-gray-200 bg-[#EAF7EC] dark:border-[#263246] dark:bg-[#182233]">
    <div class="container mx-auto flex flex-wrap items-center justify-between gap-2 px-3 py-1.5 text-[11px] font-semibold sm:px-4">
        <span class="flex items-center gap-1.5 text-gray-700 dark:text-[#F1F5F9]">
            <i class="ph ph-calendar-blank text-[#D50E18]"></i>
            <span data-mc-date>{{ bn_day_date() }}</span>
        </span>
        @if(site_setting('site_location'))
            <span class="flex items-center gap-1.5 text-gray-600 dark:text-[#94A3B8]">
                <i class="ph ph-map-pin text-[#1F7A3D] dark:text-[#22C55E]"></i>
                {{ site_setting('site_location') }}
            </span>
        @endif
        <span class="flex items-center gap-1.5 text-[#D50E18] dark:text-[#22C55E]">
            <i class="ph ph-clock"></i>
            <span data-mc-clock-live>সময় লোড হচ্ছে…</span>
        </span>
    </div>
</div>
@endif
