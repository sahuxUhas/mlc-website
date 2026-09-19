{{-- ================= প্রাকদর্শন মোডাল (সেভ না করেই সংবাদ কেমন দেখাবে) ================= --}}
<x-admin.modal id="newsPreviewModal" title="সংবাদ প্রাকদর্শন" size="xl">
    <div class="mc-preview-sheet mb-3">
        <span class="flex items-center gap-1 font-bold"><i class="ph ph-eye"></i> ফর্মের বর্তমান তথ্য দিয়ে প্রাকদর্শন</span>
        <span class="flex items-center gap-1">
            <button type="button" class="mc-btn mc-btn-ghost text-[11px]" data-preview-device="desktop"><i class="ph ph-monitor"></i> ডেস্কটপ</button>
            <button type="button" class="mc-btn mc-btn-ghost text-[11px]" data-preview-device="mobile"><i class="ph ph-device-mobile"></i> মোবাইল</button>
            <button type="button" class="mc-btn mc-btn-ghost text-[11px]" data-preview-refresh><i class="ph ph-arrows-clockwise"></i> রিফ্রেশ</button>
        </span>
    </div>

    <iframe id="newsPreviewFrame" class="mc-preview-frame" sandbox="" title="সংবাদ প্রাকদর্শন"></iframe>

    <p class="mc-tip mt-2">
        এটি সংরক্ষণের আগের ঝলক — শিরোনাম, সংক্ষিপ্ত বিবরণ, ছবি ও লেখা এখানেই দেখা যায়।
        @if($isEdit)
            সংরক্ষিত অবস্থার পূর্ণ প্রাকদর্শনের জন্য
            <a href="{{ route('admin.news.preview', $post) }}" target="_blank" rel="noopener" class="font-bold text-[#E21D2B] hover:underline">নতুন ট্যাবে খুলুন</a>।
        @endif
    </p>
</x-admin.modal>
