{{-- ================= সংরক্ষণ / প্রকাশ / ডিলিট অ্যাকশন ================= --}}
<div class="mc-card space-y-2 p-4">
    <h2 class="mb-2 flex items-center gap-2 font-serif text-sm font-bold text-slate-900 dark:text-white">
        <i class="ph ph-floppy-disk text-[#E21D2B]"></i> সংরক্ষণ
    </h2>

    <button type="submit" name="action" value="save_draft" class="mc-btn mc-btn-ghost flex w-full items-center justify-center gap-2" data-submit-action="save_draft">
        <i class="ph ph-file-dashed"></i> খসড়া হিসেবে সংরক্ষণ করুন
    </button>

    @if($isEdit)
        <button type="submit" name="action" value="update" class="mc-btn mc-btn-ghost flex w-full items-center justify-center gap-2" data-submit-action="update">
            <i class="ph ph-floppy-disk"></i> হালনাগাদ করুন (স্ট্যাটাস অপরিবর্তিত)
        </button>
    @endif

    @if($canPublish)
        <button type="submit" name="action" value="publish"
                class="mc-btn flex w-full items-center justify-center gap-2 bg-emerald-600 text-white hover:bg-emerald-700" data-submit-action="publish">
            <i class="ph-fill ph-paper-plane-tilt"></i> {{ $isEdit && $post->status === 'published' ? 'হালনাগাদ ও প্রকাশিত রাখুন' : 'সংরক্ষণ করে প্রকাশ করুন' }}
        </button>
        <button type="submit" name="action" value="schedule" class="mc-btn mc-btn-ghost flex w-full items-center justify-center gap-2" data-submit-action="schedule">
            <i class="ph ph-calendar-check"></i> নির্ধারিত সময়ে প্রকাশ (শিডিউল)
        </button>
    @else
        <button type="submit" name="action" value="pending" class="mc-btn mc-btn-primary flex w-full items-center justify-center gap-2" data-submit-action="pending">
            <i class="ph ph-paper-plane-tilt"></i> রিভিউতে পাঠান
        </button>
        <p class="rounded-lg bg-amber-50 px-3 py-2 text-[11px] font-semibold text-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
            <i class="ph ph-info"></i> প্রকাশের অনুমতি আপনার নেই — খসড়া/রিভিউ হিসেবে জমা হলে এডিটর প্রকাশ করবেন।
        </p>
    @endif

    <div class="grid grid-cols-2 gap-2 pt-1">
        <a href="{{ route('admin.news.index') }}" class="mc-btn mc-btn-ghost text-center">বাতিল</a>
        @if($isEdit)
            <a href="{{ $publicUrl ?: route('news.show', $post->slug) }}" target="_blank" rel="noopener" class="mc-btn mc-btn-ghost text-center">
                <i class="ph ph-eye"></i> দেখুন
            </a>
        @else
            <button type="button" data-mc-modal-open="newsPreviewModal" class="mc-btn mc-btn-ghost text-center">
                <i class="ph ph-device-mobile"></i> প্রাকদর্শন
            </button>
        @endif
    </div>

    @if($isEdit && $canDelete && $post->status !== 'published')
        {{-- nested form নয় — সত্যিকারের ফর্মটি মূল ফর্মের বাইরে (#del-post-*), বাটনটি form="…" দিয়ে যুক্ত --}}
        <div class="pt-1">
            <button type="submit" form="del-post-{{ $post->id }}"
                    class="mc-btn w-full bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-950/40 dark:text-red-300">
                <i class="ph ph-trash-simple"></i> ট্র্যাশে পাঠান
            </button>
        </div>
    @endif

    {{-- কনটেন্ট কাঠামোর সাহায্য --}}
    <details class="rounded-lg border border-slate-200 p-2.5 text-[11px] text-slate-600 dark:border-slate-800 dark:text-slate-300">
        <summary class="cursor-pointer font-bold">লেখার নিয়ম (সংক্ষেপে)</summary>
        <ul class="mt-1.5 list-inside list-disc space-y-1">
            <li>শিরোনাম ৩–১৯১ অক্ষর, স্পষ্ট ও তথ্যপূর্ণ।</li>
            <li>প্রথম প্যারাোগ্রাফে ৫টি প্রশ্নের (কে, কী, কোথায়, কখন, কেন) উত্তর রাখুন।</li>
            <li>সংক্ষিপ্ত বিবরণ ১৬০ অক্ষরের মধ্যে রাখলে কার্ড/গুগলে সুন্দর দেখায়।</li>
            <li>ছবির ক্যাপশন ও ক্রেডিট দিলে বিশ্বাসযোগ্যতা ও এসইও ভালো হয়।</li>
        </ul>
    </details>
</div>
