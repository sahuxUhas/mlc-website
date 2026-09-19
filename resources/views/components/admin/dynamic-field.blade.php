{{-- সেটিংস/SEO এর জন্য ডাইনামিক ফিল্ড রেন্ডারার (key/label/type/hint কাঠামো অনুযায়ী) --}}
@props(['field','settings'=>[]])
@php
    $key   = $field['key'];
    $label = $field['label'] ?? $key;
    $type  = $field['type'] ?? 'text';
    $hint  = $field['hint'] ?? null;
    $value = $settings[$key] ?? null;
    $id    = 'set-'.$key;
@endphp
@if($type === 'bool')
    <x-admin.field-checkbox :name="$key" :label="$label" :checked="filter_var($value, FILTER_VALIDATE_BOOLEAN)" :hint="$hint" />
@elseif($type === 'textarea')
    <x-admin.field-textarea :name="$key" :label="$label" :value="$value" :rows="4" :hint="$hint" />
@elseif($type === 'image')
    {{-- ছবি শুধু সরাসরি আপলোড করা হয় — কোনো URL/লিংক বসানোর ইনপুট নেই --}}
    <x-admin.field-image :name="$key" :label="$label" :current="$value"
        :accept="$field['accept'] ?? 'image/jpeg,image/png,image/webp'"
        hint="ফাইল নির্বাচন করলেই ছবি হোস্টিং API (ImgBB) তে যাবে — লিংক বসানোর সুবিধা নেই" />
@elseif($type === 'number')
    <x-admin.field-input :name="$key" :label="$label" type="number" :value="$value" :hint="$hint" step="1" />
@else
    <x-admin.field-input :name="$key" :label="$label" :value="$value" :hint="$hint" />
@endif
