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
            ['@type' => 'ListItem', 'position' => 3, 'name' => $post->title],
        ],
    ], JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')
<div class="container mx-auto max-w-6xl px-3 py-6 sm:px-4 md:py-8">
    {{-- ব্রেডক্রাম্ব --}}
    <nav class="mb-4 flex flex-wrap items-center gap-1.5 text-[11px] font-semibold text-gray-500 dark:text-[#94A3B8]" aria-label="ব্রেডক্রাম্ব">
        <a href="{{ route('home') }}" class="hover:text-[#D50E18] dark:hover:text-[#22C55E]">হোম</a>
        <i class="ph ph-caret-left text-[9px]"></i>
        @if($post->category)
            <a href="{{ route('category.show', $post->category->slug) }}" class="hover:text-[#D50E18] dark:hover:text-[#22C55E]">{{ $post->category->name }}</a>
            <i class="ph ph-caret-left text-[9px]"></i>
        @endif
        <span class="line-clamp-1 text-gray-800 dark:text-[#F1F5F9]">{{ $post->title }}</span>
    </nav>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
        {{-- ===== মূল আর্টিকেল ===== --}}
        <article class="lg:col-span-8">
            @ad('article_top')

            <header class="mb-5">
                <div class="mb-3 flex flex-wrap items-center gap-2">
                    @if($post->category)
                        <a href="{{ route('category.show', $post->category->slug) }}" class="rounded-md bg-[#E21D2B] px-2.5 py-1 text-xs font-bold text-white hover:bg-[#B9121E]">{{ $post->category->name }}</a>
                    @endif
                    @if($post->subcategory)
                        <a href="{{ route('category.show', $post->subcategory->slug) }}" class="rounded-md bg-[#1F7A3D] px-2.5 py-1 text-xs font-bold text-white hover:opacity-90">{{ $post->subcategory->name }}</a>
                    @endif
                    @if($post->is_breaking)
                        <span class="flex items-center gap-1 rounded-md bg-[#0B0B0B] px-2 py-1 text-[10px] font-bold text-white">
                            <i class="ph-fill ph-lightning text-[#E21D2B]"></i>ব্রেকিং নিউজ
                        </span>
                    @endif
                    @if($post->is_featured)
                        <span class="flex items-center gap-1 rounded-md bg-amber-100 px-2 py-1 text-[10px] font-bold text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                            <i class="ph-fill ph-star"></i>ফিচার্ড
                        </span>
                    @endif
                </div>

                <h1 class="font-serif text-2xl font-bold leading-snug text-gray-900 dark:text-[#F1F5F9] sm:text-3xl md:text-[2.1rem] md:leading-[1.45]">
                    {{ $post->title }}
                </h1>

                @if($post->excerpt)
                    <p class="mt-3 border-r-4 border-[#E21D2B] bg-gray-50 px-4 py-3 text-sm font-medium leading-relaxed text-gray-700 dark:bg-[#182233] dark:text-gray-300">
                        {{ mc_excerpt($post->excerpt, 400) }}
                    </p>
                @endif

                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-y border-gray-200 py-3 dark:border-[#263246]">
                    <div class="flex flex-wrap items-center gap-3 text-xs text-gray-600 dark:text-[#94A3B8]">
                        @if($post->reporter)
                            <a href="{{ route('reporters.show', $post->reporter->slug) }}" class="flex items-center gap-2 font-semibold text-gray-800 hover:text-[#D50E18] dark:text-[#F1F5F9] dark:hover:text-[#22C55E]">
                                @if($post->reporter->photo)
                                    <img src="{{ mc_image($post->reporter->photo) }}" alt="{{ $post->reporter->name }}" class="h-8 w-8 rounded-full object-cover" loading="lazy">
                                @else
                                    <i class="ph-fill ph-user-circle text-2xl text-[#D50E18]"></i>
                                @endif
                                <span>{{ $post->reporter->name }}@if($post->reporter->designation)<span class="block text-[10px] font-normal text-gray-500">{{ $post->reporter->designation }}</span>@endif</span>
                            </a>
                        @endif
                        <span class="flex items-center gap-1"><i class="ph ph-calendar-blank text-[#D50E18]"></i>{{ bn_date($post->published_at) }}</span>
                        <span class="flex items-center gap-1"><i class="ph ph-clock text-[#D50E18]"></i>{{ bn_ago($post->published_at) }}</span>
                        <span class="flex items-center gap-1"><i class="ph ph-eye text-[#D50E18]"></i>{{ bn_count($post->views) }} বার পঠিত</span>
                        @if($post->location)
                            <span class="flex items-center gap-1"><i class="ph ph-map-pin text-[#1F7A3D]"></i>{{ $post->location }}</span>
                        @endif
                    </div>

                    {{-- শেয়ার বাটন --}}
                    <div class="flex items-center gap-1.5">
                        <span class="text-[11px] font-bold text-gray-500 dark:text-[#94A3B8]">শেয়ার:</span>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" rel="noopener" class="mc-icon-btn h-8 w-8 dark:border-[#263246] dark:bg-[#182233] dark:text-[#F1F5F9]" aria-label="ফেসবুকে শেয়ার"><i class="ph-fill ph-facebook-logo"></i></a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($post->title) }}" target="_blank" rel="noopener" class="mc-icon-btn h-8 w-8 dark:border-[#263246] dark:bg-[#182233] dark:text-[#F1F5F9]" aria-label="টুইটারে শেয়ার"><i class="ph-fill ph-x-logo"></i></a>
                        <a href="https://wa.me/?text={{ urlencode($post->title.' '.url()->current()) }}" target="_blank" rel="noopener" class="mc-icon-btn h-8 w-8 dark:border-[#263246] dark:bg-[#182233] dark:text-[#F1F5F9]" aria-label="হোয়াটসঅ্যাপে শেয়ার"><i class="ph-fill ph-whatsapp-logo"></i></a>
                        <button type="button" data-mc-copy="{{ url()->current() }}" class="mc-icon-btn h-8 w-8 dark:border-[#263246] dark:bg-[#182233] dark:text-[#F1F5F9]" aria-label="লিংক কপি"><i class="ph ph-link"></i></button>
                    </div>
                </div>
            </header>

            {{-- ফিচার্ড ইমেজ --}}
            @if($post->featured_image)
                <figure class="mb-6">
                    <div class="overflow-hidden rounded-2xl bg-gray-100 dark:bg-gray-800">
                        <img src="{{ mc_image($post->featured_image) }}" alt="{{ $post->image_caption ?: $post->title }}" class="w-full object-cover" loading="eager" decoding="async"
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

            {{-- ভিডিও (যদি থাকে) --}}
            @if($post->video_url)
                <div class="mb-6 overflow-hidden rounded-2xl bg-black">
                    <div class="relative aspect-video">
                        <iframe src="{{ $post->video_url }}" class="absolute inset-0 h-full w-full" allowfullscreen allow="autoplay; encrypted-media; picture-in-picture" loading="lazy" title="{{ $post->title }}"></iframe>
                    </div>
                </div>
            @endif

            {{-- ===== ফুল নিউজ কনটেন্ট ===== --}}
            <div class="article-body font-serif text-[1.02rem] leading-[2] text-gray-800 dark:text-gray-200">
                {!! $post->content !!}
            </div>

            @ad('article_middle')

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

            {{-- ট্যাগ --}}
            @if($post->tags->isNotEmpty())
                <div class="mt-8 flex flex-wrap items-center gap-2 border-t border-gray-200 pt-5 dark:border-[#263246]">
                    <span class="text-xs font-bold text-gray-500 dark:text-[#94A3B8]"><i class="ph ph-hash ml-1"></i>ট্যাগ:</span>
                    @foreach($post->tags as $tag)
                        <a href="{{ route('tag.show', $tag->slug) }}" class="mc-chip dark:border-gray-600 dark:bg-[#182233] dark:text-gray-300">{{ $tag->name }}</a>
                    @endforeach
                </div>
            @endif

            @ad('article_bottom')

            {{-- ===== মন্তব্য (Name + Comment, অ্যাকাউন্ট ছাড়াই) ===== --}}
            @if($post->allow_comments && filter_var(site_setting('comments_enabled', '1'), FILTER_VALIDATE_BOOLEAN))
                <section class="mt-8 rounded-2xl border border-gray-200 bg-white p-5 dark:border-[#263246] dark:bg-[#182233]" id="comments">
                    <h2 class="mb-4 flex items-center gap-2 font-serif text-lg font-bold text-gray-900 dark:text-[#F1F5F9]">
                        <i class="ph-fill ph-chats-circle text-[#E21D2B]"></i>
                        মন্তব্য <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-[#0D1422] dark:text-gray-400">{{ bn_count($post->comments->count()) }}</span>
                    </h2>

                    @if($errors->any())
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
                            <ul class="list-inside list-disc space-y-1">
                                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('comments.store') }}" method="POST" class="mb-6 space-y-3">
                        @csrf
                        <input type="hidden" name="commentable_type" value="post">
                        <input type="hidden" name="commentable_id" value="{{ $post->id }}">
                        {{-- হানিপট ফিল্ড — বট স্প্যাম রোধ --}}
                        <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label for="guest_name" class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">আপনার নাম <span class="text-[#E21D2B]">*</span></label>
                                <input type="text" id="guest_name" name="guest_name" value="{{ old('guest_name') }}" required minlength="2" maxlength="60"
                                       class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-[#D50E18] focus:ring-2 focus:ring-[#D50E18]/30 dark:border-gray-600 dark:bg-[#0D1422] dark:text-white">
                            </div>
                            <div>
                                <label for="guest_email" class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">ইমেইল (ঐচ্ছিক)</label>
                                <input type="email" id="guest_email" name="guest_email" value="{{ old('guest_email') }}" maxlength="190"
                                       class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-[#D50E18] focus:ring-2 focus:ring-[#D50E18]/30 dark:border-gray-600 dark:bg-[#0D1422] dark:text-white">
                            </div>
                        </div>

                        <div>
                            <label for="body" class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">আপনার মন্তব্য <span class="text-[#E21D2B]">*</span></label>
                            <textarea id="body" name="body" rows="4" required minlength="4" maxlength="2000"
                                      class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-[#D50E18] focus:ring-2 focus:ring-[#D50E18]/30 dark:border-gray-600 dark:bg-[#0D1422] dark:text-white">{{ old('body') }}</textarea>
                            <p class="mt-1 text-[11px] text-gray-500 dark:text-[#94A3B8]">মন্তব্য প্রকাশের আগে মডারেশনের মধ্য দিয়ে যাবে। অশালীন ভাষা ব্যবহার করলে প্রকাশিত হবে না।</p>
                        </div>

                        <button type="submit" class="rounded-lg bg-[#D50E18] px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-[#B9121E]">
                            <i class="ph ph-paper-plane-tilt ml-1"></i> মন্তব্য পাঠান
                        </button>
                    </form>

                    {{-- অনুমোদিত মন্তব্যের তালিকা --}}
                    <div class="space-y-4">
                        @forelse($post->comments as $comment)
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-[#263246] dark:bg-[#0D1422]">
                                <div class="mb-1.5 flex items-center justify-between gap-2">
                                    <span class="flex items-center gap-2 text-sm font-bold text-gray-900 dark:text-[#F1F5F9]">
                                        <i class="ph-fill ph-user-circle text-lg text-[#1F7A3D] dark:text-[#22C55E]"></i>{{ $comment->guest_name }}
                                    </span>
                                    <span class="text-[11px] text-gray-500 dark:text-[#94A3B8]">{{ bn_ago($comment->created_at) }}</span>
                                </div>
                                <p class="text-sm leading-relaxed text-gray-700 dark:text-gray-300">{{ $comment->body }}</p>
                                <form action="{{ route('comments.report', $comment) }}" method="POST" class="mt-2">
                                    @csrf
                                    <button type="submit" class="text-[11px] font-semibold text-gray-400 hover:text-[#E21D2B]">
                                        <i class="ph ph-flag ml-0.5"></i>রিপোর্ট করুন
                                    </button>
                                </form>
                            </div>
                        @empty
                            <p class="py-6 text-center text-sm text-gray-500 dark:text-[#94A3B8]">এখনও কোনো মন্তব্য নেই। প্রথম মন্তব্যটি আপনিই করুন!</p>
                        @endforelse
                    </div>
                </section>
            @endif
        </article>

        {{-- ===== সাইডবার ===== --}}
        <aside class="lg:col-span-4">
            @ad('sidebar')

            {{-- সর্বশেষ সংবাদ --}}
            <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-4 dark:border-[#263246] dark:bg-[#182233]">
                <h3 class="mb-3 flex items-center gap-2 border-b-2 border-[#D50E18] pb-2 font-serif text-base font-bold text-gray-900 dark:text-[#F1F5F9]">
                    <span class="h-5 w-1.5 rounded-sm bg-[#E21D2B]"></span> সর্বশেষ সংবাদ
                </h3>
                <ul class="space-y-3">
                    @foreach($latest as $item)
                        <li>
                            <a href="{{ route('news.show', $item->slug) }}" class="group flex items-start gap-3">
                                <img src="{{ $item->featured_image ? mc_image($item->featured_image) : mc_placeholder_svg() }}" alt="{{ $item->title }}" loading="lazy"
                                     class="h-14 w-20 shrink-0 rounded-lg object-cover" onerror="this.onerror=null;this.src='{{ mc_placeholder_svg() }}'">
                                <span class="min-w-0 flex-1">
                                    <span class="line-clamp-2 block text-[13px] font-bold leading-snug text-gray-800 group-hover:text-[#E21D2B] dark:text-[#F1F5F9] dark:group-hover:text-[#22C55E]">{{ $item->title }}</span>
                                    <span class="mt-1 flex items-center gap-2 text-[10px] text-gray-500 dark:text-[#94A3B8]">
                                        <span><i class="ph ph-clock"></i> {{ bn_ago($item->published_at) }}</span>
                                        <span><i class="ph ph-eye"></i> {{ bn_count($item->views) }}</span>
                                    </span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- সম্পর্কিত সংবাদ --}}
            @if($related->isNotEmpty())
                <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-4 dark:border-[#263246] dark:bg-[#182233]">
                    <h3 class="mb-3 flex items-center gap-2 border-b-2 border-[#D50E18] pb-2 font-serif text-base font-bold text-gray-900 dark:text-[#F1F5F9]">
                        <span class="h-5 w-1.5 rounded-sm bg-[#E21D2B]"></span> সম্পর্কিত সংবাদ
                    </h3>
                    <ul class="space-y-3">
                        @foreach($related as $item)
                            <li>
                                <a href="{{ route('news.show', $item->slug) }}" class="group flex items-start gap-3">
                                    <img src="{{ $item->featured_image ? mc_image($item->featured_image) : mc_placeholder_svg() }}" alt="{{ $item->title }}" loading="lazy" class="h-14 w-20 shrink-0 rounded-lg object-cover">
                                    <span class="line-clamp-3 text-[13px] font-bold leading-snug text-gray-800 group-hover:text-[#E21D2B] dark:text-[#F1F5F9] dark:group-hover:text-[#22C55E]">{{ $item->title }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ঘোষণা --}}
            @if(($activeAnnouncements ?? collect())->isNotEmpty())
                <div class="rounded-2xl border border-[#C5E7C8] bg-[#EAF7EC] p-4 dark:border-[#263246] dark:bg-[#182233]">
                    <h3 class="mb-3 flex items-center gap-2 font-serif text-base font-bold text-[#1F7A3D] dark:text-[#22C55E]">
                        <i class="ph-fill ph-megaphone"></i> গুরুত্বপূর্ণ ঘোষণা
                    </h3>
                    <ul class="space-y-2">
                        @foreach($activeAnnouncements as $announcement)
                            <li>
                                <a href="{{ route('announcements.show', $announcement->slug) }}" class="block rounded-lg bg-white/70 px-3 py-2 text-xs font-semibold text-gray-800 hover:bg-white dark:bg-[#0D1422] dark:text-[#F1F5F9]">
                                    {{ $announcement->title }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('announcements.index') }}" class="mt-3 block text-center text-xs font-bold text-[#1F7A3D] hover:underline dark:text-[#22C55E]">সব ঘোষণা দেখুন →</a>
                </div>
            @endif
        </aside>
    </div>
</div>

{{-- লাইটবক্স --}}
<div id="mc-lightbox" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/90 p-4">
    <button type="button" class="absolute left-4 top-4 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20" data-mc-lightbox-close aria-label="বন্ধ করুন">
        <i class="ph-bold ph-x text-xl"></i>
    </button>
    <img id="mc-lightbox-img" src="" alt="" class="max-h-[88vh] max-w-full rounded-lg object-contain">
</div>
@endsection
