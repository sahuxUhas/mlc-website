{{-- ===== হেডার: লোগো + নাম + ট্যাগলাইন → 🔍 ☰ (ডেমোর ডিজাইন অক্ষুণ্ণ) ===== --}}
@php
    $currentPath = request()->path();
    $siteName    = site_setting('site_name', 'মহালছড়ি নিউজ');
    $prefix      = site_setting('site_prefix', 'দৈনিক');
    $tagline     = site_setting('site_tagline', 'পাহাড়ের কথা বলে');
    $domain      = site_setting('site_domain', 'mahalcharinews.com');
    $logoUrl     = site_setting('site_logo') ? asset(site_setting('site_logo')) : mc_placeholder_svg();
@endphp

<header id="site-header" class="sticky top-0 z-[80] border-b border-gray-200 bg-white/95 backdrop-blur transition-colors duration-200 dark:border-[#263246] dark:bg-[#0D1422]/95">
    <div class="container mx-auto px-3 sm:px-4">
        <div class="flex items-center justify-between gap-3 py-2 md:py-3">
            {{-- লোগো + ওয়ার্ডমার্ক --}}
            <a href="{{ route('home') }}" class="group flex min-w-0 items-center gap-2.5 text-left md:gap-3.5" aria-label="{{ $siteName }} হোমপেজ">
                <span class="relative inline-flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-[#C5E7C8] shadow-[0_2px_10px_rgba(11,11,11,0.10)] ring-1 ring-black/10 transition-transform duration-200 group-hover:-translate-y-0.5 sm:h-11 sm:w-11 md:h-[3.25rem] md:w-[3.25rem] md:rounded-xl">
                    <img src="{{ $logoUrl }}" alt="{{ $prefix }} {{ $siteName }}" class="h-full w-full object-cover object-center" loading="eager" onerror="this.onerror=null;this.src='{{ mc_placeholder_svg() }}'">
                </span>
                <span class="flex min-w-0 flex-col">
                    <span class="text-[10px] font-semibold uppercase tracking-[0.22em] text-gray-500 dark:text-[#94A3B8] md:text-xs">{{ $prefix }}</span>
                    <span class="whitespace-nowrap font-serif text-[1.1rem] font-black leading-none tracking-tight text-[#0B0B0B] dark:text-[#F1F5F9] sm:text-2xl md:text-[2.05rem] md:leading-[1.05]">
                        {{ site_setting('site_name_a', 'মহালছড়ি') }} <span class="text-[#E21D2B]">{{ site_setting('site_name_b', 'নিউজ') }}</span>
                    </span>
                    <span class="mt-0.5 flex items-center gap-1.5 whitespace-nowrap text-[10px] font-medium text-[#1F7A3D] dark:text-[#22C55E] sm:text-[11px] md:text-[13px]">
                        <span class="inline-block h-1 w-1 rounded-full bg-[#E21D2B]"></span>
                        {{ $tagline }}
                        <span class="ml-1 hidden font-sans text-[11px] tracking-wide text-gray-400 dark:text-[#94A3B8] md:inline">{{ $domain }}</span>
                    </span>
                </span>
            </a>

            {{-- ডান পাশের কন্ট্রোল --}}
            <div class="flex shrink-0 items-center gap-2 md:gap-2.5">
                <span class="mr-1 hidden text-right leading-tight lg:block">
                    <span class="block text-[11px] font-medium text-gray-500 dark:text-[#94A3B8]" data-mc-date>{{ bn_day_date() }}</span>
                    <span class="block text-[11px] font-bold text-[#D50E18]">আপডেট: <span data-mc-clock>--:--</span></span>
                </span>

                {{-- থিম টগল --}}
                <button type="button" data-mc-theme-toggle class="mc-icon-btn dark:border-[#263246] dark:bg-[#182233] dark:text-[#22C55E]" aria-label="থিম পরিবর্তন" title="ডার্ক/লাইট মোড">
                    <i class="ph-bold ph-moon text-[20px] dark:hidden"></i>
                    <i class="ph-bold ph-sun hidden text-[20px] text-amber-400 dark:block"></i>
                </button>

                {{-- সার্চ --}}
                <button type="button" data-mc-search-open class="mc-icon-btn dark:border-[#263246] dark:bg-[#182233] dark:text-[#F1F5F9]" aria-label="খুঁজুন" title="খুঁজুন">
                    <i class="ph ph-magnifying-glass text-[19px]"></i>
                </button>

                {{-- মেনু --}}
                <button type="button" data-mc-menu-open class="mc-icon-btn dark:border-[#263246] dark:bg-[#182233] dark:text-[#F1F5F9]" aria-label="সব মেনু" title="সব মেনু">
                    <i class="ph ph-list text-[19px]"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ===== ২য় সারি: বিভাগের নেভিগেশন (ডেস্কটপ) ===== --}}
    <nav class="hidden border-t border-black/10 bg-[#0B0B0B] text-white md:block dark:border-white/5">
        <div class="container mx-auto flex items-stretch justify-between gap-4 px-4">
            <ul class="hide-scrollbar flex items-stretch overflow-x-auto whitespace-nowrap">
                <li>
                    <a href="{{ route('home') }}" class="flex h-full items-center gap-1.5 px-3.5 py-2.5 text-[13px] font-semibold transition-colors hover:bg-[#D50E18] {{ request()->routeIs('home') ? 'bg-[#D50E18]' : '' }}">
                        <i class="ph-fill ph-house"></i> হোম
                    </a>
                </li>
                <li class="mx-px my-1.5 w-px bg-white/15"></li>

                @foreach($navCategories ?? [] as $category)
                    <li>
                        <a href="{{ route('category.show', $category->slug) }}"
                           class="block h-full px-3.5 py-2.5 text-[13px] font-semibold transition-colors hover:bg-[#D50E18] {{ request()->routeIs('category.show') && request()->route('slug') === $category->slug ? 'bg-[#D50E18]' : '' }}">
                            {{ $category->name }}
                        </a>
                    </li>
                @endforeach

                {{-- "আরও" ড্রপডাউন (মেনু ম্যানেজার থেকে নিয়ন্ত্রিত) --}}
                <li class="group relative">
                    <button type="button" class="flex h-full items-center gap-1 px-3.5 py-2.5 text-[13px] font-semibold hover:bg-[#D50E18]">
                        আরও <i class="ph ph-caret-down text-[10px]"></i>
                    </button>
                    <div class="invisible absolute left-0 top-full z-50 w-52 rounded-b-lg border border-gray-100 bg-white opacity-0 shadow-xl transition-all duration-200 group-hover:visible group-hover:opacity-100 dark:border-gray-700 dark:bg-[#182233]">
                        @forelse(($menu_more ?? collect()) as $item)
                            <a href="{{ $item->href }}" target="{{ $item->open_in_new_tab ? '_blank' : '_self' }}"
                               class="block px-4 py-2 text-right text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-[#263246]">
                                @if($item->icon)<i class="ph {{ $item->icon }} ml-1.5"></i>@endif {{ $item->label }}
                            </a>
                        @empty
                            <a href="{{ route('videos.index') }}" class="block px-4 py-2 text-right text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-[#263246]">ভিডিও</a>
                            <a href="{{ route('gallery.index') }}" class="block px-4 py-2 text-right text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-[#263246]">ফটো গ্যালারি</a>
                            <a href="{{ route('epaper.index') }}" class="block px-4 py-2 text-right text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-[#263246]">ই-পেপার</a>
                            <a href="{{ route('about') }}" class="block px-4 py-2 text-right text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-[#263246]">আমাদের সম্পর্কে</a>
                            <a href="{{ route('contact') }}" class="block px-4 py-2 text-right text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-[#263246]">যোগাযোগ</a>
                            <a href="{{ route('advertise') }}" class="block px-4 py-2 text-right text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-[#263246]">বিজ্ঞাপন দিন</a>
                        @endforelse
                    </div>
                </li>
            </ul>

            <div class="flex items-center gap-3 py-2.5">
                <a href="{{ route('videos.index') }}" class="flex items-center gap-2 rounded-full bg-red-600/15 px-3 py-1 text-[12px] font-bold text-red-400 ring-1 ring-red-500/40 hover:bg-[#D50E18] hover:text-white">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-red-500"></span>
                    </span>
                    লাইভ টিভি
                </a>
                <span class="hidden h-5 w-px bg-white/20 xl:block"></span>
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-1.5 text-[13px] font-semibold text-gray-200 hover:text-white">
                        <i class="ph ph-user-circle text-base"></i> {{ auth()->user()->name }}
                    </a>
                @else
                    <a href="{{ route('admin.login') }}" class="flex items-center gap-1.5 text-[13px] font-semibold text-gray-200 hover:text-white">
                        <i class="ph ph-sign-in text-base"></i> লগইন
                    </a>
                @endauth
            </div>
        </div>
    </nav>
</header>

{{-- ===== মোবাইল/সব স্ক্রিনের সাইড মেনু ড্রয়ার ===== --}}
<div id="mc-menu-drawer" class="fixed inset-0 z-[70] hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" data-mc-menu-close></div>
    <aside class="mc-slide-right absolute right-0 top-0 h-full w-[86%] max-w-sm overflow-y-auto bg-white shadow-2xl dark:bg-[#0D1422]">
        <div class="flex items-center justify-between border-b border-gray-200 p-4 dark:border-[#263246]">
            <span class="font-serif text-lg font-bold text-[#0B0B0B] dark:text-[#F1F5F9]">সব মেনু</span>
            <button type="button" data-mc-menu-close class="mc-icon-btn dark:border-[#263246] dark:bg-[#182233] dark:text-[#F1F5F9]" aria-label="বন্ধ করুন">
                <i class="ph-bold ph-x text-[20px]"></i>
            </button>
        </div>

        <nav class="p-4">
            <ul class="space-y-1">
                <li><a href="{{ route('home') }}" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-semibold text-gray-800 hover:bg-red-50 hover:text-[#D50E18] dark:text-[#F1F5F9] dark:hover:bg-[#182233]"><i class="ph ph-house text-lg"></i> হোম</a></li>
                <li><a href="{{ route('latest') }}" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-semibold text-gray-800 hover:bg-red-50 hover:text-[#D50E18] dark:text-[#F1F5F9] dark:hover:bg-[#182233]"><i class="ph ph-clock-counter-clockwise text-lg"></i> সর্বশেষ সংবাদ</a></li>

                @foreach($navCategories ?? [] as $category)
                    <li><a href="{{ route('category.show', $category->slug) }}" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-semibold text-gray-800 hover:bg-red-50 hover:text-[#D50E18] dark:text-[#F1F5F9] dark:hover:bg-[#182233]">
                        <i class="ph {{ $category->icon ?: 'ph-newspaper' }} text-lg" style="{{ $category->color ? 'color:'.$category->color : '' }}"></i> {{ $category->name }}
                    </a></li>
                @endforeach

                <li class="my-2 border-t border-gray-200 dark:border-[#263246]"></li>
                <li><a href="{{ route('videos.index') }}" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-semibold text-gray-800 hover:bg-red-50 hover:text-[#D50E18] dark:text-[#F1F5F9] dark:hover:bg-[#182233]"><i class="ph ph-play-circle text-lg"></i> ভিডিও</a></li>
                <li><a href="{{ route('gallery.index') }}" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-semibold text-gray-800 hover:bg-red-50 hover:text-[#D50E18] dark:text-[#F1F5F9] dark:hover:bg-[#182233]"><i class="ph ph-image-square text-lg"></i> ফটো গ্যালারি</a></li>
                <li><a href="{{ route('announcements.index') }}" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-semibold text-gray-800 hover:bg-red-50 hover:text-[#D50E18] dark:text-[#F1F5F9] dark:hover:bg-[#182233]"><i class="ph ph-megaphone text-lg"></i> ঘোষণা</a></li>
                <li><a href="{{ route('epaper.index') }}" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-semibold text-gray-800 hover:bg-red-50 hover:text-[#D50E18] dark:text-[#F1F5F9] dark:hover:bg-[#182233]"><i class="ph ph-newspaper text-lg"></i> ই-পেপার</a></li>
                <li><a href="{{ route('reporters.index') }}" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-semibold text-gray-800 hover:bg-red-50 hover:text-[#D50E18] dark:text-[#F1F5F9] dark:hover:bg-[#182233]"><i class="ph ph-user-focus text-lg"></i> রিপোর্টার</a></li>

                <li class="my-2 border-t border-gray-200 dark:border-[#263246]"></li>
                <li><a href="{{ route('about') }}" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-semibold text-gray-800 hover:bg-red-50 hover:text-[#D50E18] dark:text-[#F1F5F9] dark:hover:bg-[#182233]"><i class="ph ph-info text-lg"></i> আমাদের সম্পর্কে</a></li>
                <li><a href="{{ route('contact') }}" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-semibold text-gray-800 hover:bg-red-50 hover:text-[#D50E18] dark:text-[#F1F5F9] dark:hover:bg-[#182233]"><i class="ph ph-phone text-lg"></i> যোগাযোগ</a></li>
                <li><a href="{{ route('advertise') }}" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-semibold text-gray-800 hover:bg-red-50 hover:text-[#D50E18] dark:text-[#F1F5F9] dark:hover:bg-[#182233]"><i class="ph ph-currency-circle-dollar text-lg"></i> বিজ্ঞাপন</a></li>
                <li><a href="{{ route('submit-report') }}" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-semibold text-gray-800 hover:bg-red-50 hover:text-[#D50E18] dark:text-[#F1F5F9] dark:hover:bg-[#182233]"><i class="ph ph-article text-lg"></i> প্রতিবেদন দিন</a></li>
            </ul>
        </nav>
    </aside>
</div>
