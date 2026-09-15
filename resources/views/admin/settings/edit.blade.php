@extends('admin.layouts.master')
@section('title','সাইট সেটিংস')
@section('content')
<x-admin.page-head title="সাইট সেটিংস" subtitle="ওয়েবসাইটের সাধারণ তথ্য, যোগাযোগ, সোশ্যাল ও আচরণ নিয়ন্ত্রণ" />
@php
    $groups = $groups;
    $titles = ['general'=>'সাধারণ','contact'=>'যোগাযোগ','social'=>'সোশ্যাল মিডিয়া','footer'=>'ফুটার','behavior'=>'আচরণ ও মন্তব্য','integrations'=>'ইন্টিগ্রেশন ও কাস্টম কোড'];
    $icons  = ['general'=>'ph-gear-six','contact'=>'ph-phone','social'=>'ph-share-network','footer'=>'ph-layout','behavior'=>'ph-sliders-horizontal','integrations'=>'ph-code'];
    $first  = array_key_first($groups);
@endphp
<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')

    {{-- গ্রুপ ট্যাব --}}
    <div class="mb-4 flex flex-wrap gap-2">
        @foreach($groups as $gk => $fields)
            <button type="button" data-mc-tab="{{ $gk }}" data-mc-tab-group="settings"
                class="rounded-full px-3 py-1.5 text-xs font-bold transition {{ $gk===$first ? 'bg-[#E21D2B] text-white' : 'bg-white text-slate-600 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300' }}">
                <i class="ph {{ $icons[$gk] ?? 'ph-gear' }}"></i> {{ $titles[$gk] ?? ucfirst($gk) }}
            </button>
        @endforeach
    </div>

    @foreach($groups as $gk => $fields)
        <div data-mc-pane-group="settings" data-mc-pane-group-key="{{ $gk }}" class="mc-card p-4 sm:p-5 {{ $gk===$first ? '' : 'hidden' }}">
            <h2 class="mb-4 flex items-center gap-2 font-serif text-sm font-bold text-slate-900 dark:text-white">
                <i class="ph {{ $icons[$gk] ?? 'ph-gear' }} text-[#E21D2B]"></i> {{ $titles[$gk] ?? ucfirst($gk) }}
            </h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @foreach($fields as $field)
                    @if(($field['type'] ?? 'text') === 'bool')
                        <div class="md:col-span-2"><x-admin.dynamic-field :field="$field" :settings="$settings" /></div>
                    @elseif(($field['type'] ?? '') === 'textarea')
                        <div class="md:col-span-2"><x-admin.dynamic-field :field="$field" :settings="$settings" /></div>
                    @else
                        <x-admin.dynamic-field :field="$field" :settings="$settings" />
                    @endif
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="mt-4 flex flex-wrap items-center gap-2">
        <button type="submit" class="mc-btn mc-btn-primary"><i class="ph-fill ph-floppy-disk"></i> সব সেটিংস সংরক্ষণ করুন</button>
        <a href="{{ route('admin.settings.edit') }}" class="mc-btn mc-btn-ghost">রিসেট</a>
        <p class="text-[11px] text-slate-500">পরিবর্তন সাথে সাথেই ওয়েবসাইটে প্রয়োগ হবে।</p>
    </div>
</form>
@endsection
