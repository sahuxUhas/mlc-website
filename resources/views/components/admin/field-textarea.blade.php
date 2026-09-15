@props(['name','label','value'=>null,'required'=>false,'rows'=>4,'hint'=>null,'counter'=>false,'maxlength'=>null])
<div>
    <label for="{{ $name }}" class="mc-label">{{ $label }} @if($required)<span class="text-[#E21D2B]">*</span>@endif</label>
    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" @if($maxlength) maxlength="{{ $maxlength }}" @endif
              @if($required) required @endif @if($counter) data-maxlen="#{{ $name }}-count" @endif
              class="mc-input @error($name) border-red-400 @enderror">{{ old($name, $value) }}</textarea>
    <div class="mt-1 flex items-center justify-between gap-2">
        @if($hint)<p class="text-[11px] text-slate-500">{{ $hint }}</p>@else<span></span>@endif
        @if($counter)<span id="{{ $name }}-count" class="shrink-0 text-[11px] text-slate-400"></span>@endif
    </div>
    @error($name)<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
</div>
