@extends('admin.layouts.master')

@php
    $isEdit = $post->exists;
    $statusOptions = \App\Models\Post::STATUSES;
    $rootCats = $categories->whereNull('parent_id');
    $subCats  = $categories->whereNotNull('parent_id');
    // datetime-local ইনপুটের জন্য ফরম্যাট (Y-m-d\TH:i)
    $dtLocal = fn ($d) => $d ? \Illuminate\Support\Carbon::parse($d)->format('Y-m-d\TH:i') : '';
@endphp

@section('title', $isEdit ? 'সংবাদ সম্পাদনা' : 'নতুন সংবাদ')

@section('content')
<form action="{{ $isEdit ? route('admin.news.update', $post) : route('admin.news.store') }}"
      method="POST" enctype="multipart/form-data" id="newsForm" data-confirm="ফর্মে অসংরক্ষিত পরিবর্তন থাকতে পারে।">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <x-admin.page-head
        :title="$isEdit ? 'সংবাদ সম্পাদনা' : 'নতুন সংবাদ যোগ করুন'"
        :subtitle="$isEdit ? 'স্লাগ: '.$post->slug : 'সব তথ্য পূরণ করে সংবাদ প্রকাশ বা শিডিউল করুন'" />

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        {{-- ============ বাম কলাম: মূল কনটেন্ট ============ --}}
        <div class="space-y-5 xl:col-span-2">

            {{-- শিরোনাম ও স্লাগ --}}
            <div class="mc-card p-4 sm:p-5">
                <h2 class="mb-4 flex items-center gap-2 font-serif text-sm font-bold text-slate-900 dark:text-white">
                    <i class="ph ph-article text-[#E21D2B]"></i> মূল তথ্য
                </h2>

                <div class="space-y-4">
                    <div>
                        <label for="title" class="mc-label">সংবাদের শিরোনাম <span class="text-[#E21D2B]">*</span>
                            <span id="title-count" class="float-left font-normal text-slate-400"></span>
                        </label>
                        <input id="title" name="title" type="text" required minlength="5" maxlength="191"
                               value="{{ old('title', $post->title) }}" data-slug-from="#slug" data-maxlen="#title-count"
                               placeholder="যেমন: মহালছড়িতে নতুন স্বাস্থ্য কমপ্লেক্স উদ্বোধন"
                               class="mc-input font-serif text-base font-bold @error('title') border-red-400 @enderror">
                        @error('title')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="slug" class="mc-label">স্লাগ (SEO URL)</label>
                        <div class="flex items-center gap-2">
                            <span class="shrink-0 rounded-lg bg-slate-100 px-2.5 py-2 text-xs text-slate-500 dark:bg-slate-800 dark:text-slate-400" dir="ltr">{{ url('/news') }}/</span>
                            <input id="slug" name="slug" type="text" maxlength="191" value="{{ old('slug', $post->slug) }}"
                                   placeholder="auto-generated-from-title" dir="ltr"
                                   class="mc-input text-xs @error('slug') border-red-400 @enderror">
                        </div>
                        <p class="mt-1 text-[11px] text-slate-500">খালি রাখলে শিরোনাম থেকে স্বয়ংক্রিয়ভাবে তৈরি হবে। বাংলা অক্ষর URL এ সংরক্ষিত থাকে।</p>
                        @error('slug')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        {{-- ক্যাটাগরি --}}
                        <div>
                            <label for="category_id" class="mc-label">ক্যাটাগরি <span class="text-[#E21D2B]">*</span></label>
                            <select id="category_id" name="category_id" required class="mc-input @error('category_id') border-red-400 @enderror">
                                <option value="">— নির্বাচন করুন —</option>
                                @foreach($rootCats as $c)
                                    <option value="{{ $c->id }}" @selected((string) old('category_id', $post->category_id) === (string) $c->id)>{{ $c->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
                        </div>

                        {{-- সাব-ক্যাটাগরি (ক্যাটাগরি অনুযায়ী ফিল্টার হয়) --}}
                        <div>
                            <label for="subcategory_id" class="mc-label">সাব-ক্যাটাগরি</label>
                            <select id="subcategory_id" name="subcategory_id" class="mc-input @error('subcategory_id') border-red-400 @enderror">
                                <option value="">— কোনোটি নয় —</option>
                                @foreach($subCats as $sc)
                                    <option value="{{ $sc->id }}" data-parent="{{ $sc->parent_id }}"
                                            @selected((string) old('subcategory_id', $post->subcategory_id) === (string) $sc->id)>{{ $sc->name }}</option>
                                @endforeach
                            </select>
                            @error('subcategory_id')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
                        </div>

                        {{-- রিপোর্টার --}}
                        <div>
                            <label for="reporter_id" class="mc-label">রিপোর্টার / লেখক</label>
                            <select id="reporter_id" name="reporter_id" class="mc-input @error('reporter_id') border-red-400 @enderror">
                                <option value="">— নিজস্ব প্রতিবেদন —</option>
                                @foreach($reporters as $r)
                                    <option value="{{ $r->id }}" @selected((string) old('reporter_id', $post->reporter_id) === (string) $r->id)>{{ $r->name }}</option>
                                @endforeach
                            </select>
                            @error('reporter_id')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <x-admin.field-textarea name="excerpt" label="সংক্ষিপ্ত বিবরণ (Short Description)"
                        :value="$post->excerpt" :rows="3" :counter="true" :maxlength="600"
                        hint="খালি রাখলে মূল সংবাদ থেকে স্বয়ংক্রিয়ভাবে তৈরি হবে। কার্ড ও সার্চ ফলাফলে দেখা যায়।" />

                    <div>
                        <label for="content" class="mc-label">সম্পূর্ণ সংবাদ (Full Content) <span class="text-[#E21D2B]">*</span></label>
                        <textarea id="content" name="content" rows="18" required minlength="20" data-maxlen="#content-count"
                                  class="mc-input font-serif leading-[1.9] @error('content') border-red-400 @enderror">{{ old('content', $post->content) }}</textarea>
                        <div class="mt-1 flex items-center justify-between">
                            <p class="text-[11px] text-slate-500">HTML সমর্থিত: <code>&lt;p&gt; &lt;h2&gt; &lt;ul&gt; &lt;blockquote&gt; &lt;img&gt;</code></p>
                            <span id="content-count" class="text-[11px] text-slate-400"></span>
                        </div>
                        @error('content')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-admin.field-input name="location" label="স্থান / এলাকা" :value="$post->location" placeholder="মহালছড়ি, খাগড়াছড়ি" />
                        <x-admin.field-input name="video_url" label="ভিডিও URL (Embed)" type="url" :value="$post->video_url"
                            placeholder="https://…" hint="YouTube/Facebook embed লিংক — আর্টিকেলের উপরে দেখাবে" />
                    </div>

                    <x-admin.field-input name="tags" label="ট্যাগ"
                        :value="$isEdit ? $post->tags->pluck('name')->implode(', ') : old('tags')"
                        placeholder="বন্যা, ফেনী নদী, মহালছড়ি" hint="কমা দিয়ে আলাদা করুন। ট্যাগ পেজে এসইও লিংক তৈরি হয়।" />
                </div>
            </div>

            {{-- ============ ফিচার্ড ইমেজ ============ --}}
            <div class="mc-card p-4 sm:p-5">
                <h2 class="mb-4 flex items-center gap-2 font-serif text-sm font-bold text-slate-900 dark:text-white">
                    <i class="ph ph-image-square text-[#E21D2B]"></i> ফিচার্ড ইমেজ
                </h2>
                <x-admin.field-image name="featured_image" label="প্রধান ছবি" :current="$post->featured_image" />
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-admin.field-input name="image_caption" label="ছবির ক্যাপশন" :value="$post->image_caption" />
                    <x-admin.field-input name="image_credit" label="ছবির ক্রেডিট" :value="$post->image_credit" placeholder="সংগৃহীত / নিজেস্ব তোলা" />
                </div>
            </div>

            {{-- ============ একাধিক ছবি (Multiple Images) ============ --}}
            <div class="mc-card p-4 sm:p-5">
                <h2 class="mb-1 flex items-center gap-2 font-serif text-sm font-bold text-slate-900 dark:text-white">
                    <i class="ph ph-images text-[#E21D2B]"></i> সংবাদ গ্যালারি (একাধিক ছবি)
                </h2>
                <p class="mb-4 text-[11px] text-slate-500">
                    একসাথে ২০টি পর্যন্ত ছবি আপলোড করা যাবে। আপলোডের পর <strong>ক্রম পরিবর্তন</strong>, <strong>মুছে ফেলা</strong> ও
                    <strong>ফিচার্ড হিসেবে সেট</strong> করা যাবে। পাবলিক আর্টিকেল পেজে এগুলো গ্যালারি হিসেবে দেখাবে।
                </p>

                @if($isEdit)
                    {{-- বিদ্যমান ছবি — Reorder / Remove / Set Featured --}}
                    <div id="galleryList" class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                        @forelse($post->images as $image)
                            <div data-sortable-item data-sortable-id="{{ $image->id }}"
                                 class="group relative overflow-hidden rounded-xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800">
                                <img src="{{ mc_image($image->path) }}" alt="{{ $image->caption ?: $post->title }}" loading="lazy"
                                     class="aspect-video w-full object-cover">
                                @if($post->featured_image === $image->path)
                                    <span class="absolute right-1.5 top-1.5 rounded bg-[#E21D2B] px-1.5 py-0.5 text-[9px] font-black text-white shadow">ফিচার্ড</span>
                                @endif
                                <div class="absolute inset-x-0 bottom-0 flex items-center justify-between gap-1 bg-black/70 px-1.5 py-1">
                                    <div class="flex gap-1">
                                        <button type="button" data-move="up" title="উপরে" class="rounded bg-white/15 px-1.5 py-0.5 text-white hover:bg-white/30"><i class="ph-bold ph-arrow-up text-[11px]"></i></button>
                                        <button type="button" data-move="down" title="নিচে" class="rounded bg-white/15 px-1.5 py-0.5 text-white hover:bg-white/30"><i class="ph-bold ph-arrow-down text-[11px]"></i></button>
                                    </div>
                                    <div class="flex gap-1">
                                        <button type="button" onclick="document.getElementById('feat-{{ $image->id }}').submit()" title="ফিচার্ড করুন" class="rounded bg-white/15 px-1.5 py-0.5 text-white hover:bg-[#E21D2B]"><i class="ph-bold ph-star text-[11px]"></i></button>
                                        <button type="button" onclick="if(confirm('ছবিটি মুছে ফেলবেন?'))document.getElementById('imgdel-{{ $image->id }}').submit()" title="মুছুন" class="rounded bg-white/15 px-1.5 py-0.5 text-white hover:bg-red-600"><i class="ph-bold ph-trash text-[11px]"></i></button>
                                    </div>
                                </div>
                                @if($image->caption)<p class="line-clamp-1 px-2 py-1 text-[10px] text-slate-600 dark:text-slate-300">{{ $image->caption }}</p>@endif
                            </div>
                        @empty
                            <p class="col-span-full py-6 text-center text-xs text-slate-500">এই সংবাদে এখনও কোনো গ্যালারি ছবি যোগ করা হয়নি।</p>
                        @endforelse
                    </div>

                    {{-- ক্রম সংরক্ষণের hidden ইনপুট + ফর্ম --}}
                    <input type="hidden" name="order" data-order-input value="{{ $post->images->pluck('id')->implode(',') }}">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <button type="button" onclick="document.getElementById('reorderForm').submit()" class="mc-btn mc-btn-ghost flex items-center gap-1.5">
                            <i class="ph ph-arrows-down-up"></i> ছবির ক্রম সংরক্ষণ করুন
                        </button>
                        <span class="text-[11px] text-slate-500">তীর চিহ্ন দিয়ে সাজিয়ে তারপর এই বাটনে ক্লিক করুন।</span>
                    </div>

                    @foreach($post->images as $image)
                        <form id="feat-{{ $image->id }}" action="{{ route('admin.news.featured', $post) }}" method="POST" class="hidden">@csrf<input type="hidden" name="image_id" value="{{ $image->id }}"></form>
                        <form id="imgdel-{{ $image->id }}" action="{{ route('admin.news.images.destroy', [$post, $image]) }}" method="POST" class="hidden">@csrf @method('DELETE')</form>
                    @endforeach
                    <form id="reorderForm" action="{{ route('admin.news.images.reorder', $post) }}" method="POST" class="hidden">@csrf<input type="hidden" name="order" value="{{ $post->images->pluck('id')->implode(',') }}"></form>

                    <hr class="my-4 border-slate-200 dark:border-slate-800">
                @endif

                {{-- নতুন ছবি আপলোড --}}
                <div>
                    <label for="images" class="mc-label">নতুন ছবি যোগ করুন @if($isEdit)(সংরক্ষণের পর গ্যালারিতে যুক্ত হবে)@endif</label>
                    <div data-dropzone="#images"
                         class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center transition-colors hover:border-[#E21D2B] hover:bg-red-50/40 dark:border-slate-700 dark:bg-slate-800/40">
                        <i class="ph ph-cloud-arrow-up text-3xl text-slate-400"></i>
                        <p class="mt-2 text-xs font-bold text-slate-600 dark:text-slate-300">ছবি টেনে আনুন অথবা ক্লিক করে নির্বাচন করুন</p>
                        <p class="mt-0.5 text-[11px] text-slate-500">jpg, jpeg, png, webp — একসাথে সর্বোচ্চ ২০টি, প্রতিটি ৪ MB</p>
                    </div>
                    <input id="images" name="images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple class="hidden">
                    <div id="images-preview" class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-6"></div>
                    @error('images')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
                    @error('images.*')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- ============ ডান কলাম: প্রকাশ, স্ট্যাটাস, SEO ============ --}}
        <div class="space-y-5">

            {{-- প্রকাশ ও স্ট্যাটাস --}}
            <div class="mc-card p-4">
                <h2 class="mb-4 flex items-center gap-2 font-serif text-sm font-bold text-slate-900 dark:text-white">
                    <i class="ph ph-paper-plane-tilt text-[#E21D2B]"></i> প্রকাশ ও স্ট্যাটাস
                </h2>
                <div class="space-y-4">
                    <x-admin.field-select name="status" label="স্ট্যাটাস" :options="$statusOptions"
                        :selected="$post->status ?: 'draft'" required
                        hint="খসড়া → রিভিউতে → প্রকাশিত। 'নির্ধারিত' দিলে নিচের সময় অনুযায়ী স্বয়ংক্রিয় প্রকাশ হবে।" />

                    <x-admin.field-input name="published_at" label="প্রকাশের তারিখ ও সময়" type="datetime-local"
                        :value="old('published_at', $dtLocal($post->published_at))"
                        hint="খালি রাখলে প্রকাশ করার মুহূর্তে বসবে।" />

                    <div id="scheduleBox">
                        <x-admin.field-input name="scheduled_at" label="নির্ধারিত প্রকাশের সময় (Schedule)" type="datetime-local"
                            :value="old('scheduled_at', $dtLocal($post->scheduled_at))"
                            hint="স্ট্যাটাস 'নির্ধারিত' হলে এই সময় পার হলে স্বয়ংক্রিয়ভাবে প্রকাশিত হবে।" />
                    </div>

                    @if(auth()->user()->can_manage('news.publish'))
                        <div class="grid grid-cols-1 gap-2">
                            <x-admin.field-checkbox name="is_featured" label="ফিচার্ড নিউজ" :checked="$post->is_featured"
                                hint="হোমপেজে অগ্রাধিকার পাবে" />
                            <x-admin.field-checkbox name="is_breaking" label="ব্রেকিং নিউজ" :checked="$post->is_breaking"
                                hint="হেডারের ব্রেকিং বার-এ দেখাবে" />
                        </div>
                    @else
                        <p class="rounded-lg bg-amber-50 px-3 py-2 text-[11px] font-semibold text-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                            <i class="ph ph-lock-key"></i> ফিচার্ড ও ব্রেকিং সেট করার অনুমতি আপনার নেই।
                        </p>
                    @endif

                    <x-admin.field-checkbox name="allow_comments" label="মন্তব্যের অনুমতি" :checked="$post->allow_comments ?? true"
                        hint="বন্ধ করলে এই সংবাদে কেউ মন্তব্য করতে পারবে না" />
                </div>
            </div>

            {{-- অ্যাকশন বাটন --}}
            <div class="mc-card space-y-2 p-4">
                <button type="submit" class="mc-btn mc-btn-primary flex w-full items-center justify-center gap-2">
                    <i class="ph-fill ph-floppy-disk"></i> {{ $isEdit ? 'পরিবর্তন সংরক্ষণ করুন' : 'সংবাদ সংরক্ষণ করুন' }}
                </button>

                @if($isEdit)
                    @if(auth()->user()->can_manage('news.publish') && $post->status !== 'published')
                        <button type="submit" name="status" value="published" form="newsForm"
                                class="mc-btn flex w-full items-center justify-center gap-2 bg-emerald-600 text-white hover:bg-emerald-700">
                            <i class="ph-fill ph-paper-plane-tilt"></i> সংরক্ষণ করে প্রকাশ করুন
                        </button>
                    @endif
                    <div class="flex gap-2">
                        <a href="{{ route('admin.news.index') }}" class="mc-btn mc-btn-ghost flex-1 text-center">বাতিল</a>
                        <a href="{{ route('news.show', $post->slug) }}" target="_blank" class="mc-btn mc-btn-ghost flex-1 text-center"><i class="ph ph-eye"></i> দেখুন</a>
                    </div>
                @else
                    <a href="{{ route('admin.news.index') }}" class="mc-btn mc-btn-ghost block w-full text-center">বাতিল</a>
                @endif
            </div>

            {{-- SEO --}}
            <div class="mc-card p-4">
                <h2 class="mb-4 flex items-center gap-2 font-serif text-sm font-bold text-slate-900 dark:text-white">
                    <i class="ph ph-magnifying-glass text-[#E21D2B]"></i> SEO সেটিংস
                </h2>
                <p class="mb-3 text-[11px] text-slate-500">খালি রাখলে শিরোনাম ও সংক্ষিপ্ত বিবরণ থেকে স্বয়ংক্রিয়ভাবে ব্যবহার হবে।</p>
                <div class="space-y-3">
                    <x-admin.field-input name="meta_title" label="মেটা টাইটেল" :value="$post->meta_title" :counter="true" />
                    <x-admin.field-textarea name="meta_description" label="মেটা বিবরণ" :value="$post->meta_description" :rows="3" :counter="true" :maxlength="500" />
                    <x-admin.field-input name="meta_keywords" label="মেটা কীওয়ার্ড" :value="$post->meta_keywords" hint="কমা দিয়ে আলাদা করুন" />
                    <x-admin.field-input name="og_title" label="OG টাইটেল (সোশ্যাল শেয়ার)" :value="$post->og_title" />
                    <x-admin.field-textarea name="og_description" label="OG বিবরণ" :value="$post->og_description" :rows="2" :maxlength="500" />
                    <x-admin.field-image name="og_image" label="OG ছবি (১২০০×৬৩০ প্রস্তাবিত)" :current="$post->og_image" />
                    <x-admin.field-input name="canonical_url" label="Canonical URL" type="url" :value="$post->canonical_url" placeholder="https://…" />
                </div>
            </div>
        </div>
    </div>
</form>

@if($isEdit)
    {{-- গ্যালারি ছবি যোগ (এডিট মোডে আলাদা ফর্মে, যাতে মূল ফর্ম রিসাবমিট না লাগে) --}}
    <form id="addImagesForm" action="{{ route('admin.news.images.store', $post) }}" method="POST" enctype="multipart/form-data" class="hidden">
        @csrf
    </form>
@endif
@endsection

@push('scripts')
<script>
(function(){
    'use strict';

    /* ===== সাব-ক্যাটাগরি ফিল্টার (নির্বাচিত ক্যাটাগরি অনুযায়ী) ===== */
    var catSel = document.getElementById('category_id');
    var subSel = document.getElementById('subcategory_id');
    function filterSubs(){
        if(!catSel || !subSel) return;
        var val = catSel.value;
        Array.prototype.forEach.call(subSel.options, function(opt){
            if(!opt.value) return;
            var match = !val || opt.getAttribute('data-parent') === val;
            opt.hidden = !match;
            opt.disabled = !match;
        });
        if(subSel.value){
            var cur = subSel.options[subSel.selectedIndex];
            if(cur && cur.disabled) subSel.value = '';
        }
    }
    if(catSel) catSel.addEventListener('change', filterSubs);
    filterSubs();

    /* ===== একাধিক ছবি: নির্বাচিত ফাইলের থাম্বনেইল প্রিভিউ + রিমুভ ===== */
    var fileInput = document.getElementById('images');
    var previewBox = document.getElementById('images-preview');

    if(fileInput && previewBox){
        fileInput.addEventListener('change', function(){ renderPreview(); });

        function renderPreview(){
            previewBox.innerHTML = '';
            Array.prototype.forEach.call(fileInput.files, function(file, idx){
                if(!file.type.match(/^image\//)) return;
                var reader = new FileReader();
                reader.onload = function(e){
                    var wrap = document.createElement('div');
                    wrap.className = 'relative overflow-hidden rounded-lg border border-slate-200 dark:border-slate-700';
                    wrap.innerHTML = '<img src="'+e.target.result+'" alt="" class="aspect-video w-full object-cover">'
                        + '<button type="button" data-remove="'+idx+'" title="সরান" '
                        + 'class="absolute right-1 top-1 rounded bg-black/70 px-1.5 py-0.5 text-[10px] font-bold text-white hover:bg-red-600">✕</button>'
                        + '<p class="truncate px-1.5 py-1 text-[10px] text-slate-600 dark:text-slate-300">'+file.name+'</p>';
                    previewBox.appendChild(wrap);
                };
                reader.readAsDataURL(file);
            });
        }

        /* প্রিভিউ থেকে সরালে আসল FileList থেকেও সরানো হয় */
        previewBox.addEventListener('click', function(e){
            var btn = e.target.closest('[data-remove]');
            if(!btn) return;
            var idx = parseInt(btn.getAttribute('data-remove'), 10);
            var dt = new DataTransfer();
            Array.prototype.forEach.call(fileInput.files, function(f, i){ if(i !== idx) dt.items.add(f); });
            fileInput.files = dt.files;
            renderPreview();
        });

        /* ড্রপজোন থেকে ফাইল পিক হলে সরাসরি এডিট মোডে আপলোড করা যায় */
        var dz = document.querySelector('[data-dropzone="#images"]');
        if(dz){
            dz.addEventListener('click', function(){ fileInput.click(); });
            ['dragenter','dragover'].forEach(function(ev){
                dz.addEventListener(ev, function(e){ e.preventDefault(); dz.classList.add('border-[#E21D2B]','bg-red-50'); });
            });
            ['dragleave','drop'].forEach(function(ev){
                dz.addEventListener(ev, function(e){ e.preventDefault(); dz.classList.remove('border-[#E21D2B]','bg-red-50'); });
            });
            dz.addEventListener('drop', function(e){
                if(e.dataTransfer && e.dataTransfer.files.length){
                    fileInput.files = e.dataTransfer.files;
                    renderPreview();
                }
            });
        }
    }

    /* ===== রিঅর্ডার: ক্রম পরিবর্তনের পর hidden ইনপুটে সিঙ্ক ===== */
    function syncOrder(){
        var ids = [];
        document.querySelectorAll('#galleryList [data-sortable-item]').forEach(function(it){
            ids.push(it.getAttribute('data-sortable-id'));
        });
        var input = document.querySelector('[data-order-input]');
        if(input) input.value = ids.join(',');
        var rf = document.querySelector('#reorderForm input[name="order"]');
        if(rf) rf.value = ids.join(',');
    }
    document.addEventListener('click', function(e){
        if(e.target.closest('[data-move]')) setTimeout(syncOrder, 0);
    });

    /* ===== স্ট্যাটাস অনুযায়ী Schedule বক্স দেখানো/লুকানো ===== */
    var statusSel = document.querySelector('[name="status"]');
    var schedBox = document.getElementById('scheduleBox');
    function toggleSched(){
        if(!statusSel || !schedBox) return;
        var on = statusSel.value === 'scheduled';
        schedBox.style.opacity = on ? '1' : '.45';
        var inp = schedBox.querySelector('input');
        if(inp){ inp.required = on; if(!on && !inp.value) inp.placeholder = 'শুধু "নির্ধারিত" স্ট্যাটাসে প্রয়োজন'; }
    }
    if(statusSel) statusSel.addEventListener('change', toggleSched);
    toggleSched();

    /* ===== অসংরক্ষিত পরিবর্তনের সতর্কবার্তা ===== */
    var form = document.getElementById('newsForm');
    var dirty = false;
    if(form){
        form.addEventListener('input', function(){ dirty = true; });
        form.addEventListener('submit', function(){ dirty = false; });
        window.addEventListener('beforeunload', function(e){
            if(dirty){ e.preventDefault(); e.returnValue = ''; }
        });
    }
})();
</script>
@endpush
