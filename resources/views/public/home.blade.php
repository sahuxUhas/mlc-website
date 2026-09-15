@extends('layouts.public')

@section('content')
<div id="home" class="container mx-auto max-w-6xl px-3 py-6 sm:px-4 md:py-8">

    {{-- ৪. Advertisement — Top Header (728x90) --}}
    @ad('top_header')

    {{-- ৫. সর্বশেষ প্রকাশিত একটি News Highlight --}}
    @if($highlight)
        <section class="mb-10">
            <div class="mb-4 flex items-center gap-2 border-b border-gray-200 pb-2 dark:border-[#263246]">
                <span class="h-2.5 w-2.5 rounded-full bg-[#E21D2B]"></span>
                <h2 class="font-serif text-lg font-bold text-gray-900 dark:text-[#F1F5F9] sm:text-xl">সর্বশেষ সংবাদ</h2>
            </div>

            <a href="{{ route('news.show', $highlight->slug) }}"
               class="group block cursor-pointer rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition-all hover:shadow-md dark:border-[#263246] dark:bg-[#182233] sm:p-6">
                <div class="grid grid-cols-1 items-center gap-6 lg:grid-cols-12">
                    <div class="relative aspect-video overflow-hidden rounded-xl bg-gray-100 lg:col-span-7 dark:bg-gray-800">
                        <img src="{{ $highlight->featured_image ? asset('uploads/'.$highlight->featured_image) : mc_placeholder_svg() }}"
                             alt="{{ $highlight->title }}" loading="eager" decoding="async"
                             class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                             onerror="this.onerror=null;this.src='{{ mc_placeholder_svg() }}'">
                        @if($highlight->category)
                            <span class="absolute left-3 top-3 rounded-md bg-[#E21D2B] px-2.5 py-1 text-xs font-bold text-white shadow-sm">{{ $highlight->category->name }}</span>
                        @endif
                        @if($highlight->is_breaking)
                            <span class="absolute right-3 top-3 flex items-center gap-1 rounded-md bg-[#0B0B0B]/85 px-2 py-1 text-[10px] font-bold text-white">
                                <i class="ph-fill ph-lightning text-[#E21D2B]"></i>ব্রেকিং নিউজ
                            </span>
                        @endif
                    </div>

                    <div class="flex flex-col justify-between space-y-3 lg:col-span-5">
                        <div>
                            <div class="mb-2 flex flex-wrap items-center gap-2 text-[11px] text-gray-500 dark:text-[#94A3B8]">
                                @if($highlight->reporter)
                                    <span><i class="ph ph-user-circle mr-1"></i>{{ $highlight->reporter->name }}</span>
                                @endif
                                <span><i class="ph ph-calendar-blank mr-1"></i>{{ bn_date($highlight->published_at) }}</span>
                                <span><i class="ph ph-eye mr-1"></i>{{ bn_count($highlight->views) }} বার</span>
                            </div>

                            <h3 class="font-serif text-xl font-bold leading-snug text-gray-900 transition-colors group-hover:text-[#E21D2B] dark:text-[#F1F5F9] dark:group-hover:text-[#22C55E] sm:text-2xl">
                                {{ $highlight->title }}
                            </h3>

                            @if($highlight->excerpt)
                                <p class="mt-2.5 line-clamp-3 text-xs leading-relaxed text-gray-600 dark:text-[#94A3B8] sm:text-sm">{{ mc_excerpt($highlight->excerpt, 240) }}</p>
                            @endif
                        </div>

                        <div class="flex items-center justify-between border-t border-gray-100 pt-2 dark:border-[#263246]">
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-[#E21D2B] group-hover:underline dark:text-[#22C55E]">
                                বিস্তারিত পড়ুন <i class="ph ph-arrow-right text-xs"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </section>
    @endif

    {{-- হোমপেজ অ্যাড --}}
    @ad('homepage')

    {{-- ৬. Category-wise News — প্রতিটি ক্যাটাগরি থেকে সর্বোচ্চ ২টি --}}
    <div class="space-y-12">
        @forelse($sections as $section)
            @php $category = $section['category']; $posts = $section['posts']; @endphp

            <section class="scroll-mt-24" id="cat-{{ $category->slug }}">
                <div class="mb-5 flex items-center justify-between border-b border-gray-200 pb-2 dark:border-[#263246]">
                    <div class="flex items-center gap-2.5">
                        <span class="h-5 w-2.5 rounded-sm bg-[#E21D2B]"></span>
                        <h2 class="font-serif text-xl font-bold text-gray-900 dark:text-[#F1F5F9] sm:text-2xl">{{ $category->name }}</h2>
                        @if($category->union_name)
                            <span class="hidden rounded-full bg-[#EAF7EC] px-2 py-0.5 text-[10px] font-bold text-[#1F7A3D] sm:inline dark:bg-[#182233] dark:text-[#22C55E]">{{ $category->union_name }}</span>
                        @endif
                    </div>
                    <a href="{{ route('category.show', $category->slug) }}" class="flex items-center gap-1 text-xs font-bold text-[#E21D2B] hover:underline dark:text-[#22C55E]">
                        <span>আরও সংবাদ</span><i class="ph ph-caret-right text-xs"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    @foreach($posts as $post)
                        @include('partials.news-card', ['post' => $post, 'category' => $category])
                    @endforeach
                </div>
            </section>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center dark:border-[#263246] dark:bg-[#182233]">
                <i class="ph ph-newspaper-clipping text-4xl text-gray-300 dark:text-gray-600"></i>
                <p class="mt-3 font-serif text-lg font-bold text-gray-700 dark:text-gray-300">এখনও কোনো সংবাদ প্রকাশিত হয়নি</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-[#94A3B8]">অ্যাডমিন প্যানেল থেকে সংবাদ যোগ করলে এখানে দেখা যাবে।</p>
                <a href="{{ route('admin.news.create') }}" class="mt-4 inline-block rounded-lg bg-[#D50E18] px-4 py-2 text-sm font-bold text-white hover:bg-[#B9121E]">নতুন সংবাদ যোগ করুন</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
