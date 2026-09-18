{{-- ===== ফুটার — প্রতিটি অংশ অ্যাডমিন প্যানেল → সাইট সেটিংস → ফুটার থেকে নিয়ন্ত্রিত ===== --}}
@php
    $showAbout      = mc_flag('footer_show_about');
    $showCategories = mc_flag('footer_show_categories');
    $showLinks      = mc_flag('footer_show_links');
    $showContact    = mc_flag('footer_show_contact');
    $showSocial     = mc_flag('footer_show_social');
    $linksHeading   = site_setting('footer_heading_links', 'গুরুত্বপূর্ণ লিংক');
    $socialLinks    = $showSocial ? mc_social_links() : [];

    // কতটি কলাম দেখাবে — গ্রিড ক্লাস ঠিক রাখতে
    $columns = array_filter([$showAbout, $showCategories, $showLinks, $showContact]);
    $colCount = max(1, count($columns));
    $lgCols = match (true) {
        $colCount >= 4 => 'lg:grid-cols-4',
        $colCount === 3 => 'lg:grid-cols-3',
        $colCount === 2 => 'lg:grid-cols-2',
        default => 'lg:grid-cols-1',
    };

    // ফুটার মেনু (মেনু ম্যানেজার → ফুটার) — হার্ডকোড লিংকের পরিবর্তে
    $footerMenu = $menu_footer ?? collect();
@endphp
<footer class="mt-12 border-t border-gray-200 bg-[#0B0B0B] text-gray-300 dark:border-[#263246]">
    <div class="container mx-auto grid grid-cols-1 gap-8 px-4 py-10 md:grid-cols-2 {{ $lgCols }}">
        {{-- পরিচিতি --}}
        @if($showAbout)
            <div>
                <div class="mb-3 flex items-center gap-2.5">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-[#C5E7C8] ring-1 ring-white/10">
                        <img src="{{ mc_image(site_setting('site_logo')) }}" alt="{{ site_setting('site_name') }}" class="h-full w-full object-cover" loading="lazy" onerror="this.onerror=null;this.src='{{ mc_placeholder_svg() }}'">
                    </span>
                    <span class="flex flex-col">
                        <span class="text-[10px] font-semibold uppercase tracking-[0.22em] text-gray-400">{{ site_setting('site_prefix', 'দৈনিক') }}</span>
                        <span class="whitespace-nowrap font-serif text-xl font-black leading-none text-white">{{ site_setting('site_name_a', site_setting('site_name', 'মহালছড়ি নিউজ')) }} @if(site_setting('site_name_b'))<span class="text-[#E21D2B]">{{ site_setting('site_name_b') }}</span>@endif</span>
                    </span>
                </div>
                <p class="mb-3 text-xs font-medium text-[#22C55E]">{{ site_setting('site_tagline', 'পাহাড়ের কথা বলে') }}</p>
                <p class="text-sm leading-relaxed text-gray-400">
                    {{ site_setting('footer_about', 'মহালছড়ি উপজেলা, খাগড়াছড়ি ও পার্বত্য চট্টগ্রামের সর্বশেষ সংবাদ, ভিডিও ও ফটো গ্যালারি।') }}
                </p>
            </div>
        @endif

        {{-- বিভাগসমূহ --}}
        @if($showCategories)
            <div>
                <h3 class="mb-3 font-serif text-base font-bold text-white">বিভাগসমূহ</h3>
                <ul class="space-y-1.5 text-sm">
                    @foreach($footerCategories ?? [] as $category)
                        <li><a href="{{ route('category.show', $category->slug) }}" class="text-gray-400 transition-colors hover:text-[#E21D2B]">{{ $category->name }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- গুরুত্বপূর্ণ লিংক --}}
        @if($showLinks)
            <div>
                <h3 class="mb-3 font-serif text-base font-bold text-white">{{ $linksHeading }}</h3>
                <ul class="space-y-1.5 text-sm">
                    <li><a href="{{ route('latest') }}" class="text-gray-400 hover:text-[#E21D2B]">সর্বশেষ সংবাদ</a></li>
                    <li><a href="{{ route('videos.index') }}" class="text-gray-400 hover:text-[#E21D2B]">ভিডিও</a></li>
                    <li><a href="{{ route('announcements.index') }}" class="text-gray-400 hover:text-[#E21D2B]">ঘোষণা</a></li>
                    <li><a href="{{ route('gallery.index') }}" class="text-gray-400 hover:text-[#E21D2B]">ফটো গ্যালারি</a></li>
                    <li><a href="{{ route('epaper.index') }}" class="text-gray-400 hover:text-[#E21D2B]">ই-পেপার</a></li>
                    <li><a href="{{ route('reporters.index') }}" class="text-gray-400 hover:text-[#E21D2B]">রিপোর্টার</a></li>
                    @foreach($footerMenu as $item)
                        <li><a href="{{ $item->href }}" target="{{ $item->open_in_new_tab ? '_blank' : '_self' }}" class="text-gray-400 hover:text-[#E21D2B]">{{ $item->label }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- যোগাযোগ ও সোশ্যাল --}}
        @if($showContact)
            <div>
                <h3 class="mb-3 font-serif text-base font-bold text-white">যোগাযোগ</h3>
                <ul class="space-y-2 text-sm text-gray-400">
                    @if(site_setting('site_address'))
                        <li class="flex items-start gap-2"><i class="ph ph-map-pin mt-0.5 text-[#E21D2B]"></i><span>{{ site_setting('site_address') }}</span></li>
                    @endif
                    @if(site_setting('site_phone'))
                        <li class="flex items-center gap-2"><i class="ph ph-phone text-[#E21D2B]"></i><span dir="ltr">{{ site_setting('site_phone') }}</span></li>
                    @endif
                    @if(site_setting('site_email'))
                        <li class="flex items-center gap-2"><i class="ph ph-envelope-simple text-[#E21D2B]"></i><a href="mailto:{{ site_setting('site_email') }}" class="hover:text-white">{{ site_setting('site_email') }}</a></li>
                    @endif
                </ul>

                @if($socialLinks !== [])
                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        @foreach($socialLinks as $link)
                            <a href="{{ $link['url'] }}" target="_blank" rel="noopener" aria-label="{{ $link['label'] }}" title="{{ $link['label'] }}"
                               @if($link['color']) style="--mc-social: {{ $link['color'] }}" @endif
                               class="mc-icon-btn mc-social-btn h-9 w-9 border-white/10 bg-white/5 text-gray-300">
                                <i class="ph-fill {{ $link['icon'] }} text-lg"></i>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- ফুটার অ্যাড --}}
    @ad('footer')

    <div class="border-t border-white/10">
        <div class="container mx-auto flex flex-col items-center justify-between gap-2 px-4 py-4 text-xs text-gray-500 sm:flex-row">
            <p>{{ site_setting('copyright_text', '© '.bn_num(date('Y')).' '.site_setting('site_name', 'দৈনিক মহালছড়ি নিউজ').'. সর্বস্বত্ব সংরক্ষিত।') }}</p>
            <p>{{ site_setting('footer_text', 'ডিজাইন ও ডেভেলপমেন্ট: মহালছড়ি নিউজ টিম') }}</p>
        </div>
    </div>
</footer>
