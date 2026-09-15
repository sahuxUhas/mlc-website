<x-admin.modal :id="'editMenu-'.$m->id" :title="'মেনু এডিট: '.$m->label">
    <form action="{{ route('admin.menus.update',$m) }}" method="POST" class="space-y-3">@csrf @method('PUT')
        <x-admin.field-select name="location" label="লোকেশন" :options="\App\Models\Menu::LOCATIONS" :selected="$m->location" required />
        <x-admin.field-select name="parent_id" label="প্যারেন্ট আইটেম" placeholder="— মূল মেনু —"
            :options="\App\Models\Menu::where('location',$m->location)->whereNull('parent_id')->where('id','!=',$m->id)->pluck('label','id')->all()" :selected="$m->parent_id" />
        <x-admin.field-input name="label" label="লেবেল" :value="$m->label" required />
        <x-admin.field-select name="link_type" label="লিংকের ধরন" :options="['url'=>'কাস্টম URL','category'=>'ক্যাটাগরি','page'=>'স্ট্যাটিক পেজ']" :selected="$m->link_type" required />
        <x-admin.field-input name="url" label="URL" :value="$m->url" />
        <x-admin.field-input name="reference_id" label="রেফারেন্স আইডি" :value="$m->reference_id" hint="c… = ক্যাটাগরি, p… = পেজ" />
        <x-admin.field-input name="icon" label="আইকন" :value="$m->icon" />
        <x-admin.field-input name="sort_order" label="ক্রম" type="number" :value="$m->sort_order" step="1" />
        <div class="grid grid-cols-1 gap-2"><x-admin.field-checkbox name="is_enabled" label="সক্রিয়" :checked="(bool)$m->is_enabled" />
            <x-admin.field-checkbox name="open_in_new_tab" label="নতুন ট্যাবে খুলুন" :checked="(bool)$m->open_in_new_tab" /></div>
        <button type="submit" class="mc-btn mc-btn-primary w-full"><i class="ph-fill ph-floppy-disk"></i> আপডেট</button>
    </form>
</x-admin.modal>
