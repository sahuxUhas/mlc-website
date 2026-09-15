@php
    $u = auth()->user();
    $pendingComments = \App\Models\Comment::pending()->count();
    $items = [
        ['group' => 'কনটেন্ট'],
        ['id'=>'dashboard','icon'=>'ph-squares-four','label'=>'ড্যাশবোর্ড','route'=>'admin.dashboard','perm'=>null],
        ['id'=>'news','icon'=>'ph-newspaper','label'=>'সংবাদ ব্যবস্থাপনা','route'=>'admin.news.index','perm'=>'news.view','badge'=>\App\Models\Post::count()],
        ['id'=>'add','icon'=>'ph-pencil-simple-line','label'=>'নতুন সংবাদ','route'=>'admin.news.create','perm'=>'news.create'],
        ['id'=>'trash','icon'=>'ph-trash-simple','label'=>'রিসাইকল বিন','route'=>'admin.trash.index','perm'=>'news.delete'],
        ['id'=>'categories','icon'=>'ph-list-dashes','label'=>'ক্যাটাগরি ও সাব-ক্যাটাগরি','route'=>'admin.categories.index','perm'=>'categories.manage'],
        ['id'=>'tags','icon'=>'ph-hash','label'=>'ট্যাগ ম্যানেজার','route'=>'admin.tags.index','perm'=>'tags.manage'],
        ['id'=>'media','icon'=>'ph-image-square','label'=>'মিডিয়া লাইব্রেরি','route'=>'admin.media.index','perm'=>'media.upload'],
        ['id'=>'breaking','icon'=>'ph-lightning','label'=>'ব্রেকিং নিউজ','route'=>'admin.breaking.index','perm'=>'breaking.manage'],
        ['id'=>'videos','icon'=>'ph-monitor-play','label'=>'ভিডিও','route'=>'admin.videos.index','perm'=>'videos.manage'],
        ['id'=>'announcements','icon'=>'ph-megaphone','label'=>'ঘোষণা','route'=>'admin.announcements.index','perm'=>'announcements.manage'],
        ['id'=>'albums','icon'=>'ph-images','label'=>'ফটো গ্যালারি','route'=>'admin.albums.index','perm'=>'albums.manage'],
        ['group' => 'সম্প্রদায়'],
        ['id'=>'comments','icon'=>'ph-chats-circle','label'=>'মন্তব্য মডারেশন','route'=>'admin.comments.index','perm'=>'comments.moderate','badge'=>$pendingComments,'badgeTone'=>'amber'],
        ['id'=>'reports','icon'=>'ph-article','label'=>'পাঠকের প্রতিবেদন','route'=>'admin.reports.index','perm'=>'reports.manage'],
        ['id'=>'messages','icon'=>'ph-envelope-simple','label'=>'যোগাযোগ বার্তা','route'=>'admin.messages.index','perm'=>null,'badge'=>\App\Models\ContactMessage::unread()->count()],
        ['id'=>'newsletter','icon'=>'ph-envelope','label'=>'নিউজলেটার','route'=>'admin.newsletter.index','perm'=>null],
        ['group' => 'গ্রোথ'],
        ['id'=>'ads','icon'=>'ph-currency-circle-dollar','label'=>'বিজ্ঞাপন ম্যানেজার','route'=>'admin.ads.index','perm'=>'ads.manage'],
        ['group' => 'সেটআপ'],
        ['id'=>'menus','icon'=>'ph-list-bullets','label'=>'মেনু ম্যানেজার','route'=>'admin.menus.index','perm'=>'menus.manage'],
        ['id'=>'pages','icon'=>'ph-file-text','label'=>'স্ট্যাটিক পেজ','route'=>'admin.pages.index','perm'=>'pages.manage'],
        ['id'=>'epapers','icon'=>'ph-newspaper-clipping','label'=>'ই-পেপার','route'=>'admin.epapers.index','perm'=>'epapers.manage'],
        ['id'=>'reporters','icon'=>'ph-user-focus','label'=>'রিপোর্টার ও লেখক','route'=>'admin.reporters.index','perm'=>'reporters.manage'],
        ['id'=>'users','icon'=>'ph-users','label'=>'ইউজার ও রোল','route'=>'admin.users.index','perm'=>null,'superOnly'=>true],
        ['id'=>'settings','icon'=>'ph-gear','label'=>'সাইট সেটিংস','route'=>'admin.settings.edit','perm'=>'settings.manage'],
        ['id'=>'seo','icon'=>'ph-magnifying-glass','label'=>'SEO সেটিংস','route'=>'admin.seo.edit','perm'=>'settings.manage'],
        ['id'=>'activity','icon'=>'ph-clock-counter-clockwise','label'=>'অ্যাক্টিভিটি লগ','route'=>'admin.activity.index','perm'=>'activity.view'],
    ];
@endphp

<aside class="fixed left-0 top-0 bottom-0 z-50 flex w-64 flex-col overflow-hidden bg-gray-900 text-white dark:bg-[#0B1120]">
    <div class="flex items-center justify-between border-b border-gray-800 p-5">
        <a href="{{ route('home') }}" class="font-serif text-lg font-bold"><span class="text-[#E21D2B]">{{ site_setting('site_prefix','দৈনিক') }} {{ site_setting('site_name','মহালছড়ি নিউজ') }}</span></a>
        <button type="button" id="closeSidebar" class="rounded p-1 text-gray-400 hover:text-white lg:hidden"><i class="ph-bold ph-x text-lg"></i></button>
    </div>
    <div class="flex items-center gap-3 border-b border-gray-800 p-4">
        @if($u->avatar)<img src="{{ mc_image($u->avatar) }}" alt="" class="h-9 w-9 rounded-full object-cover">@else<span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#E21D2B] font-bold"><i class="ph-fill ph-user"></i></span>@endif
        <div class="min-w-0">
            <p class="truncate text-sm font-bold">{{ $u->name }}</p>
            <p class="truncate text-xs capitalize text-gray-400">{{ $u->roleLabel() }}</p>
        </div>
    </div>
    <nav class="mc-scroll flex-1 overflow-y-auto py-3">
        <ul class="space-y-1 px-2">
            @foreach($items as $item)
                @if(isset($item['group']))
                    <li class="px-4 pb-1 pt-4 text-[10px] font-bold uppercase tracking-[0.18em] text-gray-500">{{ $item['group'] }}</li>
                @elseif(!empty($item['superOnly']) && !$u->isSuperAdmin())
                    @continue
                @elseif(!empty($item['perm']) && !$u->can_manage($item['perm']))
                    @continue
                @else
                    <li>
                        <a href="{{ route($item['route']) }}" class="flex items-center rounded-lg px-4 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs($item['route']) || ($item['id']==='news' && request()->routeIs('admin.news.*')) ? 'bg-[#E21D2B] text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                            <i class="ph {{ $item['icon'] }} ml-3 text-xl"></i>
                            <span class="flex-1 text-right">{{ $item['label'] }}</span>
                            @if(!empty($item['badge']) && $item['badge'] > 0)
                                <span class="mr-1 rounded px-1.5 text-[10px] font-black {{ ($item['badgeTone'] ?? 'slate')==='amber' ? 'bg-amber-400 text-gray-900' : 'bg-gray-700 text-gray-300' }}">{{ bn_num($item['badge']) }}</span>
                            @endif
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
    </nav>
    <div class="border-t border-gray-800 p-3">
        <a href="{{ route('home') }}" target="_blank" class="mb-2 flex items-center justify-center gap-2 rounded-lg bg-gray-800 px-3 py-2 text-xs font-bold text-gray-300 hover:bg-gray-700"><i class="ph ph-globe"></i> ওয়েবসাইট দেখুন</a>
        <form action="{{ route('admin.logout') }}" method="POST">@csrf
            <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-lg bg-red-600/15 px-3 py-2 text-xs font-bold text-red-400 hover:bg-[#E21D2B] hover:text-white"><i class="ph ph-sign-out"></i> লগআউট</button>
        </form>
    </div>
</aside>
