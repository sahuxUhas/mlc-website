@extends('admin.layouts.master')
@section('title','অ্যালবাম এডিট')
@section('content')
<x-admin.page-head :title="'অ্যালবাম: '.$album->title" :subtitle="'স্লাগ: '.$album->slug" />
<div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    <div class="xl:col-span-2">
        {{-- ছবি যোগ --}}
        <div class="mc-card mb-5 p-4">
            <h2 class="mb-3 font-serif text-sm font-bold text-slate-900 dark:text-white"><i class="ph ph-plus-circle text-[#E21D2B]"></i> নতুন ছবি যোগ</h2>
            <form action="{{ route('admin.albums.photos.store',$album) }}" method="POST" enctype="multipart/form-data" class="space-y-3">@csrf
                <div data-dropzone="#albumPhotos" class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center hover:border-[#E21D2B] dark:border-slate-700 dark:bg-slate-800/40">
                    <i class="ph ph-images text-2xl text-slate-400"></i><p class="mt-1.5 text-xs font-bold text-slate-600 dark:text-slate-300">ক্লিক করে ছবি নির্বাচন করুন</p></div>
                <input id="albumPhotos" type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp" class="hidden">
                <div id="albumPhotos-preview" class="grid grid-cols-4 gap-2 sm:grid-cols-8"></div>
                <button type="submit" class="mc-btn mc-btn-primary"><i class="ph ph-upload-simple"></i> ছবি যোগ করুন</button>
            </form>
        </div>

        {{-- ক্রম পরিবর্তন + কভার + ডিলিট --}}
        <div class="mc-card p-4">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <h2 class="font-serif text-sm font-bold text-slate-900 dark:text-white"><i class="ph ph-images text-[#E21D2B]"></i> অ্যালবামের ছবি ({{ bn_count($album->photos->count()) }})</h2>
                <button type="button" onclick="document.getElementById('reorderPhotos').submit()" class="mc-btn mc-btn-ghost text-xs"><i class="ph ph-arrows-down-up"></i> ক্রম সংরক্ষণ</button>
            </div>
            <form id="reorderPhotos" action="{{ route('admin.albums.photos.reorder',$album) }}" method="POST">@csrf<input type="hidden" name="order" data-order-input value="{{ $album->photos->pluck('id')->implode(',') }}"></form>
            <div id="photoList" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                @forelse($album->photos as $ph)
                    <div data-sortable-item data-sortable-id="{{ $ph->id }}" class="group relative overflow-hidden rounded-xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800">
                        <img src="{{ mc_image($ph->path) }}" alt="" loading="lazy" class="aspect-square w-full object-cover">
                        @if($album->cover_image === $ph->path)<span class="absolute left-1.5 top-1.5 rounded bg-[#E21D2B] px-1.5 py-0.5 text-[9px] font-black text-white">কভার</span>@endif
                        <div class="absolute inset-x-0 bottom-0 flex items-center justify-between gap-1 bg-black/70 px-1.5 py-1">
                            <div class="flex gap-1"><button type="button" data-move="up" title="উপরে" class="rounded bg-white/15 px-1.5 py-0.5 text-white hover:bg-white/30"><i class="ph-bold ph-arrow-up text-[11px]"></i></button>
                                <button type="button" data-move="down" title="নিচে" class="rounded bg-white/15 px-1.5 py-0.5 text-white hover:bg-white/30"><i class="ph-bold ph-arrow-down text-[11px]"></i></button></div>
                            <div class="flex gap-1">
                                <form action="{{ route('admin.albums.update',$album) }}" method="POST" class="inline">@csrf @method('PUT')<input type="hidden" name="title" value="{{ $album->title }}"><input type="hidden" name="cover_photo_id" value="{{ $ph->id }}">
                                    <button type="submit" title="কভার করুন" class="rounded bg-white/15 px-1.5 py-0.5 text-white hover:bg-[#E21D2B]"><i class="ph-bold ph-star text-[11px]"></i></button></form>
                                <form action="{{ route('admin.albums.photos.destroy',[$album,$ph]) }}" method="POST" class="inline" data-confirm="ছবিটি অ্যালবাম থেকে সরাবেন?">@csrf @method('DELETE')
                                    <button type="submit" title="মুছুন" class="rounded bg-white/15 px-1.5 py-0.5 text-white hover:bg-red-600"><i class="ph-bold ph-trash text-[11px]"></i></button></form></div>
                        </div>
                        @if($ph->caption)<p class="line-clamp-1 px-2 py-1 text-[10px] text-slate-600 dark:text-slate-300">{{ $ph->caption }}</p>@endif
                    </div>
                @empty <p class="col-span-full py-8 text-center text-xs text-slate-500">এই অ্যালবামে এখনও কোনো ছবি নেই — উপরে থেকে যোগ করুন।</p> @endforelse
            </div>
        </div>
    </div>

    {{-- অ্যালবামের তথ্য --}}
    <div>
        <div class="mc-card p-4">
            <h2 class="mb-3 font-serif text-sm font-bold text-slate-900 dark:text-white"><i class="ph ph-pencil-simple text-[#E21D2B]"></i> অ্যালবামের তথ্য</h2>
            <form action="{{ route('admin.albums.update',$album) }}" method="POST" enctype="multipart/form-data" class="space-y-3">@csrf @method('PUT')
                <x-admin.field-input name="title" label="শিরোনাম" :value="$album->title" required />
                <x-admin.field-input name="slug" label="স্লাগ" :value="$album->slug" />
                <x-admin.field-textarea name="description" label="বিবরণ" :value="$album->description" :rows="3" />
                <x-admin.field-image name="cover_image" label="কভার ছবি" :current="$album->cover_image" />
                <div class="grid grid-cols-2 gap-3"><x-admin.field-input name="sort_order" label="ক্রম" type="number" :value="$album->sort_order" step="1" />
                    <div class="self-end"><x-admin.field-checkbox name="is_visible" label="দৃশ্যমান" :checked="(bool)$album->is_visible" /></div></div>
                <x-admin.field-input name="published_at" label="প্রকাশের তারিখ" type="datetime-local"
                    :value="$album->published_at ? \Illuminate\Support\Carbon::parse($album->published_at)->format('Y-m-d\TH:i') : ''" />
                <x-admin.field-input name="meta_title" label="মেটা টাইটেল" :value="$album->meta_title" />
                <x-admin.field-textarea name="meta_description" label="মেটা বিবরণ" :value="$album->meta_description" :rows="2" />
                <button type="submit" class="mc-btn mc-btn-primary w-full"><i class="ph-fill ph-floppy-disk"></i> আপডেট</button>
            </form>
        </div>
        <div class="mt-4 flex gap-2"><a href="{{ route('admin.albums.index') }}" class="mc-btn mc-btn-ghost flex-1 text-center">← তালিকা</a>
            <a href="{{ route('gallery.show',$album->slug) }}" target="_blank" class="mc-btn mc-btn-ghost flex-1 text-center"><i class="ph ph-eye"></i> দেখুন</a></div>
    </div>
</div>
@endsection
@push('scripts')
<script>(function(){var fi=document.getElementById('albumPhotos'),pb=document.getElementById('albumPhotos-preview');
if(fi&&pb){fi.addEventListener('change',function(){pb.innerHTML='';Array.prototype.forEach.call(fi.files,function(f){if(!f.type.match(/^image\//))return;var r=new FileReader();r.onload=function(e){var d=document.createElement('div');d.className='overflow-hidden rounded-lg border border-slate-200 dark:border-slate-700';d.innerHTML='<img src="'+e.target.result+'" class="aspect-square w-full object-cover">';pb.appendChild(d)};r.readAsDataURL(f)})});
var dz=document.querySelector('[data-dropzone="#albumPhotos"]');if(dz){dz.addEventListener('click',function(){fi.click()});dz.addEventListener('drop',function(e){e.preventDefault();if(e.dataTransfer.files.length){fi.files=e.dataTransfer.files;fi.dispatchEvent(new Event('change'))}})}}
function sync(){var ids=[];document.querySelectorAll('#photoList [data-sortable-item]').forEach(function(i){ids.push(i.getAttribute('data-sortable-id'))});var inp=document.querySelector('[data-order-input]');if(inp)inp.value=ids.join(',')}
document.addEventListener('click',function(e){if(e.target.closest('[data-move]'))setTimeout(sync,0)})})();</script>
@endpush
