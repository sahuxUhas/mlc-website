{{-- ছবি আপলোড ফিল্ড — বর্তমান ছবি + ইনস্ট্যান্ট প্রিভিউ সহ --}}
@props(['name','label','current'=>null,'required'=>false,'hint'=>'jpg, jpeg, png, webp — সর্বোচ্চ ৪ MB'])
<div>
    <label for="{{ $name }}" class="mc-label">{{ $label }} @if($required)<span class="text-[#E21D2B]">*</span>@endif</label>
    <div class="flex items-start gap-3">
        <img id="{{ $name }}-preview" src="{{ $current ? mc_image($current) : '' }}" alt="প্রিভিউ"
             class="h-16 w-24 shrink-0 rounded-lg border border-slate-200 bg-slate-100 object-cover dark:border-slate-700 dark:bg-slate-800 {{ $current ? '' : 'hidden' }}">
        <div class="min-w-0 flex-1">
            <input id="{{ $name }}" name="{{ $name }}" type="file" accept="image/jpeg,image/png,image/webp,image/gif,image/avif"
                   data-preview="#{{ $name }}-preview"
                   class="block w-full text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-bold file:text-slate-700 hover:file:bg-slate-200 dark:text-slate-400 dark:file:bg-slate-800 dark:file:text-slate-200">
            <p class="mt-1 text-[11px] text-slate-500">{{ $hint }}</p>
            @error($name)<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>
