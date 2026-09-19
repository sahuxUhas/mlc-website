@extends('admin.layouts.master')
@php $isEdit = $page->exists; @endphp
@section('title', $isEdit ? 'পেজ সম্পাদনা' : 'নতুন পেজ')
@section('content')
<form action="{{ $isEdit ? route('admin.pages.update',$page) : route('admin.pages.store') }}" method="POST" enctype="multipart/form-data">
    @csrf @if($isEdit) @method('PUT') @endif
    <x-admin.page-head :title="$isEdit ? 'পেজ সম্পাদনা' : 'নতুন স্ট্যাটিক পেজ'" :subtitle="$isEdit ? 'স্লাগ: '.$page->slug : 'পেজ তৈরি করে মেনুতে যুক্ত করুন'" />
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="space-y-5 xl:col-span-2">
            <div class="mc-card p-4 sm:p-5">
                <x-admin.field-input name="title" label="পেজের শিরোনাম" :value="$page->title" required />
                <div class="mt-4"><x-admin.field-input name="slug" label="স্লাগ" :value="$page->slug" hint="খালি রাখলে শিরোনাম থেকে তৈরি হবে। পাবলিক URL: /{slug}" /></div>
                <div class="mt-4"><x-admin.field-textarea name="content" label="পেজের কনটেন্ট (HTML সমর্থিত)" :value="$page->content" :rows="18" /></div>
                <div class="mt-4">
                    {{-- সরাসরি ফাইল আপলোড — কোনো Image URL/Link ইনপুট নেই --}}
                    <x-admin.field-image name="featured_image" label="পেজের ছবি" :current="$page->featured_image"
                        :removeName="$page->featured_image ? 'remove_featured_image' : null" previewClass="h-20 w-32" />
                </div>
            </div>
            <div class="mc-card p-4 sm:p-5">
                <h2 class="mb-4 flex items-center gap-2 font-serif text-sm font-bold text-slate-900 dark:text-white"><i class="ph ph-magnifying-glass text-[#E21D2B]"></i> SEO</h2>
                <div class="space-y-3">
                    <x-admin.field-input name="meta_title" label="মেটা টাইটেল" :value="$page->meta_title" />
                    <x-admin.field-textarea name="meta_description" label="মেটা বিবরণ" :value="$page->meta_description" :rows="2" />
                    <x-admin.field-image name="og_image" label="OG ছবি (সোশ্যাল শেয়ার)"
                        :current="$page->og_image" :removeName="$page->og_image ? 'remove_og_image' : null" previewClass="h-16 w-28" />
                    <x-admin.field-input name="canonical_url" label="Canonical URL" type="url" :value="$page->canonical_url" />
                </div>
            </div>
        </div>
        <div class="space-y-5">
            <div class="mc-card p-4">
                <h2 class="mb-4 flex items-center gap-2 font-serif text-sm font-bold text-slate-900 dark:text-white"><i class="ph ph-gear-six text-[#E21D2B]"></i> প্রকাশের নিয়ন্ত্রণ</h2>
                <div class="space-y-3">
                    <x-admin.field-select name="template" label="টেমপ্লেট" :options="['default'=>'ডিফল্ট','full'=>'ফুল উইডথ','sidebar'=>'সাইডবার সহ','contact'=>'যোগাযোগ ফর্ম']" :selected="$page->template ?: 'default'" required />
                    <x-admin.field-input name="sort_order" label="ক্রম" type="number" :value="$page->sort_order ?? 0" step="1" />
                    <x-admin.field-checkbox name="is_visible" label="পেজ দৃশ্যমান" :checked="(bool)($page->is_visible ?? true)" hint="বন্ধ করলে পাবলিক সাইটে পেজটি ৪০৪ দেখাবে" />
                    <x-admin.field-checkbox name="show_in_footer" label="ফুটারে লিংক দেখান" :checked="(bool)($page->show_in_footer ?? false)" />
                </div>
            </div>
            <div class="mc-card space-y-2 p-4">
                <button type="submit" class="mc-btn mc-btn-primary w-full"><i class="ph-fill ph-floppy-disk"></i> {{ $isEdit ? 'আপডেট করুন' : 'পেজ তৈরি করুন' }}</button>
                <a href="{{ route('admin.pages.index') }}" class="mc-btn mc-btn-ghost block w-full text-center">← তালিকায় ফিরুন</a>
                @if($isEdit)<a href="{{ route('page.show',$page->slug) }}" target="_blank" class="mc-btn mc-btn-ghost block w-full text-center"><i class="ph ph-eye"></i> পাবলিকে দেখুন</a>@endif
            </div>
        </div>
    </div>
</form>
@endsection
