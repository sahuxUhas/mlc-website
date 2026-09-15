@extends('admin.layouts.master')
@section('title','মেনু ব্যবস্থাপনা')
@section('content')
<x-admin.page-head title="মেনু ব্যবস্থাপনা" subtitle="ক্রম, দৃশ্যমানতা, অভ্যন্তরীণ ও বাহ্যিক লিংক — মোবাইল বটম নাভ সহ" action="addMenu" actionLabel="নতুন মেনু আইটেম" />

{{-- লোকেশন ট্যাব --}}
<div class="mb-4 flex flex-wrap gap-2">
    @foreach($locations as $key=>$lbl)
        <a href="{{ route('admin.menus.index',['location'=>$key]) }}" class="rounded-full px-3 py-1.5 text-xs font-bold transition {{ $location===$key ? 'bg-[#E21D2B] text-white' : 'bg-white text-slate-600 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300' }}">{{ $lbl }}</a>
    @endforeach
</div>
<p class="mb-4 text-[11px] text-slate-500"><i class="ph ph-info"></i> <strong>{{ $locations[$location] ?? $location }}</strong> লোকেশনের মেনু সম্পাদনা করা হচ্ছে। তীর চিহ্ন দিয়ে সাজিয়ে “ক্রম সংরক্ষণ” করুন।</p>

<div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    <div class="xl:col-span-2">
        <div class="mc-card p-4">
            <div class="mb-3 flex items-center justify-between gap-2">
                <h2 class="font-serif text-sm font-bold text-slate-900 dark:text-white">মেনু আইটেম ({{ bn_count($menus->count()) }})</h2>
                <button type="button" onclick="document.getElementById('reorderMenus').submit()" class="mc-btn mc-btn-ghost text-xs"><i class="ph ph-arrows-down-up"></i> ক্রম সংরক্ষণ</button>
            </div>
            <form id="reorderMenus" action="{{ route('admin.menus.reorder') }}" method="POST">@csrf<input type="hidden" name="order" data-order-input value="{{ $menus->pluck('id')->implode(',') }}"></form>
            <ul id="menuList" class="space-y-2">
                @forelse($menus as $m)
                    @include('admin.menus._item',['m'=>$m,'level'=>0])
                    @foreach($m->children as $ch) @include('admin.menus._item',['m'=>$ch,'level'=>1]) @endforeach
                @empty <li><x-admin.empty-state icon="ph-list" message="এই লোকেশনে কোনো মেনু আইটেম নেই" hint="ডান পাশের ফর্ম থেকে যোগ করুন।" /></li> @endforelse
            </ul>
        </div>
    </div>

    {{-- নতুন মেনু আইটেম --}}
    <div class="mc-card p-4">
        <h2 class="mb-3 flex items-center gap-2 font-serif text-sm font-bold text-slate-900 dark:text-white"><i class="ph ph-plus-circle text-[#E21D2B]"></i> নতুন মেনু আইটেম</h2>
        <form action="{{ route('admin.menus.store') }}" method="POST" class="space-y-3">@csrf
            <input type="hidden" name="location" value="{{ $location }}">
            <x-admin.field-select name="parent_id" label="প্যারেন্ট আইটেম" placeholder="— মূল মেনু —" :options="$menus->pluck('label','id')->all()" />
            <x-admin.field-input name="label" label="মেনু লেবেল" required placeholder="যেমন: মহালছড়ি" />
            <x-admin.field-select name="link_type" label="লিংকের ধরন" :options="['url'=>'কাস্টম URL','category'=>'ক্যাটাগরি','page'=>'স্ট্যাটিক পেজ']" selected="url" required />
            <div id="urlBox"><x-admin.field-input name="url" label="URL" placeholder="/news অথবা https://…" hint="অভ্যন্তরীণ লিংকের জন্য / দিয়ে শুরু করুন" /></div>
            <div id="refBox" class="hidden">
                <x-admin.field-select name="reference_id" label="রেফারেন্স" placeholder="— নির্বাচন করুন —"
                    :options="$categories->pluck('name','id')->mapWithKeys(fn($v,$k)=>['c'.$k=>$v])->merge($pages->pluck('title','id')->mapWithKeys(fn($v,$k)=>['p'.$k=>$v]))->all()" />
                <p class="mt-1 text-[11px] text-slate-500">c… = ক্যাটাগরি, p… = পেজ</p>
            </div>
            <x-admin.field-input name="icon" label="আইকন" placeholder="ph-house" hint="মোবাইল বটম নাভে আইকন দেখায়" />
            <x-admin.field-input name="sort_order" label="ক্রম" type="number" value="0" step="1" />
            <div class="grid grid-cols-1 gap-2"><x-admin.field-checkbox name="is_enabled" label="সক্রিয়" :checked="true" />
                <x-admin.field-checkbox name="open_in_new_tab" label="নতুন ট্যাবে খুলুন" :checked="false" /></div>
            <button type="submit" class="mc-btn mc-btn-primary w-full"><i class="ph-fill ph-floppy-disk"></i> যোগ করুন</button>
        </form>
    </div>
</div>

@foreach($menus as $m) @include('admin.menus._edit',['m'=>$m]) @foreach($m->children as $ch) @include('admin.menus._edit',['m'=>$ch]) @endforeach @endforeach
@endsection
@push('scripts')
<script>(function(){var sel=document.querySelector('[name="link_type"]');function tg(){if(!sel)return;var isUrl=sel.value==='url';document.getElementById('urlBox').classList.toggle('hidden',!isUrl);document.getElementById('refBox').classList.toggle('hidden',isUrl)}
if(sel){sel.addEventListener('change',tg);tg()}
function sync(){var ids=[];document.querySelectorAll('#menuList [data-sortable-item]').forEach(function(i){ids.push(i.getAttribute('data-sortable-id'))});var inp=document.querySelector('[data-order-input]');if(inp)inp.value=ids.join(',')}
document.addEventListener('click',function(e){if(e.target.closest('[data-move]'))setTimeout(sync,0)})})();</script>
@endpush
