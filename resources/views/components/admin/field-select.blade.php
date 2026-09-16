@props(['name','label','options'=>[],'selected'=>null,'required'=>false,'placeholder'=>null,'hint'=>null])
<div>
    <label for="{{ $name }}" class="mc-label">{{ $label }} @if($required)<span class="text-[#E21D2B]">*</span>@endif</label>
    <select id="{{ $name }}" name="{{ $name }}" @if($required) required @endif class="mc-input @error($name) border-red-400 @enderror">
        @if($placeholder !== null)<option value="">{{ $placeholder }}</option>@endif
        @foreach($options as $val => $lbl)
            <option value="{{ $val }}" @selected((string) old($name, $selected) === (string) $val)>{{ $lbl }}</option>
        @endforeach
    </select>
    @if($hint)<p class="mt-1 text-[11px] text-slate-500">{{ $hint }}</p>@endif
    @error($name)<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
</div>
