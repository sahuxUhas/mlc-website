@extends('layouts.public')

@section('title', ($post->meta_title ?: $post->title).' | '.site_setting('site_name', config('app.name')))
@section('meta_description', $post->meta_description ?: mc_excerpt($post->excerpt ?: $post->content, 300))
@section('meta_keywords', $post->meta_keywords ?: $post->tags->pluck('name')->implode(', '))
@section('og_title', $post->og_title ?: $post->title)
@section('og_description', $post->og_description ?: mc_excerpt($post->excerpt ?: $post->content, 240))
@section('og_type', 'article')
@section('og_image', $post->og_image ? mc_image($post->og_image) : ($post->featured_image ? mc_image($post->featured_image) : ''))
@section('canonical', $post->canonical_url ?: route('news.show', $post->slug))

@php
    // শেয়ারের জন্য canonical URL (SEO-র canonical-এর সাথে সামঞ্জস্যপূর্ণ)
    $shareUrl     = $post->canonical_url ?: route('news.show', $post->slug);
    $shareTitle   = $post->title;
    $commentsOn   = $post->allow_comments && filter_var(site_setting('comments_enabled', '1'), FILTER_VALIDATE_BOOLEAN);
    $formHasError = $errors->any();
@endphp

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
            ['@type' => 'ListItem', 'position' => 3, 'name' => $post->title],
        ],
    ], JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')
{{--
    সংবাদের বিস্তারিত পেজ — চূড়ান্ত কাঠামো:
    শিরোনাম → ফিচার্ড ছবি → নিউজ কনটেন্ট → শেয়ার → ফেসবুক ফলো → মন্তব্য → ফুটার
    (ডিজাইন টোকেন/কালার সিস্টেম আগের মতোই; এখানে কেবল অপ্রয়োজনীয় অংশ বাদ দেওয়া হয়েছে)
--}}
<article class="mc-article container mx-auto max-w-6xl px-3 py-6 sm:px-4 md:py-8">
    <div class="mc-article-shell mx-auto">

        {{-- ===== ১. শিরোনাম (পুরো পেজে একবারই) ===== --}}
        <h1 class="mb-5 font-serif text-2xl font-bold leading-snug text-gray-900 dark:text-[#F1F5F9] sm:text-3xl md:text-[2.1rem] md:leading-[1.45]">
            {{ $post->title }}
        </h1>

        {{-- ===== ২. ফিচার্ড ছবি (ডাটাবেস থেকে; নিচে কোনো duplicate শিরোনাম নয়) ===== --}}
        @if($post->featured_image)
            <figure class="mb-6">
                <div class="overflow-hidden rounded-2xl bg-gray-100 dark:bg-gray-800">
                    <img src="{{ mc_image($post->featured_image) }}"
                         alt="{{ $post->image_caption ?: $post->title }}"
                         class="mc-article-hero w-full object-cover" loading="eager" decoding="async"
                         onerror="this.onerror=null;this.src='{{ mc_placeholder_svg() }}'">
                </div>
                @if($post->image_caption || $post->image_credit)
                    <figcaption class="mt-2 text-xs text-gray-500 dark:text-[#94A3B8]">
                        @if($post->image_caption){{ $post->image_caption }}@endif
                        @if($post->image_credit)<span class="mr-2 font-semibold">— ছবি: {{ $post->image_credit }}</span>@endif
                    </figcaption>
                @endif
            </figure>
        @endif

        {{-- বিজ্ঞাপন স্লট (সেট করা থাকলে) — কনটেন্টের ঠিক আগে --}}
        @ad('article_top')

        {{-- ===== ৩. নিউজ কনটেন্ট (ডাটাবেসের আসল কনটেন্ট) ===== --}}
        <div class="article-body font-serif text-[1.02rem] leading-[2] text-gray-800 dark:text-gray-200">
            {!! $post->content !!}
        </div>

        @ad('article_middle')

        {{-- ভিডিও (news-এ সেট করা থাকলে — বিদ্যমান মিডিয়া সিস্টেম) --}}
        @if($post->video_url)
            <div class="mt-6 overflow-hidden rounded-2xl bg-black">
                <div class="relative aspect-video">
                    <iframe src="{{ $post->video_url }}" class="absolute inset-0 h-full w-full" allowfullscreen allow="autoplay; encrypted-media; picture-in-picture" loading="lazy" title="{{ $post->title }}"></iframe>
                </div>
            </div>
        @endif

        {{-- গ্যালারি — একাধিক ছবি (Order অনুযায়ী) --}}
        @if($post->images->isNotEmpty())
            <section class="mt-8">
                <h2 class="mb-3 flex items-center gap-2 font-serif text-lg font-bold text-gray-900 dark:text-[#F1F5F9]">
                    <span class="h-5 w-1.5 rounded-sm bg-[#E21D2B]"></span> ছবি ঘর
                </h2>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach($post->images as $image)
                        <figure class="group overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-800">
                            <img src="{{ mc_image($image->path) }}" alt="{{ $image->caption ?: $post->title }}" loading="lazy" decoding="async"
                                 class="aspect-video w-full cursor-zoom-in object-cover transition-transform duration-500 group-hover:scale-105"
                                 data-mc-lightbox="{{ mc_image($image->path) }}">
                            @if($image->caption)
                                <figcaption class="px-2 py-1.5 text-[11px] text-gray-600 dark:text-[#94A3B8]">{{ $image->caption }}</figcaption>
                            @endif
                        </figure>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ===== ৪. শেয়ার — Facebook / Messenger / WhatsApp / Telegram / X / Copy Link ===== --}}
        <section class="mt-6 rounded-2xl border border-gray-200 bg-white p-4 dark:border-[#263246] dark:bg-[#182233] sm:p-5" aria-labelledby="mc-share-title">
            <div class="mc-sec-head">
                <h2 id="mc-share-title" class="mc-sec-title"><i class="ph-fill ph-share-network text-[#E21D2B]"></i> সংবাদটি শেয়ার করুন</h2>
                <span class="mc-copy-msg" data-mc-copy-msg role="status" aria-live="polite"></span>
            </div>

            <div class="mc-share-grid">
                <a class="mc-share-btn mc-share-fb" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}"
                   target="_blank" rel="noopener noreferrer" aria-label="ফেসবুকে শেয়ার করুন">
                    <i class="ph-fill ph-facebook-logo"></i><span>Facebook</span>
                </a>

                <button type="button" class="mc-share-btn mc-share-msgr" data-mc-share-messenger
                        data-url="{{ $shareUrl }}" data-title="{{ $shareTitle }}"
                        @if($facebookAppId) data-app-id="{{ $facebookAppId }}" @endif
                        aria-label="Messenger-এ শেয়ার করুন">
                    <i class="ph-fill ph-messenger-logo"></i><span>Messenger</span>
                </button>

                <a class="mc-share-btn mc-share-wa" href="https://wa.me/?text={{ urlencode($shareTitle.' '.$shareUrl) }}"
                   target="_blank" rel="noopener noreferrer" aria-label="হোয়াটসঅ্যাপে শেয়ার করুন">
                    <i class="ph-fill ph-whatsapp-logo"></i><span>WhatsApp</span>
                </a>

                <a class="mc-share-btn mc-share-tg" href="https://t.me/share/url?url={{ urlencode($shareUrl) }}&text={{ urlencode($shareTitle) }}"
                   target="_blank" rel="noopener noreferrer" aria-label="টেলিগ্রামে শেয়ার করুন">
                    <i class="ph-fill ph-telegram-logo"></i><span>Telegram</span>
                </a>

                <a class="mc-share-btn mc-share-x" href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($shareTitle) }}"
                   target="_blank" rel="noopener noreferrer" aria-label="X-এ শেয়ার করুন">
                    <i class="ph-fill ph-x-logo"></i><span>X</span>
                </a>

                <button type="button" class="mc-share-btn mc-share-copy" data-mc-share-copy data-url="{{ $shareUrl }}"
                        aria-label="নিউজের লিংক কপি করুন">
                    <i class="ph ph-link"></i><span>কপি লিংক</span>
                </button>
            </div>

            {{-- মোবাইলে native share (navigator.share) থাকলে এই বাটনটি দেখাবে --}}
            <button type="button" class="mc-native-share" data-mc-share-native data-url="{{ $shareUrl }}" data-title="{{ $shareTitle }}">
                <i class="ph ph-share-fat"></i> সবার সাথে শেয়ার করুন
            </button>
        </section>

        {{-- ===== ৫. ফেসবুক ফলো (URL সেটিংস থেকে) ===== --}}
        @if($facebookFollowUrl)
            <section class="mc-follow mt-5">
                <div class="mc-follow-text">
                    <i class="ph-fill ph-facebook-logo mc-follow-ico" aria-hidden="true"></i>
                    <p>মহালছড়ির প্রতিটি খবর সবার আগে পেতে মহালছড়ি নিউজ-এর ফেসবুক পেজ ফলো করুন।</p>
                </div>
                <a class="mc-follow-btn" href="{{ $facebookFollowUrl }}" target="_blank" rel="noopener">
                    <i class="ph-fill ph-user-plus"></i> ফেসবুকে ফলো করুন
                </a>
            </section>
        @endif

        {{-- ===== ৬. মন্তব্য (compact — Name + Comment, লগইন ছাড়াই) ===== --}}
        @if($commentsOn)
            <section id="comments" class="mt-6 rounded-2xl border border-gray-200 bg-white p-4 dark:border-[#263246] dark:bg-[#182233] sm:p-5"
                     aria-labelledby="mc-comments-title">
                @if(session('mc_comment_submitted'))
                    <p class="mc-note-ok" role="status">
                        <i class="ph-fill ph-check-circle"></i>
                        <span>আপনার মন্তব্য পর্যালোচনার জন্য পাঠানো হয়েছে। অনুমোদনের পর এটি প্রকাশিত হবে।</span>
                    </p>
                @endif

                <div class="mc-sec-head">
                    <h2 id="mc-comments-title" class="mc-sec-title">
                        <i class="ph-fill ph-chats-circle text-[#E21D2B]"></i> মন্তব্য
                        @if($commentCount > 0)<span class="mc-count">{{ bn_num($commentCount) }}</span>@endif
                    </h2>
                    <button type="button" class="mc-comment-toggle" data-mc-comment-toggle
                            data-open-label="💬 মন্তব্য করুন" data-close-label="বন্ধ করুন"
                            aria-expanded="{{ $formHasError ? 'true' : 'false' }}" aria-controls="mc-comment-form">
                        <i class="ph ph-chat-circle-dots" aria-hidden="true"></i><span>💬 মন্তব্য করুন</span>
                    </button>
                </div>

                {{-- ভ্যালিডেশনের বার্তা (ডুপ্লিকেট ফ্ল্যাশ নয় — শুধু এরর ব্যাগ) --}}
                @if($formHasError)
                    <div class="mc-errors" role="alert">
                        <ul>
                            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <div id="mc-comment-form" class="mc-comment-form {{ $formHasError ? 'is-open' : '' }}">
                    <form action="{{ route('comments.store') }}" method="POST" class="space-y-3">
                        @csrf
                        <input type="hidden" name="commentable_type" value="post">
                        <input type="hidden" name="commentable_id" value="{{ $post->id }}">
                        {{-- হানিপট ফিল্ড — বট স্প্যাম রোধ --}}
                        <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">

                        <div>
                            <label for="mc-guest-name" class="mc-form-label">আপনার নাম <span class="text-[#E21D2B]">*</span></label>
                            <input type="text" id="mc-guest-name" name="guest_name" value="{{ old('guest_name') }}"
                                   placeholder="নাম লিখুন" required minlength="2" maxlength="60" autocomplete="name"
                                   class="mc-form-input">
                        </div>

                        <div>
                            <label for="mc-comment-body" class="mc-form-label">আপনার মন্তব্য <span class="text-[#E21D2B]">*</span></label>
                            <textarea id="mc-comment-body" name="body" rows="3" placeholder="মন্তব্য লিখুন" required minlength="4" maxlength="2000"
                                      class="mc-form-input">{{ old('body') }}</textarea>
                        </div>

                        <button type="submit" class="mc-submit-btn">
                            <i class="ph ph-paper-plane-tilt" aria-hidden="true"></i> মন্তব্য পাঠান
                        </button>
                    </form>
                </div>

                {{-- শুধুমাত্র অনুমোদিত মন্তব্য (pending/rejected/spam কখনো দেখাবে না) --}}
                <div class="mc-comments">
                    @forelse($post->comments as $comment)
                        @include('partials.comment-item', ['comment' => $comment, 'post' => $post, 'isReply' => false])
                    @empty
                        <p class="mc-empty">এখনও কোনো মন্তব্য নেই। প্রথম মন্তব্যটি করুন।</p>
                    @endforelse
                </div>
            </section>
        @endif

        @ad('article_bottom')
    </div>
</article>

{{-- লাইটবক্স --}}
<div id="mc-lightbox" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/90 p-4">
    <button type="button" class="absolute left-4 top-4 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20" data-mc-lightbox-close aria-label="বন্ধ করুন">
        <i class="ph-bold ph-x text-xl"></i>
    </button>
    <img id="mc-lightbox-img" src="" alt="" class="max-h-[88vh] max-w-full rounded-lg object-contain">
</div>
@endsection

@push('scripts')
    @if(session('mc_comment_submitted') || $formHasError)
        <script>
            // মন্তব্য জমা/ভুল হলে সরাসরি মন্তব্য অংশে স্ক্রোল
            (function () {
                var box = document.getElementById('comments');
                if (box) { box.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
            })();
        </script>
    @endif
@endpush
