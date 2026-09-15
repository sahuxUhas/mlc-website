{{-- মডিউল পেজ হেডার: শিরোনাম + বর্ণনা + প্রধান অ্যাকশন বাটন --}}
@props(['title', 'subtitle' => null, 'action' => null, 'actionLabel' => null, 'actionIcon' => 'ph-plus'])
<div class="mb-5 flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-4 dark:border-slate-800">
    <div>
        <h1 class="font-serif text-lg font-bold text-slate-900 dark:text-white sm:text-xl">{{ $title }}</h1>
        @if($subtitle)<p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>@endif
    </div>
    @if($action && $actionLabel)
        <button type="button" data-mc-modal-open="{{ $action }}" class="mc-btn mc-btn-primary flex items-center gap-1.5">
            <i class="ph {{ $actionIcon }}"></i> {{ $actionLabel }}
        </button>
    @endif
</div>
