{{-- ================= ছবি: Featured + একাধিক গ্যালারি ছবি (Upload / Preview / Remove / Reorder) ================= --}}

{{-- ---------- ফিচার্ড ইমেজ ---------- --}}
<div class="mc-card p-4 sm:p-5">
    <h2 class="mb-1 flex items-center gap-2 font-serif text-sm font-bold text-slate-900 dark:text-white">
        <i class="ph ph-image-square text-[#E21D2B]"></i> ফিচার্ড ইমেজ (প্রধান ছবি)
    </h2>
    <p class="mc-tip mb-4">
        <strong>নিচের বাটন থেকে সরাসরি ফাইল আপলোড করুন</strong> — ছবি Laravel Backend হয়ে হোস্টিং API
        ({{ $imageProvider['provider_label'] ?? 'সার্ভার স্টোরেজ' }}) তে যায়, ডাটাবেসে শুধু রেফারেন্স থাকে।
        লিংক/URL বসানোর কোনো সুবিধা নেই; আপলোডের পর এখানে শুধু প্রিভিউ/থাম্বনেইল দেখা যায়।
    </p>

    <x-admin.field-image name="featured_image" label="প্রধান ছবি"
        :current="$post->featured_image" :thumb="$post->featured_thumb"
        :removeName="$isEdit && ($post->featured_image || $post->featured_media_id) ? 'remove_featured_image' : null"
        :providerLabel="$post->featured_image || $post->featured_media_id ? ($imageProvider['provider_label'] ?? null) : null"
        previewClass="h-24 w-36" />

    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-admin.field-input name="image_caption" label="ছবির ক্যাপশন" :value="$post->image_caption"
            placeholder="যেমন: মহালছড়ি বাজারে সেনাবাহিনীর ত্রাণ বিতরণ" />
        <x-admin.field-input name="image_credit" label="ছবির ক্রেডিট" :value="$post->image_credit"
            placeholder="নিজস্ব প্রতিবেদক / সংগৃহীত" />
    </div>
</div>

{{-- ---------- গ্যালারি (একাধিক ছবি) ---------- --}}
<div class="mc-card p-4 sm:p-5">
    <h2 class="mb-1 flex items-center gap-2 font-serif text-sm font-bold text-slate-900 dark:text-white">
        <i class="ph ph-images text-[#E21D2B]"></i> সংবাদ গ্যালারি (একাধিক ছবি)
    </h2>
    <p class="mc-tip mb-4">
        একসাথে {{ bn_num($maxUploads) }}টি পর্যন্ত ছবি <strong>সরাসরি আপলোড</strong> করা যায় (প্রতিটি সর্বোচ্চ {{ bn_num(intdiv($maxUploadKb, 1024)) }} MB)।
        ক্রম বদলাতে ছবি টেনে আনুন বা তীর চিহ্ন ব্যবহার করুন — <strong>★</strong> চিহ্নে ছবিটি ফিচার্ড করুন, ✕ চিহ্নে মুছে ফেলুন।
        @if($isEdit) ক্রম স্বয়ংক্রিয়ভাবে সংরক্ষিত হয়। @else সংবাদ সংরক্ষণের সময় সব ছবি একসাথে যুক্ত হবে। @endif
    </p>

    {{-- বিদ্যমান ছবি (Edit mode) --}}
    @if($isEdit)
        <div class="mc-gallery-grid" id="galleryList" data-gallery-list>
            @forelse($post->images as $index => $image)
                @php
                    $isFeaturedImage = $post->featured_media_id && (int) $post->featured_media_id === (int) $image->media_id;
                @endphp
                <div class="mc-gallery-item {{ $isFeaturedImage ? 'is-featured' : '' }}"
                     data-gallery-item data-id="{{ $image->id }}" data-media-id="{{ $image->media_id }}" draggable="true">
                    <span class="mc-badge-order" data-order-badge>{{ bn_num($index + 1) }}</span>
                    @if($isFeaturedImage)<span class="mc-badge-featured">ফিচার্ড</span>@endif

                    <img src="{{ $image->url() }}" alt="{{ $image->label() }}" loading="lazy"
                         onerror="this.onerror=null;this.src='{{ mc_placeholder_svg() }}'">

                    <div class="mc-gallery-bar">
                        <div class="flex gap-1">
                            <button type="button" data-move="up" title="উপরে"><i class="ph-bold ph-arrow-up"></i></button>
                            <button type="button" data-move="down" title="নিচে"><i class="ph-bold ph-arrow-down"></i></button>
                        </div>
                        <div class="flex gap-1">
                            <button type="button" data-toggle-caption title="ক্যাপশন/ক্রেডিট"><i class="ph-bold ph-text-aa"></i></button>
                            {{-- JS ছাড়াও কাজ করে: বাটনগুলো মূল ফর্মের বাইরের সহায়ক ফর্মের সাথে যুক্ত (form="…") --}}
                            <button type="submit" form="feat-{{ $image->id }}" data-set-featured title="ফিচার্ড করুন"><i class="ph-bold ph-star"></i></button>
                            <button type="submit" form="del-{{ $image->id }}" class="is-danger" data-delete-image
                                    title="মুছে ফেলুন"><i class="ph-bold ph-trash"></i></button>
                        </div>
                    </div>

                    @if($image->caption || $image->credit)
                        <p class="truncate px-2 py-1 text-[10px] text-slate-500 dark:text-slate-400">
                            {{ $image->caption ?: $image->credit }}
                        </p>
                    @endif

                    {{-- ক্যাপশন/ক্রেডিট/Alt এডিট (JS ছাড়াও কাজ করে — ইনপুটগুলো cap-* ফর্মের সাথে যুক্ত) --}}
                    <div class="hidden border-t border-slate-200 bg-slate-50 p-2 dark:border-slate-800 dark:bg-slate-800/60"
                         data-caption-panel data-caption-form="{{ $image->id }}">
                        <div class="space-y-1.5">
                            <input type="text" form="cap-{{ $image->id }}" name="caption" maxlength="190"
                                   value="{{ $image->caption }}" placeholder="ক্যাপশন" class="mc-input text-[11px]">
                            <input type="text" form="cap-{{ $image->id }}" name="credit" maxlength="120"
                                   value="{{ $image->credit }}" placeholder="ক্রেডিট" class="mc-input text-[11px]">
                            <input type="text" form="cap-{{ $image->id }}" name="alt_text" maxlength="190"
                                   value="{{ $image->alt_text }}" placeholder="Alt টেক্সট (SEO)" class="mc-input text-[11px]">
                            <button type="submit" form="cap-{{ $image->id }}" class="mc-btn mc-btn-ghost w-full text-[11px]">সংরক্ষণ</button>
                        </div>
                    </div>
                </div>
            @empty
                <p class="col-span-full rounded-lg border border-dashed border-slate-300 py-6 text-center text-xs text-slate-500 dark:border-slate-700">
                    এই সংবাদে এখনো কোনো গ্যালারি ছবি যোগ করা হয়নি।
                </p>
            @endforelse
        </div>

        {{-- ক্রম সংরক্ষণের hidden ইনপুট (AJAX ব্যর্থ হলে ফর্ম সাবমিটেই ক্রম যায়) --}}
        <input type="hidden" name="order" data-order-input value="{{ $post->images->pluck('id')->implode(',') }}">
        <div class="mt-3 flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.news.edit', $post) }}" class="mc-btn mc-btn-ghost flex items-center gap-1.5">
                <i class="ph ph-arrows-clockwise"></i> তালিকা রিফ্রেশ
            </a>
            <span class="mc-tip">ক্রম বদলালে স্বয়ংক্রিয়ভাবে সংরক্ষিত হয়; সমস্যা হলে পেজ রিফ্রেশ করুন।</span>
        </div>

        <hr class="my-4 border-slate-200 dark:border-slate-800">
    @endif

    {{-- নতুন ছবি যোগ --}}
    <div>
        <label for="images" class="mc-label">নতুন ছবি যোগ করুন @if($isEdit)(নির্বাচন করলেই আপলোড শুরু হবে)@endif</label>
        <div class="mc-dropzone" data-dropzone="#images" data-news-dropzone>
            <i class="ph ph-cloud-arrow-up"></i>
            <p class="text-xs font-bold text-slate-600 dark:text-slate-300">ছবি টেনে আনুন অথবা ক্লিক করে নির্বাচন করুন</p>
            <p class="text-[11px] text-slate-500">jpg, jpeg, png, webp — একসাথে সর্বোচ্চ {{ bn_num($maxUploads) }}টি</p>
        </div>
        <input id="images" name="images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple class="hidden" data-no-auto-submit>

        <div id="images-preview" class="mc-gallery-grid mt-3"></div>

        @error('images')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
        @error('images.*')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror
        @if(session('upload_errors'))
            <ul class="mc-upload-errors">
                @foreach(session('upload_errors') as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        @endif
    </div>

    {{-- JS বন্ধ থাকলে ক্যাপশন/ক্রেডিট ফর্মগুলো খোলা অবস্থায় দেখা যাবে --}}
    <noscript>
        <style>[data-caption-panel]{display:block !important}</style>
    </noscript>
</div>
