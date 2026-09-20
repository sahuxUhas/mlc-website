{{-- ================= মূল তথ্য: শিরোনাম, ক্যাটাগরি, কনটেন্ট, ট্যাগ ================= --}}
<div class="mc-card p-4 sm:p-5">
    <h2 class="mb-4 flex items-center gap-2 font-serif text-sm font-bold text-slate-900 dark:text-white">
        <i class="ph ph-article text-[#E21D2B]"></i> মূল তথ্য
    </h2>

    <div class="space-y-4">
        {{-- শিরোনাম --}}
        <div>
            <label for="title" class="mc-label">সংবাদের শিরোনাম <span class="text-[#E21D2B]">*</span></label>
            <input id="title" name="title" type="text" required minlength="3" maxlength="191"
                   value="{{ old('title', $post->title) }}" data-slug-from="#slug" data-maxlen="#title-count"
                   placeholder="যেমন: মহালছড়িতে নতুন স্বাস্থ্য কমপ্লেক্স উদ্বোধন"
                   class="mc-input font-serif text-base font-bold @error('title') border-red-400 @enderror">
            <div class="mt-1 flex items-center justify-between gap-2">
                <p class="text-[11px] text-slate-500">সংক্ষিপ্ত, স্পষ্ট ও আকর্ষণীয় শিরোনাম লিখুন (সর্বোচ্চ ১৯১ অক্ষর)।</p>
                <span id="title-count" class="shrink-0 text-[11px] text-slate-400"></span>
            </div>
            @error('title')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
        </div>

        {{-- স্লাগ --}}
        <div>
            <label for="slug" class="mc-label">স্লাগ (SEO URL)</label>
            <div class="flex items-center gap-2">
                <span class="shrink-0 rounded-lg bg-slate-100 px-2.5 py-2 text-xs text-slate-500 dark:bg-slate-800 dark:text-slate-400" dir="ltr">{{ url('/news') }}/</span>
                <input id="slug" name="slug" type="text" maxlength="191" value="{{ old('slug', $post->slug) }}"
                       placeholder="auto-generated-from-title" dir="ltr" pattern="[\p{L}\p{N}\-_]+"
                       class="mc-input text-xs @error('slug') border-red-400 @enderror">
            </div>
            <p class="mt-1 text-[11px] text-slate-500">খালি রাখলে শিরোনাম থেকে স্বয়ংক্রিয়ভাবে তৈরি হবে। বাংলা অক্ষর URL-এ সংরক্ষিত থাকে — একই স্লাগ দুইবার হতে পারবে না।</p>
            @error('slug')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
        </div>

        {{-- ক্যাটাগরি / সাব-ক্যাটাগরি / রিপোর্টার --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label for="category_id" class="mc-label">ক্যাটাগরি <span class="text-[#E21D2B]">*</span></label>
                <select id="category_id" name="category_id" required class="mc-input @error('category_id') border-red-400 @enderror">
                    <option value="">— নির্বাচন করুন —</option>
                    @foreach($rootCats as $c)
                        <option value="{{ $c->id }}" @selected($selectedCat === (string) $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
                @error('category_id')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="subcategory_id" class="mc-label">সাব-ক্যাটাগরি</label>
                <select id="subcategory_id" name="subcategory_id" class="mc-input @error('subcategory_id') border-red-400 @enderror"
                        data-selected="{{ old('subcategory_id', $post->subcategory_id) }}">
                    <option value="">— কোনোটি নয় —</option>
                    @foreach($subCats as $sc)
                        <option value="{{ $sc->id }}" data-parent="{{ $sc->parent_id }}"
                                @selected((string) old('subcategory_id', $post->subcategory_id) === (string) $sc->id)>{{ $sc->name }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-[11px] text-slate-500">নির্বাচিত ক্যাটাগরি অনুযায়ী তালিকা বদলে যায়।</p>
                @error('subcategory_id')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="reporter_id" class="mc-label">রিপোর্টার / লেখক</label>
                <select id="reporter_id" name="reporter_id" class="mc-input @error('reporter_id') border-red-400 @enderror">
                    <option value="">— নিজস্ব প্রতিবেদন —</option>
                    @foreach($reporters as $r)
                        <option value="{{ $r->id }}" @selected((string) old('reporter_id', $post->reporter_id) === (string) $r->id)>
                            {{ $r->name }}{{ $r->designation ? ' — '.$r->designation : '' }}
                        </option>
                    @endforeach
                </select>
                @error('reporter_id')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- সংক্ষিপ্ত বিবরণ --}}
        <x-admin.field-textarea name="excerpt" label="সংক্ষিপ্ত বিবরণ (Short Description)"
            :value="$post->excerpt" :rows="3" :counter="true" :maxlength="600"
            hint="খালি রাখলে মূল সংবাদ থেকে স্বয়ংক্রিয়ভাবে তৈরি হবে। কার্ড, সার্চ ফলাফল ও সোশ্যাল শেয়ারে এটি দেখা যায়।" />

        {{-- বিস্তারিত কনটেন্ট এডিটর --}}
        <div>
            <div class="mb-1 flex flex-wrap items-center justify-between gap-2">
                <label for="content" class="mc-label mb-0">বিস্তারিত সংবাদ (Full Content) <span class="text-[#E21D2B]">*</span></label>
                <span class="text-[11px] text-slate-400"><span id="content-count">০</span> অক্ষর · <span id="content-words">০</span> শব্দ</span>
            </div>

            <div data-editor class="mc-editor @error('content') border-red-400 @enderror">
                {{-- টুলবার: ডিজাইনে ব্যবহৃত একই আইকন/রঙ --}}
                <div class="mc-editor-toolbar">
                    <button type="button" data-ed-block="h2" title="উপ-শিরোনাম (H2)" class="mc-btn-editor"><i class="ph-bold ph-text-h-two"></i></button>
                    <button type="button" data-ed-block="h3" title="উপ-শিরোনাম (H3)" class="mc-btn-editor"><i class="ph-bold ph-text-h-three"></i></button>
                    <button type="button" data-ed-wrap="strong" title="গাঢ়" class="mc-btn-editor"><i class="ph-bold ph-text-b"></i></button>
                    <button type="button" data-ed-wrap="em" title="ইটালিক" class="mc-btn-editor"><i class="ph-bold ph-text-italic"></i></button>
                    <button type="button" data-ed-wrap="u" title="আন্ডারলাইন" class="mc-btn-editor"><i class="ph-bold ph-text-underline"></i></button>
                    <span class="mc-ed-sep"></span>
                    <button type="button" data-ed-block="ul" title="বুলেট তালিকা" class="mc-btn-editor"><i class="ph-bold ph-list-bullets"></i></button>
                    <button type="button" data-ed-block="ol" title="নম্বর তালিকা" class="mc-btn-editor"><i class="ph-bold ph-list-numbers"></i></button>
                    <button type="button" data-ed-block="quote" title="উদ্ধৃতি" class="mc-btn-editor"><i class="ph-bold ph-quotes"></i></button>
                    <button type="button" data-ed-block="hr" title="বিভাজক রেখা" class="mc-btn-editor"><i class="ph-bold ph-minus"></i></button>
                    <span class="mc-ed-sep"></span>
                    <button type="button" data-ed-link title="লিংক" class="mc-btn-editor"><i class="ph-bold ph-link"></i></button>
                    <button type="button" data-ed-image title="ছবি যোগ করুন" class="mc-btn-editor text-[#E21D2B]"><i class="ph-bold ph-image-plus"></i></button>
                    <span class="mc-ed-sep"></span>
                    <button type="button" data-ed-preview title="লাইভ প্রাকদর্শন" class="mc-btn-editor"><i class="ph-bold ph-eye"></i></button>
                    <button type="button" data-ed-full title="বড় করে লিখুন" class="mc-btn-editor"><i class="ph-bold ph-arrows-out"></i></button>

                    <span class="ml-auto flex items-center gap-1 pr-1 text-[10px] text-slate-400">
                        <span id="editor-upload-status" class="hidden font-bold text-[#E21D2B]"></span>
                        <i class="ph-fill ph-shield-check"></i> নিরাপদ
                    </span>
                </div>

                <textarea id="content" name="content" rows="18" required minlength="20"
                          placeholder="সংবাদের বিস্তারিত এখানে লিখুন…">{{ old('content', $post->content) }}</textarea>
            </div>

            {{-- এডিটরে যোগ করা ছবির তালিকা (raw URL ছাড়া) --}}
            <div id="editor-media-list" class="mt-2 flex flex-wrap gap-2 {{ empty($contentMedia) ? 'hidden' : '' }}">
                @foreach($contentMedia as $item)
                    <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white p-1 pr-2 dark:border-slate-700 dark:bg-slate-800" data-editor-media="{{ $item['id'] }}">
                        <img src="{{ $item['thumb'] }}" alt="{{ $item['name'] }}" class="h-9 w-12 rounded object-cover"
                             onerror="this.onerror=null;this.src='{{ mc_placeholder_svg() }}'">
                        <button type="button" data-ed-remove-media="{{ $item['id'] }}" class="text-[10px] font-bold text-red-600 hover:underline">সরান</button>
                    </div>
                @endforeach
            </div>
            <input type="file" id="editorImages" accept="image/jpeg,image/png,image/webp" multiple class="hidden">

            <div class="mt-1 flex flex-wrap items-center justify-between gap-2">
                <p class="text-[11px] text-slate-500">
                    টুলবার থেকে শিরোনাম, তালিকা, উদ্ধৃতি, লিংক ও ছবি যোগ করা যায়। ছবি হোস্টিং API তে যায় —
                    কনটেন্টে শুধু নিরাপদ রেফারেন্স থাকে, কোনো লিংক লেখা হিসেবে দেখা যায় না।
                </p>
            </div>
            @error('content')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
        </div>

        {{-- লাইভ প্রাকদর্শন প্যানেল (JS চালু করলে) --}}
        <div id="editor-preview-panel" class="hidden">
            <p class="mb-1 flex items-center gap-1 text-[11px] font-bold text-slate-500"><i class="ph ph-eye"></i> লাইভ প্রাকদর্শন (সংরক্ষণের আগের ঝলক)</p>
            <iframe id="editor-preview-frame" class="mc-preview-frame" style="height:18rem" sandbox="" title="কনটেন্ট প্রাকদর্শন"></iframe>
        </div>

        {{-- স্থান ও ভিডিও --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-admin.field-input name="location" label="স্থান / এলাকা" :value="$post->location" placeholder="মহালছড়ি, খাগড়াছড়ি" />
            <x-admin.field-input name="video_url" label="ভিডিও Embed URL" type="url" :value="$post->video_url"
                placeholder="https://…" hint="YouTube/Facebook embed লিংক — আর্টিকেলের উপরে দেখানো হবে" />
        </div>

        {{-- ট্যাগ: Add / Edit / Delete --}}
        <div data-tag-field>
            <label for="tagInput" class="mc-label">ট্যাগ (Tag)</label>
            <div class="mc-tag-box" data-tag-box>
                {{-- JS চালু থাকলে chip গুলো এখানে যোগ হয় --}}
                <input id="tagInput" type="text" list="tagSuggest" autocomplete="off"
                       placeholder="ট্যাগ লিখে Enter চাপুন…">
            </div>
            <input type="hidden" name="tags" id="tagsField" value="{{ implode(', ', $tagNames) }}">
            <datalist id="tagSuggest">
                @foreach($allTags as $t)<option value="{{ $t->name }}"></option>@endforeach
            </datalist>

            {{-- JS ছাড়াও ট্যাগ যোগ/মুছার সহজ ইনপুট --}}
            <p class="mt-1 text-[11px] text-slate-500">
                Enter চাপলে ট্যাগ যুক্ত হবে, ✕ চিহ্নে মুছে যাবে। ট্যাগ পেজে প্রতিটি ট্যাগের নিজস্ব SEO লিংক তৈরি হয়।
                @if(auth()->user()->can_manage('tags.manage'))
                    <a href="{{ route('admin.tags.index') }}" class="font-bold text-[#E21D2B] hover:underline" target="_blank">ট্যাগ ম্যানেজার</a>
                @endif
            </p>
            <ul id="tagFallback" class="mc-tip" hidden></ul>
            @error('tags')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>
