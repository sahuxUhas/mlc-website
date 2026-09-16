@extends('admin.layouts.master')
@section('title','মিডিয়া লাইব্রেরি')
@section('content')
<x-admin.page-head title="মিডিয়া লাইব্রেরি" subtitle="ছবি ও ডকুমেন্ট আপলোড, সার্চ, কপি-URL ও ডিলিট" />

{{-- আপলোড কার্ড --}}
<div class="mc-card mb-5 p-4">
    <h2 class="mb-3 flex items-center gap-2 font-serif text-sm font-bold text-slate-900 dark:text-white"><i class="ph ph-cloud-arrow-up text-[#E21D2B]"></i> নতুন আপলোড</h2>
    <form action="{{ route('admin.media.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
        @csrf
        <div data-dropzone="#mediaFiles" class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-7 text-center hover:border-[#E21D2B] hover:bg-red-50/40 dark:border-slate-700 dark:bg-slate-800/40">
            <i class="ph ph-images text-3xl text-slate-400"></i>
            <p class="mt-2 text-xs font-bold text-slate-600 dark:text-slate-300">ফাইল টেনে আনুন অথবা ক্লিক করুন</p>
            <p class="mt-0.5 text-[11px] text-slate-500">jpg, jpeg, png, webp, gif, avif, pdf, doc, docx — সর্বোচ্চ ৪ MB প্রতিটি</p>
        </div>
        <input id="mediaFiles" name="files[]" type="file" multiple accept="image/*,.pdf,.doc,.docx" class="hidden">
        <div id="mediaFiles-preview" class="grid grid-cols-4 gap-2 sm:grid-cols-8"></div>
        <div class="flex flex-wrap items-end gap-3">
            <div class="min-w-40 flex-1"><x-admin.field-input name="folder" label="ফোল্ডার" value="general" hint="general, news, category, reporter, ads, album" /></div>
            <button type="submit" class="mc-btn mc-btn-primary"><i class="ph-fill ph-upload-simple"></i> আপলোড করুন</button>
        </div>
    </form>
</div>

{{-- পরিসংখ্যান --}}
<div class="mb-4 grid grid-cols-3 gap-3">
    @foreach([['ph-files','মোট ফাইল',$stats['total']],['ph-image-square','ছবি',$stats['images']],['ph-hard-drives','মোট সাইজ',number_format($stats['size']/1048576,2).' MB']] as [$ic,$lbl,$val])
        <div class="mc-card p-3 text-center"><i class="ph {{ $ic }} text-lg text-[#E21D2B]"></i>
            <p class="mt-1 font-serif text-base font-bold text-slate-900 dark:text-white">{{ $val }}</p><p class="text-[11px] text-slate-500">{{ $lbl }}</p></div>
    @endforeach
</div>

<x-admin.filter-bar :action="route('admin.media.index')" :fields="[
    ['name'=>'q','placeholder'=>'ফাইলের নাম বা alt টেক্সট খুঁজুন…'],
    ['name'=>'type','type'=>'select','placeholder'=>'সব ধরন','options'=>['image'=>'ছবি','document'=>'ডকুমেন্ট','video'=>'ভিডিও']],
    ['name'=>'folder','type'=>'select','placeholder'=>'সব ফোল্ডার','options'=>\App\Models\Media::select('folder')->distinct()->whereNotNull('folder')->pluck('folder','folder')->all()]]" />

<div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5 xl:grid-cols-6">
    @forelse($media as $m)
        <div class="mc-card group overflow-hidden p-0">
            <div class="relative aspect-square bg-slate-100 dark:bg-slate-800">
                @if($m->is_image)
                    <img src="{{ mc_image($m->path) }}" alt="{{ $m->alt_text }}" loading="lazy" class="h-full w-full object-cover">
                @else
                    <span class="flex h-full w-full items-center justify-center"><i class="ph ph-file-text text-3xl text-slate-400"></i></span>
                @endif
                <div class="absolute inset-x-0 bottom-0 flex gap-1 bg-gradient-to-t from-black/85 to-transparent p-1.5">
                    <button type="button" data-copy="{{ mc_image($m->path) }}" title="URL কপি" class="flex-1 rounded bg-white/20 py-1 text-[10px] font-bold text-white hover:bg-white/35">কপি</button>
                    <button type="button" data-mc-modal-open="editMedia-{{ $m->id }}" title="এডিট" class="rounded bg-white/20 px-1.5 py-1 text-white hover:bg-white/35"><i class="ph ph-pencil-simple text-[11px]"></i></button>
                    <form action="{{ route('admin.media.destroy',$m) }}" method="POST" class="inline" data-confirm="ফাইলটি মুছে ফেলবেন? এটি ব্যবহৃত হলে সংবাদে ছবি ভাঙতে পারে।">@csrf @method('DELETE')
                        <button type="submit" title="মুছুন" class="rounded bg-white/20 px-1.5 py-1 text-white hover:bg-red-600"><i class="ph ph-trash-simple text-[11px]"></i></button></form>
                </div>
            </div>
            <div class="p-2"><p class="truncate text-[11px] font-bold text-slate-700 dark:text-slate-200">{{ $m->file_name }}</p>
                <p class="text-[10px] text-slate-400">{{ $m->folder }} · {{ number_format(($m->size ?: 0)/1024,0) }} KB</p></div>
        </div>
    @empty <div class="col-span-full"><x-admin.empty-state icon="ph-image-square" message="মিডিয়া লাইব্রেরি খালি" hint="উপরের ফর্ম থেকে প্রথম ফাইল আপলোড করুন।" /></div> @endforelse
</div>
{{ $media->links() }}

@foreach($media as $m)
<x-admin.modal :id="'editMedia-'.$m->id" :title="'এডিট: '.$m->file_name">
    <form action="{{ route('admin.media.update',$m) }}" method="POST" class="space-y-3">@csrf @method('PUT')
        <img src="{{ mc_image($m->path) }}" alt="" class="mx-auto max-h-40 rounded-lg border border-slate-200 dark:border-slate-700">
        <x-admin.field-input name="alt_text" label="Alt টেক্সট (SEO)" :value="$m->alt_text" />
        <x-admin.field-textarea name="caption" label="ক্যাপশন" :value="$m->caption" :rows="2" />
        <x-admin.field-input name="folder" label="ফোল্ডার" :value="$m->folder" />
        <div class="rounded-lg bg-slate-50 p-2.5 dark:bg-slate-800/60"><p class="mb-1 text-[10px] font-bold uppercase text-slate-500">সরাসরি URL</p>
            <code class="block break-all text-[11px] text-slate-600 dark:text-slate-300" dir="ltr">{{ mc_image($m->path) }}</code></div>
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
