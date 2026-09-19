@extends('admin.layouts.master')

@php
    /* ============================================================
     |  সংবাদ তৈরি / সম্পাদনা — পূর্ণাঙ্গ ফর্ম
     |  UI, Color, Logo, Typography, Sidebar: আগের ডিজাইনই অপরিবর্তিত
     |  ============================================================ */
    $isEdit      = $post->exists;
    $user        = auth()->user();
    $canPublish  = $user->can_manage('news.publish');
    $canDelete   = $user->can_manage('news.delete');
    $statusOptions = \App\Models\Post::STATUSES;

    $rootCats    = $categories->whereNull('parent_id');
    $subCats     = $categories->whereNotNull('parent_id');
    $selectedCat = (string) old('category_id', $post->category_id);

    $datePart = fn ($d) => $d ? (mc_carbon($d)?->format('Y-m-d') ?? '') : '';
    $timePart = fn ($d) => $d ? (mc_carbon($d)?->format('H:i') ?? '') : '';

    // পাবলিক এডিটরে ব্যবহৃত সময় (পাবলিশ/শিডিউল)
    $publishedDate = old('published_date', $datePart($post->published_at) ?: now()->format('Y-m-d'));
    $publishedTime = old('published_time', $timePart($post->published_at) ?: now()->format('H:i'));
    $scheduledDate = old('scheduled_date', $datePart($post->scheduled_at));
    $scheduledTime = old('scheduled_time', $timePart($post->scheduled_at));

    // ট্যাগ (chip হিসেবে দেখানো হয়; hidden input এ কমা-বিচ্ছিন্ন স্ট্রিং যায়)
    $tagNames = collect(
        old('tags') !== null
            ? preg_split('/[,،\n]+/u', (string) old('tags'))
            : ($isEdit ? $post->tags->pluck('name')->all() : [])
    )->map(fn ($t) => trim((string) $t))->filter()->unique()->values()->all();

    $currentStatus = old('status', $post->status ?: 'draft');
    $previewUrl    = $isEdit ? route('admin.news.preview', $post) : null;
    $publicUrl     = $isEdit && $post->is_live ? route('news.show', $post->slug) : null;
@endphp

@section('title', $isEdit ? 'সংবাদ সম্পাদনা' : 'নতুন সংবাদ')

@section('content')
<form action="{{ $isEdit ? route('admin.news.update', $post) : route('admin.news.store') }}"
      method="POST" enctype="multipart/form-data" id="newsForm">
    @csrf
    @if($isEdit) @method('PUT') @endif

    {{-- ===== পেজ হেড ===== --}}
    <div class="mb-5 flex flex-wrap items-start justify-between gap-3 border-b border-slate-200 pb-4 dark:border-slate-800">
        <div class="min-w-0">
            <h1 class="flex flex-wrap items-center gap-2 font-serif text-lg font-bold text-slate-900 dark:text-white sm:text-xl">
                {{ $isEdit ? 'সংবাদ সম্পাদনা' : 'নতুন সংবাদ যোগ করুন' }}
                <span class="mc-status-{{ $currentStatus }} rounded-full px-2 py-0.5 text-[10px] font-bold">{{ $statusOptions[$currentStatus] ?? $currentStatus }}</span>
                @if($post->is_breaking)<span class="rounded-full bg-[#E21D2B] px-2 py-0.5 text-[10px] font-bold text-white">ব্রেকিং</span>@endif
                @if($post->is_featured)<span class="rounded-full bg-amber-500 px-2 py-0.5 text-[10px] font-bold text-white">হোমপেজ</span>@endif
            </h1>
            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                @if($isEdit)
                    সংরক্ষণ করলেই পাবলিক ওয়েবসাইটে পরিবর্তন দেখা যাবে · শেষ হালনাগাদ: {{ bn_date($post->updated_at) }}
                @else
                    সব তথ্য পূরণ করে খসড়া সংরক্ষণ করুন, রিভিউতে পাঠান, শিডিউল করুন বা সরাসরি প্রকাশ করুন
                @endif
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" data-mc-modal-open="newsPreviewModal" class="mc-btn mc-btn-ghost flex items-center gap-1.5">
                <i class="ph ph-device-mobile-camera"></i> প্রাকদর্শন
            </button>
            @if($previewUrl)
                <a href="{{ $previewUrl }}" target="_blank" rel="noopener" class="mc-btn mc-btn-ghost flex items-center gap-1.5">
                    <i class="ph ph-arrow-square-out"></i> নতুন ট্যাবে
                </a>
            @endif
            @if($publicUrl)
                <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="mc-btn mc-btn-ghost flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400">
                    <i class="ph ph-globe"></i> লাইভ পেজ
                </a>
            @endif
            <a href="{{ route('admin.news.index') }}" class="mc-btn mc-btn-ghost"><i class="ph ph-list-bullets"></i> তালিকা</a>
        </div>
    </div>

    {{-- ===== এলার্ট (Upload / ভ্যালিডেশন) ===== --}}
    @if(session('upload_errors'))
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300">
            <p class="mb-1 flex items-center gap-1 font-bold"><i class="ph ph-warning-circle"></i> কিছু ছবি আপলোড করা যায়নি</p>
            <ul class="list-inside list-disc space-y-0.5">
                @foreach(session('upload_errors') as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
            <p class="mb-1 flex items-center gap-1 font-bold"><i class="ph ph-x-circle"></i> ফর্মটি সংরক্ষণ করা যায়নি — নিচের ত্রুটিগুলো ঠিক করুন</p>
            <ul class="list-inside list-disc space-y-0.5">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="mc-news-shell">
        {{-- ================= বাম কলাম ================= --}}
        <div class="mc-news-col">
            @include('admin.news.partials._basics')
            @include('admin.news.partials._images')
        </div>

        {{-- ================= ডান কলাম ================= --}}
        <div class="mc-news-col">
            @include('admin.news.partials._publish')
            @include('admin.news.partials._actions')
            @include('admin.news.partials._seo')
        </div>
    </div>

    {{-- ===== মোবাইল/স্ক্রল-বান্ধব স্টিকি অ্যাকশন বার ===== --}}
    <div class="mc-sticky-actions">
        <span class="mr-auto text-[11px] text-slate-500 dark:text-slate-400">
            <i class="ph ph-info"></i> সংরক্ষণ করলে ক্যাশ রিফ্রেশ হয়ে ওয়েবসাইটে সাথে সাথে আপডেট দেখা যাবে
        </span>
        <button type="submit" name="action" value="save_draft" class="mc-btn mc-btn-ghost flex items-center gap-1.5" data-submit-action="save_draft">
            <i class="ph ph-file-dashed"></i> খসড়া সংরক্ষণ
        </button>
        @if($canPublish)
            <button type="submit" name="action" value="schedule" class="mc-btn mc-btn-ghost flex items-center gap-1.5" data-submit-action="schedule">
                <i class="ph ph-calendar-check"></i> শিডিউল
            </button>
            <button type="submit" name="action" value="publish" class="mc-btn mc-btn-primary flex items-center gap-1.5" data-submit-action="publish">
                <i class="ph-fill ph-paper-plane-tilt"></i> {{ $isEdit ? 'হালনাগাদ ও প্রকাশ' : 'প্রকাশ করুন' }}
            </button>
        @else
            <button type="submit" name="action" value="pending" class="mc-btn mc-btn-primary flex items-center gap-1.5" data-submit-action="pending">
                <i class="ph ph-paper-plane-tilt"></i> রিভিউতে পাঠান
            </button>
        @endif
    </div>
</form>

{{-- ছবি/ট্যাগের সহায়ক ফর্মগুলো (nested form এড়াতে মূল ফর্মের বাইরে) --}}
@include('admin.news.partials._image-forms')

@include('admin.news.partials._preview-modal')
@endsection

@push('scripts')
<script>
    /* ফর্ম ↔ JS সংযোগ (ছবির raw URL কোথাও পাঠানো হয় না) */
    window.MC_NEWS = {
        isEdit: @json($isEdit),
        postId: @json($post->id),
        canPublish: @json($canPublish),
        maxKb: @json((int) $maxUploadKb),
        maxUploads: @json((int) $maxUploads),
        mediaMap: @json($contentMedia),
        deleteImageLabel: 'ছবিটি গ্যালারি থেকে মুছে ফেলবেন?',
        routes: {
            editorMedia: @json(route('admin.news.media.store')),
            reorder: @json($isEdit ? route('admin.news.images.reorder', $post) : null),
            imagesStore: @json($isEdit ? route('admin.news.images.store', $post) : null)
        },
        labels: {
            featured: 'ফিচার্ড',
            featuredSet: 'এই ছবিটি ফিচার্ড করা হচ্ছে…',
            uploading: 'আপলোড হচ্ছে…',
            saved: 'সংরক্ষিত হয়েছে',
            error: 'সমস্যা হয়েছে',
            notAllowed: 'এই ধরনের ফাইল গ্রহণ করা হয় না',
            tooBig: 'ফাইলটি অনেক বড়',
            mediaKey: 'ছবি'
        }
    };
</script>
<script src="{{ asset('js/news-editor.js') }}" defer></script>
@endpush
