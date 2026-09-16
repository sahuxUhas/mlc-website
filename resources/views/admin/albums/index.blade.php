@extends('admin.layouts.master')
@section('title','ফটো অ্যালবাম')
@section('content')
<x-admin.page-head title="ফটো অ্যালবাম / গ্যালারি" subtitle="একাধিক ছবি, কভার, ক্রম ও দৃশ্যমানতা" action="addAlbum" actionLabel="নতুন অ্যালবাম" />
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    @forelse($albums as $al)
        <div class="mc-card overflow-hidden p-0">
            <a href="{{ route('admin.albums.edit',$al) }}" class="block aspect-video bg-slate-100 dark:bg-slate-800">
                <img src="{{ mc_image($al->cover_image) }}" alt="{{ $al->title }}" loading="lazy" class="h-full w-full object-cover">
            </a>
            <div class="p-3"><p class="line-clamp-1 font-serif text-sm font-bold text-slate-900 dark:text-white">{{ $al->title }}</p>
                @if($al->description)<p class="mt-0.5 line-clamp-2 text-[11px] text-slate-500">{{ $al->description }}</p>@endif
                <div class="mt-2 flex items-center justify-between gap-2">
                    <span class="inline-flex items-center gap-1 text-[11px] text-slate-500"><i class="ph ph-images"></i> {{ bn_count($al->photos_count) }} ছবি
                        @if(!$al->is_visible)<span class="mc-badge bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300">লুকানো</span>@endif</span>
                    <div class="flex gap-1"><a href="{{ route('admin.albums.edit',$al) }}" title="এডিট" class="rounded border border-slate-300 p-1.5 text-slate-600 hover:border-[#E21D2B] hover:text-[#E21D2B] dark:border-slate-700 dark:text-slate-300"><i class="ph ph-pencil-simple"></i></a>
                    <a href="{{ route('gallery.show',$al->slug) }}" target="_blank" title="দেখুন" class="rounded border border-slate-300 p-1.5 text-slate-600 hover:border-blue-500 hover:text-blue-600 dark:border-slate-700 dark:text-slate-300"><i class="ph ph-arrow-square-out"></i></a>
                    <form action="{{ route('admin.albums.destroy',$al) }}" method="POST" data-confirm="অ্যালবাম ‘{{ $al->title }}’ মুছে ফেলবেন?">@csrf @method('DELETE')
                        <button type="submit" title="মুছুন" class="rounded border border-red-300 p-1.5 text-red-600 hover:bg-red-50 dark:border-red-800"><i class="ph ph-trash-simple"></i></button></form></div>
                </div></div>
        </div>
    @empty <div class="sm:col-span-2 lg:col-span-3 xl:col-span-4"><x-admin.empty-state icon="ph-images" message="কোনো অ্যালবাম নেই" hint="উপরের ‘নতুন অ্যালবাম’ বাটনে ক্লিক করুন।" /></div> @endforelse
</div>
{{ $albums->links() }}
<x-admin.modal id="addAlbum" title="নতুন অ্যালবাম">
    <form action="{{ route('admin.albums.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">@csrf
        <x-admin.field-input name="title" label="অ্যালবাম শিরোনাম" required />
        <x-admin.field-input name="slug" label="স্লাগ" hint="খালি রাখলে শিরোনাম থেকে তৈরি হবে" />
        <x-admin.field-textarea name="description" label="বিবরণ" :rows="3" />
        <x-admin.field-image name="cover_image" label="কভার ছবি" hint="খালি রাখলে প্রথম আপলোড করা ছবি কভার হবে" />
        <div class="grid grid-cols-2 gap-3"><x-admin.field-input name="sort_order" label="ক্রম" type="number" value="0" step="1" />
            <div class="self-end"><x-admin.field-checkbox name="is_visible" label="দৃশ্যমান" :checked="true" /></div></div>
        <x-admin.field-input name="published_at" label="প্রকাশের তারিখ" type="datetime-local" />
        <x-admin.field-input name="meta_title" label="মেটা টাইটেল" />
        <x-admin.field-textarea name="meta_description" label="মেটা বিবরণ" :rows="2" />
        <div><label class="mc-label">ছবি (একাধিক)</label><input type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp" class="block w-full text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-bold">
            <p class="mt-1 text-[11px] text-slate-500">তৈরির পর এডিট পেজে আরও ছবি যোগ, ক্রম পরিবর্তন ও কভার সেট করা যাবে।</p></div>
        <button type="submit" class="mc-btn mc-btn-primary w-full"><i class="ph-fill ph-floppy-disk"></i> তৈরি করুন</button>
    </form>
</x-admin.modal>
@endsection
