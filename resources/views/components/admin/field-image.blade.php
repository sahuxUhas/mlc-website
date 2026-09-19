{{-- ছবি আপলোড ফিল্ড — থাম্বনেইল প্রিভিউ + অপশনাল রিমুভ + ইনস্ট্যান্ট প্রিভিউ
     (raw URL কখনো দেখানো হয় না; ছবি হোস্টিং API তে যায়, DB-তে রেফারেন্স থাকে) --}}
@props([
    'name', 'label', 'current' => null, 'required' => false, 'hint' => null,
    'removeName' => null, 'thumb' => null, 'providerLabel' => null, 'previewClass' => 'h-16 w-24',
])
@php
    $hasCurrent = trim((string) $current) !== '';
    $hint = $hint ?: 'jpg, jpeg, png, webp — সর্বোচ্চ '.bn_num((int) config('images.max_kb', 4096) / 1024).' MB';
@endphp
<div>
    <label for="{{ $name }}" class="mc-label">{{ $label }} @if($required)<span class="text-[#E21D2B]">*</span>@endif</label>
    <div class="flex items-start gap-3">
        @if($hasCurrent)
            <div class="shrink-0 space-y-1">
                <img id="{{ $name }}-preview" src="{{ $thumb ?: mc_image($current) }}" alt="{{ $label }} প্রিভিউ"
                     class="{{ $previewClass }} rounded-lg border border-slate-200 bg-slate-100 object-cover dark:border-slate-700 dark:bg-slate-800"
                     onerror="this.onerror=null;this.src='{{ mc_placeholder_svg() }}'">
                @if($removeName)
                    <label class="flex cursor-pointer items-center justify-center gap-1 rounded-md bg-red-50 px-2 py-1 text-[10px] font-bold text-red-700 hover:bg-red-100 dark:bg-red-950/40 dark:text-red-300">
                        <input type="checkbox" name="{{ $removeName }}" value="1" class="h-3 w-3 rounded border-red-300 text-red-600">
                        ছবি সরান
                    </label>
                @endif
            </div>
        @else
            <img id="{{ $name }}-preview" src="" alt="প্রিভিউ"
                 class="{{ $previewClass }} hidden shrink-0 rounded-lg border border-slate-200 bg-slate-100 object-cover dark:border-slate-700 dark:bg-slate-800">
        @endif

        <div class="min-w-0 flex-1">
            <input id="{{ $name }}" name="{{ $name }}" type="file" accept="image/jpeg,image/png,image/webp"
                   data-preview="#{{ $name }}-preview"
                   class="block w-full text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-bold file:text-slate-700 hover:file:bg-slate-200 dark:text-slate-400 dark:file:bg-slate-800 dark:file:text-slate-200">
            <p class="mt-1 text-[11px] text-slate-500">{{ $hint }}</p>

            @if($hasCurrent && $providerLabel)
                <p class="mt-1 inline-flex items-center gap-1 rounded bg-emerald-50 px-1.5 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                    <i class="ph-fill ph-check-circle"></i> {{ $providerLabel }} এ সংরক্ষিত আছে
                </p>
            @endif

            @error($name)<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>
