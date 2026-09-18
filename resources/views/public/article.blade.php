@extends('layouts.public')

@section('title', ($post->meta_title ?: $post->title).' | '.site_setting('site_name', config('app.name')))
@section('meta_description', $post->meta_description ?: mc_excerpt($post->excerpt ?: $post->content, 300))
@section('meta_keywords', $post->meta_keywords ?: $post->tags->pluck('name')->implode(', '))
@section('og_title', $post->og_title ?: $post->title)
@section('og_description', $post->og_description ?: mc_excerpt($post->excerpt ?: $post->content, 240))
@section('og_type', 'article')
@section('og_image', $post->og_image ? mc_image($post->og_image) : ($post->featured_image ? mc_image($post->featured_image) : ''))
@section('canonical', $post->canonical_url ?: route('news.show', $post->slug))

@push('head')
    @if(filter_var(site_setting('seo_schema_enabled', '1'), FILTER_VALIDATE_BOOLEAN))
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endif
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'হোম', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $post->category?->name, 'item' => route('category.show', $post->category?->slug)],
        ],
    ], JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')
<div class="container mx-auto max-w-3xl px-3 py-6 sm:px-4 md:py-8" id="article-page">
    {{-- Breadcrumb --}}
    <nav class="mb-5 flex flex-wrap items-center gap-1.5 text-sm text-gray-500 dark:text-[#94A3B8]" aria-label="ব্রেডক্রাম্ব">
        <a href="{{ route('home') }}" class="hover:text-[#D50E18] dark:hover:text-[#22C55E] flex items-center gap-1"><i class="ph ph-house"></i> হোম</a>
        @if($post->category)
            <i class="ph ph-caret-right text-[10px]"></i>
            <a href="{{ route('category.show', $post->category->slug) }}" class="hover:text-[#D50E18] dark:hover:text-[#22C55E] font-semibold">{{ $post->category->name }}</a>
        @endif
    </nav>

    <h1 class="mb-4 font-serif text-2xl font-bold leading-tight text-gray-900 dark:text-[#F1F5F9] md:text-3xl">
        {{ $post->title }}
    </h1>

    @if($post->featured_image)
        <figure class="mb-6 overflow-hidden rounded-xl">
            <img src="{{ mc_image($post->featured_image) }}" alt="{{ $post->title }}" class="w-full object-cover" loading="eager" decoding="async"
                 onerror="this.onerror=null;this.src='{{ mc_placeholder_svg() }}'">
        </figure>
    @endif

    @if($post->video_url)
        <div class="mb-6 overflow-hidden rounded-2xl bg-black">
            <div class="relative aspect-video">
                <iframe src="{{ $post->video_url }}" class="absolute inset-0 h-full w-full" allowfullscreen allow="autoplay; encrypted-media; picture-in-picture" loading="lazy" title="{{ $post->title }}"></iframe>
            </div>
        </div>
    @endif

    <div class="article-body font-serif text-[1.02rem] leading-[2] text-gray-800 dark:text-gray-200">
        {!! $post->content !!}
    </div>

    @if($post->images->isNotEmpty())
        <section class="mt-8">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach($post->images as $image)
                    <figure class="group overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-800">
                        <img src="{{ mc_image($image->path) }}" alt="{{ $post->title }}" loading="lazy" decoding="async"
                             class="aspect-video w-full cursor-zoom-in object-cover transition-transform duration-500 group-hover:scale-105"
                             data-mc-lightbox="{{ mc_image($image->path) }}">
                    </figure>
                @endforeach
            </div>
        </section>
    @endif

    {{-- 3 Buttons in same row: Share (native), Copy Link, Comment - no emoji, direct icons, no custom modal --}}
    <div class="mt-8 border-t border-gray-200 dark:border-[#263246] pt-6">
        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" id="mc-share-btn" class="inline-flex items-center gap-2 rounded-full bg-gray-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-black dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100 transition-colors shadow-sm">
                <i class="ph ph-share-network text-base"></i> শেয়ার করুন
            </button>
            <button type="button" id="mc-copy-btn" class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-white px-5 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition-colors">
                <i class="ph ph-link text-base"></i> কপি লিংক
            </button>
            <button type="button" id="mc-comment-jump" class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-white px-5 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition-colors">
                <i class="ph ph-chat-circle text-base"></i> মন্তব্য করুন @if($post->comments->count()) <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs dark:bg-gray-700">{{ bn_count($post->comments->count()) }}</span> @endif
            </button>
        </div>
    </div>

    @if($post->allow_comments && filter_var(site_setting('comments_enabled', '1'), FILTER_VALIDATE_BOOLEAN))
        <section class="mt-6 rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-[#263246] dark:bg-[#182233]/50" id="comments">
            <h2 class="mb-4 font-serif text-lg font-bold text-gray-900 dark:text-[#F1F5F9]">মন্তব্য লিখুন</h2>

            @if($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif
            @if(session('success'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('comments.store') }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="commentable_type" value="post">
                <input type="hidden" name="commentable_id" value="{{ $post->id }}">
                <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">

                <div>
                    <label for="guest_name" class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">আপনার নাম <span class="text-[#E21D2B]">*</span></label>
                    <input type="text" id="guest_name" name="guest_name" value="{{ old('guest_name') }}" required minlength="2" maxlength="60"
                           placeholder="নাম লিখুন"
                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-[#D50E18] focus:ring-1 focus:ring-[#D50E18] dark:border-gray-600 dark:bg-[#0D1422] dark:text-white">
                </div>

                <div>
                    <label for="body" class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">মন্তব্য <span class="text-[#E21D2B]">*</span></label>
                    <textarea id="body" name="body" rows="4" required minlength="4" maxlength="2000"
                              placeholder="আপনার মন্তব্য লিখুন..."
                              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-[#D50E18] focus:ring-1 focus:ring-[#D50E18] dark:border-gray-600 dark:bg-[#0D1422] dark:text-white">{{ old('body') }}</textarea>
                </div>

                <button type="submit" class="rounded-lg bg-[#D50E18] px-6 py-2.5 text-sm font-bold text-white hover:bg-[#B9121E] transition-colors">
                    মন্তব্য পাঠান
                </button>
            </form>

            <div class="mt-8">
                <h3 class="mb-4 flex items-center gap-2 font-bold text-gray-900 dark:text-[#F1F5F9]">
                    <i class="ph ph-chat-circle text-[#E21D2B]"></i> মন্তব্যসমূহ @if($post->comments->count()) <span class="text-sm font-normal text-gray-500">({{ bn_count($post->comments->count()) }})</span> @endif
                </h3>
                <div class="space-y-3">
                    @forelse($post->comments as $comment)
                        <div class="flex gap-3 rounded-xl border border-gray-100 bg-white p-4 dark:border-[#263246] dark:bg-[#0D1422]">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#EAF7EC] font-serif font-bold text-[#1F7A3D] dark:bg-[#12291C] dark:text-[#7BCF95]">{{ mb_substr($comment->guest_name, 0, 1) }}</span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2 text-[13px] font-bold text-gray-900 dark:text-[#F1F5F9]">
                                    {{ $comment->guest_name }}
                                    <span class="text-[11px] font-normal text-gray-500 dark:text-[#94A3B8]">{{ bn_ago($comment->created_at) }}</span>
                                </div>
                                <p class="mt-1 text-sm leading-relaxed text-gray-700 dark:text-gray-300">{{ $comment->body }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-xl border border-dashed border-gray-300 py-8 text-center text-sm text-gray-500 dark:border-gray-600 dark:text-[#94A3B8]">এখনো কোনো মন্তব্য নেই — প্রথম মন্তব্যটি আপনিই করুন!</p>
                    @endforelse
                </div>
            </div>
        </section>
    @endif
</div>

<div id="mc-lightbox" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/90 p-4">
    <button type="button" class="absolute left-4 top-4 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20" data-mc-lightbox-close aria-label="বন্ধ করুন">
        <i class="ph-bold ph-x text-xl"></i>
    </button>
    <img id="mc-lightbox-img" src="" alt="" class="max-h-[88vh] max-w-full rounded-lg object-contain">
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const shareBtn = document.getElementById('mc-share-btn');
    const copyBtn = document.getElementById('mc-copy-btn');
    const commentJump = document.getElementById('mc-comment-jump');
    const commentsSection = document.getElementById('comments');
    const url = window.location.href;
    const title = document.title;

    if (shareBtn) {
        shareBtn.addEventListener('click', function() {
            if (navigator.share) {
                navigator.share({ title: title, url: url }).catch(() => {});
            } else {
                // No custom modal per requirement - fallback to copy on desktop
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(url).then(() => {
                        if (typeof mcToast !== 'undefined') mcToast('✓ নিউজ লিংক কপি হয়েছে');
                    });
                }
            }
        });
    }

    function copyLink() {
        const doToast = () => {
            if (typeof mcToast !== 'undefined') {
                mcToast('✓ নিউজ লিংক কপি হয়েছে');
            } else {
                const t = document.createElement('div');
                t.textContent = '✓ নিউজ লিংক কপি হয়েছে';
                t.className = 'fixed bottom-20 left-1/2 -translate-x-1/2 rounded-full bg-gray-900 text-white px-4 py-2 text-sm font-bold shadow-lg z-[200]';
                document.body.appendChild(t);
                setTimeout(() => t.remove(), 2500);
            }
            if (copyBtn) {
                const orig = copyBtn.innerHTML;
                copyBtn.innerHTML = '<i class="ph ph-check"></i> কপি হয়েছে';
                copyBtn.classList.add('border-emerald-500', 'bg-emerald-50', 'text-emerald-600');
                setTimeout(() => {
                    copyBtn.innerHTML = orig;
                    copyBtn.classList.remove('border-emerald-500', 'bg-emerald-50', 'text-emerald-600');
                }, 2000);
            }
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(doToast).catch(() => {
                const ta = document.createElement('textarea');
                ta.value = url;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                doToast();
            });
        } else {
            const ta = document.createElement('textarea');
            ta.value = url;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            doToast();
        }
    }

    if (copyBtn) copyBtn.addEventListener('click', copyLink);

    if (commentJump && commentsSection) {
        commentJump.addEventListener('click', () => {
            commentsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            const nameInput = document.getElementById('guest_name');
            if (nameInput) setTimeout(() => nameInput.focus(), 600);
        });
    }
});
</script>
@endpush
@endsection
