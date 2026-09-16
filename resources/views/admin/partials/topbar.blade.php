<header class="sticky top-0 z-40 flex items-center justify-between gap-3 border-b border-slate-200 bg-white/95 px-4 py-3 backdrop-blur dark:border-slate-800 dark:bg-[#0B1120]/95 sm:px-6">
    <div class="flex items-center gap-3">
        <button type="button" id="openSidebar" class="rounded-lg border border-slate-300 p-2 text-slate-600 hover:bg-slate-100 lg:hidden dark:border-slate-700 dark:text-slate-300"><i class="ph-bold ph-list text-lg"></i></button>
        <div>
            <h1 class="font-serif text-base font-bold text-slate-900 dark:text-white sm:text-lg">@yield('title','ড্যাশবোর্ড')</h1>
            <p class="hidden text-[11px] text-slate-500 sm:block dark:text-slate-400">{{ bn_day_date() }}</p>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.news.create') }}" class="hidden items-center gap-1.5 rounded-lg bg-[#E21D2B] px-3 py-2 text-xs font-bold text-white hover:bg-[#B9121E] sm:flex"><i class="ph ph-plus"></i> নতুন সংবাদ</a>
        @if(auth()->user()->can_manage('comments.moderate'))
            <a href="{{ route('admin.comments.index') }}?status=pending" class="relative rounded-lg border border-slate-300 p-2 text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300" title="মন্তব্য মডারেশন">
                <i class="ph ph-chats-circle text-lg"></i>
                @php $pc = \App\Models\Comment::pending()->count(); @endphp
                @if($pc>0)<span class="absolute -left-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-amber-400 px-1 text-[9px] font-black text-gray-900">{{ bn_num($pc) }}</span>@endif
            </a>
        @endif
        <button type="button" id="adminThemeToggle" class="rounded-lg border border-slate-300 p-2 text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300" title="থিম">
            <i class="ph-bold ph-moon text-lg dark:hidden"></i><i class="ph-bold ph-sun hidden text-lg text-amber-400 dark:block"></i>
        </button>
        <a href="{{ route('admin.profile.edit') }}" class="rounded-lg border border-slate-300 p-2 text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300" title="প্রোফাইল"><i class="ph ph-user-circle text-lg"></i></a>
    </div>
</header>
