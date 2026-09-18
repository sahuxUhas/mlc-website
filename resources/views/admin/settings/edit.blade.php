@extends('admin.layouts.master')
@section('title','সাইট সেটিংস')
@section('content')
<x-admin.page-head title="সাইট সেটিংস" subtitle="ওয়েবসাইটের সাধারণ তথ্য, যোগাযোগ, সোশ্যাল, হেডার-ফুটার ও আচরণ নিয়ন্ত্রণ" />
@php
    $titles = [
        'general'      => 'সাধারণ',
        'contact'      => 'যোগাযোগ',
        'social'       => 'সোশ্যাল মিডিয়া',
        'header'       => 'হেডার',
        'footer'       => 'ফুটার',
        'behavior'     => 'আচরণ ও মন্তব্য',
        'texts'        => 'ছোটখাটো লেখা',
        'media'        => 'মিডিয়া ও ImgBB',
        'integrations' => 'ইন্টিগ্রেশন ও কাস্টম কোড',
    ];
    $icons  = [
        'general'      => 'ph-gear-six',
        'contact'      => 'ph-phone',
        'social'       => 'ph-share-network',
        'header'       => 'ph-browser',
        'footer'       => 'ph-layout',
        'behavior'     => 'ph-sliders-horizontal',
        'texts'        => 'ph-text-aa',
        'media'        => 'ph-image-square',
        'integrations' => 'ph-code',
    ];
    $first = array_key_first($groups);
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
                    @if(in_array($field['type'] ?? 'text', ['bool', 'textarea'], true))
                        <div class="md:col-span-2"><x-admin.dynamic-field :field="$field" :settings="$settings" /></div>
                    @else
                        <x-admin.dynamic-field :field="$field" :settings="$settings" />
                    @endif
                @endforeach
            </div>

            {{-- ===== সোশ্যাল মিডিয়া লিংক রিপিটার (যত খুশি লিংক যোগ/বাদ) ===== --}}
            @if($gk === 'social')
                <div class="mt-6 border-t border-slate-200 pt-5 dark:border-slate-700">
                    <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                        <h3 class="flex items-center gap-2 font-serif text-sm font-bold text-slate-900 dark:text-white">
                            <i class="ph ph-list-plus text-[#E21D2B]"></i> সোশ্যাল লিংক তালিকা
                        </h3>
                        <button type="button" data-social-add class="mc-btn mc-btn-ghost text-xs"><i class="ph ph-plus"></i> নতুন লিংক যোগ করুন</button>
                    </div>
                    <p class="mb-3 text-[11px] text-slate-500">
                        এই তালিকায় একটি লিংকও থাকলে উপরের ঘরগুলো আর ব্যবহার হবে না। আইকনের নাম
                        <a class="font-bold text-[#E21D2B] hover:underline" href="https://phosphor.icons/" target="_blank" rel="noopener">phosphor.icons</a>
                        থেকে নিন — যেমন: <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">ph-facebook-logo</code>,
                        <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">ph-youtube-logo</code>,
                        <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">ph-tiktok-logo</code>
                    </p>

                    <div id="socialRows" class="space-y-2">
                        @forelse($socialLinks as $i => $link)
                            <div data-social-row class="grid grid-cols-1 items-end gap-2 rounded-lg border border-slate-200 p-3 dark:border-slate-700 sm:grid-cols-12">
                                <div class="sm:col-span-3">
                                    <label class="mc-label">নাম</label>
                                    <input type="text" name="social[label][]" value="{{ old('social.label.'.$i, $link['label']) }}" placeholder="ফেসবুক" class="mc-input">
                                </div>
                                <div class="sm:col-span-3">
                                    <label class="mc-label">আইকন</label>
                                    <input type="text" name="social[icon][]" value="{{ old('social.icon.'.$i, $link['icon']) }}" placeholder="ph-facebook-logo" class="mc-input">
                                </div>
                                <div class="sm:col-span-4">
                                    <label class="mc-label">URL</label>
                                    <input type="text" name="social[url][]" value="{{ old('social.url.'.$i, $link['url']) }}" placeholder="https://facebook.com/…" class="mc-input">
                                </div>
                                <div class="sm:col-span-1">
                                    <label class="mc-label">রঙ</label>
                                    <input type="color" name="social[color][]" value="{{ old('social.color.'.$i, $link['color'] ?? '#E21D2B') }}" class="h-9 w-full cursor-pointer rounded border border-slate-300 bg-white p-1 dark:border-slate-600 dark:bg-slate-800">
                                </div>
                                <div class="sm:col-span-1">
                                    <button type="button" data-social-remove class="mc-btn w-full bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-950/40 dark:text-red-300" title="মুছে ফেলুন"><i class="ph ph-trash-simple"></i></button>
                                </div>
                            </div>
                        @empty
                            <p data-social-empty class="rounded-lg border border-dashed border-slate-300 p-4 text-center text-xs text-slate-500 dark:border-slate-700">
                                এখনো কোনো সোশ্যাল লিংক যোগ করা হয়নি — “নতুন লিংক যোগ করুন” চাপুন।
                            </p>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    @endforeach

    <div class="mt-4 flex flex-wrap items-center gap-2">
        <button type="submit" class="mc-btn mc-btn-primary"><i class="ph-fill ph-floppy-disk"></i> সব সেটিংস সংরক্ষণ করুন</button>
        <a href="{{ route('admin.settings.edit') }}" class="mc-btn mc-btn-ghost">রিসেট</a>
        <a href="{{ route('home') }}" target="_blank" class="mc-btn mc-btn-ghost"><i class="ph ph-eye"></i> ওয়েবসাইটে দেখুন</a>
        <p class="text-[11px] text-slate-500">পরিবর্তন সাথে সাথেই ওয়েবসাইটে প্রয়োগ হবে।</p>
    </div>
</form>

{{-- নতুন সোশ্যাল সারির টেমপ্লেট --}}
<template id="socialRowTemplate">
    <div data-social-row class="grid grid-cols-1 items-end gap-2 rounded-lg border border-slate-200 p-3 dark:border-slate-700 sm:grid-cols-12">
        <div class="sm:col-span-3"><label class="mc-label">নাম</label><input type="text" name="social[label][]" placeholder="ফেসবুক" class="mc-input"></div>
        <div class="sm:col-span-3"><label class="mc-label">আইকন</label><input type="text" name="social[icon][]" placeholder="ph-facebook-logo" class="mc-input"></div>
        <div class="sm:col-span-4"><label class="mc-label">URL</label><input type="text" name="social[url][]" placeholder="https://facebook.com/…" class="mc-input"></div>
        <div class="sm:col-span-1"><label class="mc-label">রঙ</label><input type="color" name="social[color][]" value="#E21D2B" class="h-9 w-full cursor-pointer rounded border border-slate-300 bg-white p-1 dark:border-slate-600 dark:bg-slate-800"></div>
        <div class="sm:col-span-1"><button type="button" data-social-remove class="mc-btn w-full bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-950/40 dark:text-red-300" title="মুছে ফেলুন"><i class="ph ph-trash-simple"></i></button></div>
    </div>
</template>

@push('scripts')
<script>
(function () {
    'use strict';
    var list = document.getElementById('socialRows');
    var template = document.getElementById('socialRowTemplate');
    if (!list || !template) { return; }

    function toggleEmpty() {
        var empty = list.querySelector('[data-social-empty]');
        var rows = list.querySelectorAll('[data-social-row]');
        if (empty) { empty.classList.toggle('hidden', rows.length > 0); }
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest('[data-social-add]')) {
            var empty = list.querySelector('[data-social-empty]');
            if (empty) { empty.remove(); }
            list.appendChild(template.content.cloneNode(true));
            toggleEmpty();
            return;
        }
        var remove = e.target.closest('[data-social-remove]');
        if (remove) {
            var row = remove.closest('[data-social-row]');
            if (row) { row.remove(); }
            toggleEmpty();
        }
    });

    toggleEmpty();
})();
</script>
@endpush
@endsection
