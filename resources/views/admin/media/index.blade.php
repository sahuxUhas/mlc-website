@extends('admin.layouts.master')
@section('title','মিডিয়া লাইব্রেরি')
@section('content')
<x-admin.page-head title="মিডিয়া লাইব্রেরি" subtitle="ছবি ও ডকুমেন্ট আপলোড, সার্চ, ফিল্টার ও ডিলিট — ছবি হোস্টিংয়ে যায়, DB-তে শুধু রেফারেন্স থাকে" />

{{-- আপলোড সার্ভিসের অবস্থা (API Key বা raw URL কখনো দেখানো হয় না) --}}
@php $provider = $provider ?? ['provider_label' => 'সার্ভার স্টোরেজ', 'configured' => true, 'remote' => false, 'name' => 'local']; @endphp

@if($provider['configured'] ?? false)
    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-800 dark:bg-emerald-900/20">
        <div class="flex flex-wrap items-center gap-2 text-sm font-bold text-emerald-700 dark:text-emerald-300">
            <i class="ph-fill ph-cloud-arrow-up text-lg"></i> ছবি আপলোড সক্রিয়
            <span class="rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] text-white">{{ $provider['provider_label'] }}</span>
            <span class="text-[11px] font-normal text-emerald-600 dark:text-emerald-400">
                ছবি সরাসরি হোস্টিং API দিয়ে আপলোড হয়; ওয়েবসাইটে নিজের ডোমেইনের নিরাপদ লিংক দিয়ে দেখানো হয়।
            </span>
        </div>
    </div>
@else
    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-3 dark:border-amber-800 dark:bg-amber-900/20">
        <div class="flex flex-wrap items-center gap-2 text-sm font-bold text-amber-700 dark:text-amber-300">
            <i class="ph ph-warning-circle text-lg"></i> ছবি হোস্টিং কনফিগার করা নেই
            <span class="text-[11px] font-normal">
                সার্ভারের <code>.env</code> ফাইলে <code>IMGBB_API_KEY</code> ও <code>IMGBB_ENABLED=true</code> সেট করুন
                (নিরাপত্তার কারণে Admin Panel থেকে Key দেওয়ার ব্যবস্থা নেই)। Key না থাকলে ছবি সার্ভার স্টোরেজে যাবে।
            </span>
        </div>
    </div>
@endif

{{-- আপলোড কার্ড --}}
<div class="mc-card mb-5 p-4">
    <h2 class="mb-3 flex items-center gap-2 font-serif text-sm font-bold text-slate-900 dark:text-white">
        <i class="ph ph-cloud-arrow-up text-[#E21D2B]"></i> নতুন আপলোড
        <span class="rounded-full bg-slate-800 px-2 py-0.5 text-[10px] text-white dark:bg-slate-700">{{ $provider['provider_label'] }}</span>
    </h2>
    <form action="{{ route('admin.media.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
        @csrf
        <div data-dropzone="#mediaFiles" class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-7 text-center hover:border-[#E21D2B] hover:bg-red-50/40 dark:border-slate-700 dark:bg-slate-800/40">
            <i class="ph ph-images text-3xl text-slate-400"></i>
            <p class="mt-2 text-xs font-bold text-slate-600 dark:text-slate-300">ফাইল টেনে আনুন অথবা ক্লিক করুন</p>
            <p class="mt-0.5 text-[11px] text-slate-500">jpg, jpeg, png, webp, gif, avif, pdf — প্রতিটি সর্বোচ্চ {{ bn_num((int) config('images.max_kb', 4096) / 1024) }} MB</p>
        </div>
        <input id="mediaFiles" name="files[]" type="file" multiple accept="image/*,.pdf" class="hidden">
        <div id="mediaFiles-preview" class="grid grid-cols-4 gap-2 sm:grid-cols-8"></div>
        @error('files')<p class="text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
        @error('files.*')<p class="text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
        @if(session('upload_errors'))
            <ul class="list-inside list-disc rounded-lg bg-amber-50 px-3 py-2 text-[11px] text-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                @foreach(session('upload_errors') as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        @endif
        <div class="flex flex-wrap items-end gap-3">
            <div class="min-w-40 flex-1"><x-admin.field-input name="folder" label="ফোল্ডার" value="general" hint="general, news, category, reporter, ads, album" /></div>
            <button type="submit" class="mc-btn mc-btn-primary"><i class="ph-fill ph-upload-simple"></i> আপলোড করুন</button>
        </div>
    </form>
</div>

{{-- পরিসংখ্যান --}}
<div class="mb-4 grid grid-cols-4 gap-3">
    @foreach([['ph-files','মোট ফাইল',$stats['total']],['ph-image-square','ছবি',$stats['images']],['ph-hard-drives','মোট সাইজ',number_format($stats['size']/1048576,2).' MB'],['ph-cloud','হোস্টিংয়ে',$stats['imgbb'].' টি']] as [$ic,$lbl,$val])
        <div class="mc-card p-3 text-center"><i class="ph {{ $ic }} text-lg text-[#E21D2B]"></i>
            <p class="mt-1 font-serif text-base font-bold text-slate-900 dark:text-white">{{ $val }}</p><p class="text-[11px] text-slate-500">{{ $lbl }}</p></div>
    @endforeach
</div>

<x-admin.filter-bar :action="route('admin.media.index')" :fields="[
    ['name'=>'q','placeholder'=>'ফাইলের নাম বা alt টেক্সট খুঁজুন…'],
    ['name'=>'type','type'=>'select','placeholder'=>'সব ধরন','options'=>['image'=>'ছবি','document'=>'ডকুমেন্ট','video'=>'ভিডিও']],
    ['name'=>'folder','type'=>'select','placeholder'=>'সব ফোল্ডার','options'=>($folders ?? [])]]" />

<div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5 xl:grid-cols-6">
    @forelse($media as $m)
        <div class="mc-card group overflow-hidden p-0">
            <div class="relative aspect-square bg-slate-100 dark:bg-slate-800">
                @if($m->is_imgbb)
                    <span class="absolute left-1.5 top-1.5 z-10 rounded-full bg-slate-800/90 px-1.5 py-0.5 text-[9px] font-bold text-white">{{ $m->provider_label }}</span>
                @endif
                @if($m->is_image)
                    <img src="{{ $m->thumb }}" alt="{{ $m->alt_text ?: $m->display_name }}" loading="lazy" class="h-full w-full object-cover"
                         onerror="this.onerror=null;this.src='{{ mc_placeholder_svg() }}'">
                @else
                    <span class="flex h-full w-full items-center justify-center"><i class="ph ph-file-text text-3xl text-slate-400"></i></span>
                @endif
                <div class="absolute inset-x-0 bottom-0 flex gap-1 bg-gradient-to-t from-black/85 to-transparent p-1.5">
                    <button type="button" data-mc-modal-open="editMedia-{{ $m->id }}" title="এডিট" class="flex-1 rounded bg-white/20 py-1 text-[10px] font-bold text-white hover:bg-white/35"><i class="ph ph-pencil-simple text-[11px]"></i> এডিট</button>
                    <form action="{{ route('admin.media.destroy',$m) }}" method="POST" class="inline" data-confirm="ফাইলটি মুছে ফেলবেন? এটি ব্যবহৃত হলে সংবাদে ছবি ভাঙতে পারে।">@csrf @method('DELETE')
                        <button type="submit" title="মুছুন" class="rounded bg-white/20 px-1.5 py-1 text-white hover:bg-red-600"><i class="ph ph-trash-simple text-[11px]"></i></button></form>
                </div>
            </div>
            <div class="p-2"><p class="truncate text-[11px] font-bold text-slate-700 dark:text-slate-200" title="{{ $m->display_name }}">{{ $m->display_name }}</p>
                <p class="text-[10px] text-slate-400">{{ $m->folder ?: 'general' }} · {{ $m->human_size }}</p></div>
        </div>
    @empty <div class="col-span-full"><x-admin.empty-state icon="ph-image-square" message="মিডিয়া লাইব্রেরি খালি" hint="উপরের ফর্ম থেকে প্রথম ফাইল আপলোড করুন।" /></div> @endforelse
</div>
{{ $media->links() }}

@foreach($media as $m)
<x-admin.modal :id="'editMedia-'.$m->id" :title="'এডিট: '.$m->display_name">
    <form action="{{ route('admin.media.update',$m) }}" method="POST" class="space-y-3">@csrf @method('PUT')
        @if($m->is_image)
            <img src="{{ $m->thumb }}" alt="{{ $m->alt_text ?: $m->display_name }}" class="mx-auto max-h-40 rounded-lg border border-slate-200 dark:border-slate-700"
                 onerror="this.onerror=null;this.src='{{ mc_placeholder_svg() }}'">
        @endif
        <x-admin.field-input name="alt_text" label="Alt টেক্সট (SEO)" :value="$m->alt_text" />
        <x-admin.field-textarea name="caption" label="ক্যাপশন" :value="$m->caption" :rows="2" />
        <x-admin.field-input name="folder" label="ফোল্ডার" :value="$m->folder ?: 'general'" />
        <div class="rounded-lg bg-slate-50 p-2.5 dark:bg-slate-800/60">
            <p class="mb-1 text-[10px] font-bold uppercase text-slate-500">ছবির তথ্য</p>
            <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-600 dark:text-slate-300">
                <p>হোস্টিং: <span class="font-bold">{{ $m->provider_label }}</span></p>
                <p>সাইজ: <span class="font-bold">{{ $m->human_size }}</span></p>
                @if($m->width && $m->height)<p>মাপ: <span class="font-bold" dir="ltr">{{ $m->width }}×{{ $m->height }}</span></p>@endif
                <p>আপলোড: <span class="font-bold">{{ bn_date($m->created_at, false) }}</span></p>
            </div>
            <p class="mt-2 text-[10px] text-slate-400">নিরাপত্তার কারণে হোস্টিংয়ের মূল লিংক কোথাও দেখানো হয় না — ওয়েবসাইটে নিজের ডোমেইনের নিরাপদ লিংক ব্যবহৃত হয়।</p>
        </div>
        <button type="submit" class="mc-btn mc-btn-primary w-full"><i class="ph-fill ph-floppy-disk"></i> আপডেট</button>
    </form>
</x-admin.modal>
@endforeach
@endsection
@push('scripts')
<script>(function(){var fi=document.getElementById('mediaFiles'),pb=document.getElementById('mediaFiles-preview');
if(fi&&pb){fi.addEventListener('change',function(){pb.innerHTML='';Array.prototype.forEach.call(fi.files,function(f){var d=document.createElement('div');d.className='rounded-lg border border-slate-200 bg-white p-1 text-center dark:border-slate-700 dark:bg-slate-800';if(f.type.match(/^image\//)){var r=new FileReader();r.onload=function(e){d.innerHTML='<img src="'+e.target.result+'" class="aspect-square w-full rounded object-cover"><p class="mt-0.5 truncate text-[9px] text-slate-500">'+f.name+'</p>'};r.readAsDataURL(f)}else{d.innerHTML='<i class="ph ph-file-text text-xl text-slate-400"></i><p class="truncate text-[9px] text-slate-500">'+f.name+'</p>'}pb.appendChild(d)})});
var dz=document.querySelector('[data-dropzone="#mediaFiles"]');if(dz){dz.addEventListener('click',function(){fi.click()});['dragenter','dragover'].forEach(function(ev){dz.addEventListener(ev,function(e){e.preventDefault()})});dz.addEventListener('drop',function(e){e.preventDefault();if(e.dataTransfer&&e.dataTransfer.files.length){fi.files=e.dataTransfer.files;fi.dispatchEvent(new Event('change'))}})}}})();</script>
@endpush
