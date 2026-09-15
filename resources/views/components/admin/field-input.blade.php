{{-- টেক্সট/ইমেইল/নম্বর/ইউআরএল ইনপুট ফিল্ড --}}
@props(['name','label','type'=>'text','value'=>null,'required'=>false,'hint'=>null,'placeholder'=>null,'attrs'=>'','step'=>null])
<div>
    <label for="{{ $name }}" class="mc-label">{{ $label }} @if($required)<span class="text-[#E21D2B]">*</span>@endif</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}"
           @if($required) required @endif @if($step) step="{{ $step }}" @endif placeholder="{{ $placeholder }}" {!! $attrs !!}
           class="mc-input @error($name) border-red-400 ring-1 ring-red-300 @enderror">
    @if($hint)<p class="mt-1 text-[11px] text-slate-500">{{ $hint }}</p>@endif
    @error($name)<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
</div>
