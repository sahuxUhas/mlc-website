{{-- ===== নিউজ কার্ড (ডেমোর কার্ড ডিজাইন হুবহু) ===== --}}
@props(['post', 'category' => null, 'variant' => 'default'])
@php
    $cat = $category ?? $post->category;
    $img = $post->featured_image ? mc_image($post->featured_image) : mc_placeholder_svg();
@endphp

<a href="{{ route('news.show', $post->slug) }}"
   class="group flex flex-col justify-between rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition-all hover:shadow-md dark:border-[#263246] dark:bg-[#182233]">
    <div class="flex flex-col gap-4 sm:flex-row">
        <div class="relative aspect-video shrink-0 overflow-hidden rounded-xl bg-gray-100 sm:w-44 dark:bg-gray-800">
            <img src="{{ $img }}" alt="{{ $post->title }}" loading="lazy" decoding="async"
                 class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                 onerror="this.onerror=null;this.src='{{ mc_placeholder_svg() }}'">
            @if($cat)
                <span class="absolute left-2 top-2 rounded bg-[#E21D2B] px-2 py-0.5 text-[10px] font-bold text-white shadow-sm">{{ $cat->name }}</span>
            @endif
            @if($post->is_breaking)
                <span class="absolute right-2 top-2 flex items-center gap-1 rounded bg-[#0B0B0B]/85 px-1.5 py-0.5 text-[9px] font-bold text-white">
                    <i class="ph-fill ph-lightning text-[8px] text-[#E21D2B]"></i>ব্রেকিং
                </span>
            @endif
        </div>

        <div class="flex min-w-0 flex-1 flex-col justify-between">
            <div>
                <h3 class="line-clamp-3 font-serif text-[15px] font-bold leading-snug text-gray-900 transition-colors group-hover:text-[#E21D2B] dark:text-[#F1F5F9] dark:group-hover:text-[#22C55E] sm:text-base">
                    {{ $post->title }}
                </h3>
                @if($post->excerpt)
                    <p class="mt-1.5 line-clamp-2 text-xs leading-relaxed text-gray-600 dark:text-[#94A3B8]">{{ mc_excerpt($post->excerpt, 140) }}</p>
                @endif
            </div>

            <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-2 text-[11px] text-gray-500 dark:border-[#263246] dark:text-[#94A3B8]">
                <div class="flex items-center gap-3">
                    <span><i class="ph ph-clock mr-1 text-[#E21D2B]"></i>{{ bn_date($post->published_at, false) }}</span>
                    <span><i class="ph ph-eye mr-1 text-[#E21D2B]"></i>{{ bn_count($post->views) }} বার</span>
                </div>
                <span class="flex items-center gap-0.5 font-bold text-[#E21D2B] group-hover:underline dark:text-[#22C55E]">
                    বিস্তারিত পড়ুন <i class="ph ph-arrow-right text-xs"></i>
                </span>
            </div>
        </div>
    </div>
</a>
