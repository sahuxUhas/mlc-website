{{-- ================= SEO: মেটা, সোশ্যাল, SERP প্রাকদর্শন ================= --}}
<div class="mc-card p-4">
    <h2 class="mb-4 flex items-center gap-2 font-serif text-sm font-bold text-slate-900 dark:text-white">
        <i class="ph ph-magnifying-glass text-[#E21D2B]"></i> SEO সেটিংস
    </h2>
    <p class="mc-tip mb-3">খালি রাখলে শিরোনাম ও সংক্ষিপ্ত বিবরণ থেকে স্বয়ংক্রিয়ভাবে ব্যবহার হবে।</p>

    <div class="space-y-3">
        <div>
            <label for="meta_title" class="mc-label">মেটা টাইটেল (SEO Title)</label>
            <input id="meta_title" name="meta_title" type="text" maxlength="191" value="{{ old('meta_title', $post->meta_title) }}"
                   data-serp="title" data-maxlen="#meta_title-count" placeholder="গুগলে যে শিরোনাম দেখা যাবে"
                   class="mc-input text-xs @error('meta_title') border-red-400 @enderror">
            <div class="flex items-center justify-between">
                <span class="mc-tip">৫০–৬০ অক্ষর আদর্শ</span>
                <span id="meta_title-count" class="text-[11px] text-slate-400"></span>
            </div>
            @error('meta_title')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="meta_description" class="mc-label">মেটা বিবরণ (SEO Description)</label>
            <textarea id="meta_description" name="meta_description" rows="3" maxlength="500" data-serp="description"
                      data-maxlen="#meta_description-count" placeholder="সার্চ ফলের নিচে যে ২ লাইন দেখা যায়"
                      class="mc-input text-xs @error('meta_description') border-red-400 @enderror">{{ old('meta_description', $post->meta_description) }}</textarea>
            <div class="flex items-center justify-between">
                <span class="mc-tip">১৫০–১৬০ অক্ষর আদর্শ</span>
                <span id="meta_description-count" class="text-[11px] text-slate-400"></span>
            </div>
            @error('meta_description')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="meta_keywords" class="mc-label">মেটা কীওয়ার্ড (SEO Keywords)</label>
            <input id="meta_keywords" name="meta_keywords" type="text" maxlength="400" value="{{ old('meta_keywords', $post->meta_keywords) }}"
                   placeholder="মহালছড়ি, খাগড়াছড়ি, শিক্ষা" class="mc-input text-xs @error('meta_keywords') border-red-400 @enderror">
            <p class="mc-tip">কমা দিয়ে আলাদা করুন — ৫–১০টি প্রাসঙ্গিক শব্দ রাখুন।</p>
            @error('meta_keywords')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
        </div>

        {{-- SERP প্রাকদর্শন --}}
        <div class="rounded-lg border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-900/60">
            <p class="mb-1.5 text-[10px] font-bold uppercase tracking-wide text-slate-400">গুগলে যা দেখা যাবে</p>
            <p class="truncate text-[13px] font-bold text-[#1a0dab]" dir="ltr">{{ url('/news') }}/{{ $post->slug ?: 'slug' }}</p>
            <p id="serp-title" class="truncate text-[15px] font-medium text-[#1a0dab]">{{ old('meta_title', $post->meta_title) ?: ($post->title ?: 'সংবাদের শিরোনাম') }}</p>
            <p id="serp-description" class="mt-0.5 text-[12px] leading-snug text-slate-600 dark:text-slate-300">
                {{ old('meta_description', $post->meta_description) ?: mc_excerpt($post->excerpt ?: $post->content, 160) ?: 'সংক্ষিপ্ত বিবরণ এখানে দেখা যাবে।' }}
            </p>
        </div>

        <details class="rounded-lg border border-slate-200 p-2.5 dark:border-slate-800">
            <summary class="cursor-pointer text-xs font-bold text-slate-700 dark:text-slate-200">সোশ্যাল শেয়ার (Open Graph)</summary>
            <div class="mt-3 space-y-3">
                <x-admin.field-input name="og_title" label="OG টাইটেল" :value="$post->og_title" placeholder="শেয়ারে যে শিরোনাম দেখাবে" />
                <x-admin.field-textarea name="og_description" label="OG বিবরণ" :value="$post->og_description" :rows="2" :maxlength="500" />
                <x-admin.field-image name="og_image" label="OG ছবি (১২০০×৬৩০ প্রস্তাবিত)"
                    :current="$post->og_image" :thumb="$post->og_image ? mc_image($post->og_image) : null"
                    :removeName="$isEdit && ($post->og_image || $post->og_media_id) ? 'remove_og_image' : null" />
                <x-admin.field-input name="canonical_url" label="Canonical URL" type="url" :value="$post->canonical_url" placeholder="https://…" />
            </div>
        </details>
    </div>
</div>
