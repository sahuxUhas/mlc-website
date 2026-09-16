{{-- সার্চ + ফিল্টার বার (GET ফর্ম) --}}
@props(['action','fields'=>[],'submitLabel'=>'ফিল্টার'])
<form method="GET" action="{{ $action }}" class="mb-4 grid grid-cols-2 gap-2 rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-[#0F172A] md:grid-cols-4 lg:grid-cols-5">
    @foreach($fields as $f)
        @if(($f['type'] ?? 'text') === 'select')
            <select name="{{ $f['name'] }}" class="mc-input text-xs">
                <option value="">{{ $f['placeholder'] ?? 'সব' }}</option>
                @foreach($f['options'] as $val => $lbl)
                    <option value="{{ $val }}" @selected((string) request($f['name']) === (string) $val)>{{ $lbl }}</option>
                @endforeach
            </select>
        @else
            <input type="{{ $f['type'] ?? 'text' }}" name="{{ $f['name'] }}" value="{{ request($f['name']) }}"
                   placeholder="{{ $f['placeholder'] ?? '' }}" class="mc-input text-xs">
        @endif
    @endforeach
    <div class="flex gap-2">
        <button type="submit" class="mc-btn mc-btn-primary flex-1"><i class="ph ph-funnel"></i> {{ $submitLabel }}</button>
        <a href="{{ $action }}" class="mc-btn mc-btn-ghost" title="রিসেট"><i class="ph ph-arrow-counter-clockwise"></i></a>
    </div>
</form>
