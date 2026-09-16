<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
    <td class="px-4 py-2.5">
        <div class="flex items-center gap-2.5" style="{{ $level ? 'padding-left:1.5rem' : '' }}">
            @if($level)<i class="ph ph-arrow-elbow-down-right text-xs text-slate-300"></i>@endif
            @if($cat->image)<img src="{{ mc_image($cat->image) }}" alt="" class="h-8 w-8 shrink-0 rounded object-cover" loading="lazy">@endif
            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-sm text-white" style="background:{{ $cat->color ?: '#E21D2B' }}"><i class="ph {{ $cat->icon ?: 'ph-newspaper' }}"></i></span>
            <div class="min-w-0">
                <p class="truncate font-serif text-sm font-bold text-slate-900 dark:text-white">{{ $cat->name }}</p>
                @if($cat->union_name)<p class="truncate text-[11px] text-slate-500">{{ $cat->union_name }}</p>@endif
            </div>
        </div>
    </td>
    <td class="px-4 py-2.5"><code class="text-[11px] text-slate-500" dir="ltr">{{ $cat->slug }}</code></td>
    <td class="px-4 py-2.5 text-center"><span class="mc-badge bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ bn_count($cat->posts_count) }}</span></td>
    <td class="px-4 py-2.5 text-center">{{ bn_num($cat->sort_order) }}</td>
    <td class="px-4 py-2.5 text-center">
        @if(!$cat->is_visible)<span class="mc-badge bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300">লুকানো</span>
        @elseif($cat->show_on_home)<span class="mc-badge bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">হোম+মেনু</span>
        @else<span class="mc-badge bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300">সক্রিয়</span>@endif
    </td>
    <td class="px-4 py-2.5 text-right">
        <x-admin.action-buttons :edit="'editCat-'.$cat->id" :view="route('category.show',$cat->slug)"
            :toggle="route('admin.categories.toggle',$cat)" :toggleOn="(bool)$cat->is_visible"
            :destroy="route('admin.categories.destroy',$cat)" />
    </td>
</tr>
