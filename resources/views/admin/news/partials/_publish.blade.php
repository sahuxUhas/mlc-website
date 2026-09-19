{{-- ================= প্রকাশ, স্ট্যাটাস, তারিখ-সময়, ফ্ল্যাগ ================= --}}
<div class="mc-card p-4">
    <h2 class="mb-4 flex items-center gap-2 font-serif text-sm font-bold text-slate-900 dark:text-white">
        <i class="ph ph-paper-plane-tilt text-[#E21D2B]"></i> প্রকাশ ও স্ট্যাটাস
    </h2>

    <div class="space-y-4">
        {{-- স্ট্যাটাস --}}
        <div>
            <label for="status" class="mc-label">স্ট্যাটাস</label>
            <select id="status" name="status" class="mc-input @error('status') border-red-400 @enderror">
                @foreach($statusOptions as $key => $label)
                    <option value="{{ $key }}" @selected($currentStatus === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <p class="mc-tip">খসড়া → রিভিউতে → প্রকাশিত। “নির্ধারিত” দিলে নিচের তারিখ-সময় অনুযায়ী স্বয়ংক্রিয়ভাবে প্রকাশিত হবে।</p>
            @error('status')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
        </div>

        {{-- প্রকাশের তারিখ ও সময় --}}
        <div>
            <label class="mc-label">প্রকাশের তারিখ ও সময় (Publish Date &amp; Time)</label>
            <div class="grid grid-cols-2 gap-2">
                <input type="date" id="published_date" name="published_date" value="{{ $publishedDate }}"
                       class="mc-input text-xs @error('published_date') border-red-400 @enderror">
                <input type="time" id="published_time" name="published_time" value="{{ $publishedTime }}"
                       class="mc-input text-xs @error('published_time') border-red-400 @enderror">
            </div>
            <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                <button type="button" class="mc-btn mc-btn-ghost text-[11px]" data-set-now="#published_date,#published_time">এখন</button>
                <button type="button" class="mc-btn mc-btn-ghost text-[11px]" data-set-time="09:00" data-set-target="#published_date,#published_time">আজ সকাল ৯:০০</button>
                <button type="button" class="mc-btn mc-btn-ghost text-[11px]" data-shift="+1 day" data-set-target="#published_date,#published_time">আগামীকাল</button>
            </div>
            <p class="mc-tip">খালি রাখলে সংরক্ষণের মুহূর্তে বর্তমান সময় বসবে। <span id="published-human" class="font-bold text-slate-600 dark:text-slate-300"></span></p>
            @error('published_date')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
            @error('published_time')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
            @error('published_at')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
        </div>

        {{-- নির্ধারিত প্রকাশ --}}
        <div class="rounded-lg border border-indigo-200 bg-indigo-50/60 p-3 dark:border-indigo-900 dark:bg-indigo-950/30">
            <label class="mc-label mb-1.5 flex items-center gap-1 text-indigo-900 dark:text-indigo-200">
                <i class="ph ph-calendar-check"></i> নির্ধারিত প্রকাশ (Schedule)
            </label>
            <div class="grid grid-cols-2 gap-2">
                <input type="date" id="scheduled_date" name="scheduled_date" value="{{ $scheduledDate }}"
                       class="mc-input text-xs @error('scheduled_date') border-red-400 @enderror">
                <input type="time" id="scheduled_time" name="scheduled_time" value="{{ $scheduledTime }}"
                       class="mc-input text-xs @error('scheduled_time') border-red-400 @enderror">
            </div>
            <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                <button type="button" class="mc-btn mc-btn-ghost text-[11px]" data-shift="+1 hour" data-set-target="#scheduled_date,#scheduled_time">১ ঘণ্টা পরে</button>
                <button type="button" class="mc-btn mc-btn-ghost text-[11px]" data-shift="+1 day" data-set-target="#scheduled_date,#scheduled_time">আগামীকাল এই সময়</button>
                <button type="button" class="mc-btn mc-btn-ghost text-[11px]" data-shift="+1 week" data-set-target="#scheduled_date,#scheduled_time">এক সপ্তাহ পরে</button>
            </div>
            <p class="mc-tip">স্ট্যাটাস “নির্ধারিত” থাকলে এই সময়ে স্বয়ংক্রিয়ভাবে প্রকাশিত হবে (cron বা সাইট ভিজিটে)। <span id="scheduled-human" class="font-bold text-indigo-800 dark:text-indigo-300"></span></p>
            @error('scheduled_date')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
            @error('scheduled_time')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
            @error('scheduled_at')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
        </div>

        {{-- ফ্ল্যাগ --}}
        @if($canPublish)
            <div class="space-y-2">
                <x-admin.field-checkbox name="is_featured" label="হোমপেজে দেখান (ফিচার্ড)" :checked="$post->is_featured"
                    hint="হোমপেজে অগ্রাধিকার পাবে ও হাইলাইটে যেতে পারে" />
                <x-admin.field-checkbox name="is_breaking" label="ব্রেকিং নিউজ হিসেবে দেখান" :checked="$post->is_breaking"
                    hint="হেডারের ব্রেকিং বার-এ সবার আগে দেখা যাবে" />
            </div>
        @else
            <p class="rounded-lg bg-amber-50 px-3 py-2 text-[11px] font-semibold text-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                <i class="ph ph-lock-key"></i> হোমপেজে দেখানো ও ব্রেকিং নিউজ সেট করার অনুমতি আপনার নেই — এডিটর ঠিক করে দেবেন।
            </p>
        @endif

        <x-admin.field-checkbox name="allow_comments" label="মন্তব্যের অনুমতি" :checked="$post->allow_comments ?? true"
            hint="বন্ধ করলে এই সংবাদে কেউ মন্তব্য করতে পারবে না" />

        {{-- তথ্য --}}
        <div class="rounded-lg bg-slate-50 p-3 text-[11px] text-slate-600 dark:bg-slate-800/60 dark:text-slate-300">
            <p class="flex items-center gap-1 font-bold text-slate-700 dark:text-slate-200"><i class="ph ph-info"></i> সংবাদের তথ্য</p>
            <div class="mt-1.5 grid grid-cols-2 gap-1.5">
                <p>লেখক: <span class="font-bold">{{ $post->author?->name ?? $user->name }}</span></p>
                <p>ভিউ: <span class="font-bold">{{ bn_count((int) $post->views) }}</span></p>
                <p>মন্তব্য: <span class="font-bold">{{ bn_num((int) $post->comments_count) }}</span></p>
                <p>ছবি: <span class="font-bold">{{ bn_num($post->images->count()) }} টি</span></p>
                @if($isEdit)
                    <p class="col-span-2 truncate">পাবলিক লিংক:
                        <a href="{{ $publicUrl ?: route('news.show', $post->slug) }}" target="_blank" rel="noopener" class="font-bold text-[#E21D2B] hover:underline" dir="ltr">
                            {{ url('/news') }}/{{ $post->slug }}
                        </a>
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
