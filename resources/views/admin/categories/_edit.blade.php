<x-admin.modal :id="'editCat-'.$cat->id" :title="'এডিট: '.$cat->name">
    <form action="{{ route('admin.categories.update',$cat) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
        @csrf @method('PUT')
        <x-admin.field-select name="parent_id" label="প্যারেন্ট ক্যাটাগরি" placeholder="— মূল ক্যাটাগরি —"
            :selected="$cat->parent_id" :options="\App\Models\Category::root()->where('id','!=',$cat->id)->pluck('name','id')->all()" />
        <x-admin.field-input name="name" label="ক্যাটাগরি নাম" :value="$cat->name" required />
        <x-admin.field-input name="slug" label="স্লাগ" :value="$cat->slug" />
        <div class="grid grid-cols-2 gap-3">
            <x-admin.field-input name="icon" label="আইকন" :value="$cat->icon" />
            <div>
                <label class="mc-label">রঙ</label>
                <input type="color" name="color" value="{{ $cat->color ?: '#E21D2B' }}" class="h-10 w-full cursor-pointer rounded-lg border border-slate-300 bg-white p-1 dark:border-slate-700 dark:bg-slate-900">
            </div>
        </div>
        <x-admin.field-input name="union_name" label="ইউনিয়ন / এলাকা" :value="$cat->union_name" />
        <x-admin.field-textarea name="description" label="বিবরণ" :value="$cat->description" :rows="2" />
        <x-admin.field-image name="image" label="ছবি" :current="$cat->image" />
        <x-admin.field-input name="sort_order" label="ক্রম" type="number" :value="$cat->sort_order" step="1" />
        <x-admin.field-input name="meta_title" label="মেটা টাইটেল" :value="$cat->meta_title" />
        <x-admin.field-textarea name="meta_description" label="মেটা বিবরণ" :value="$cat->meta_description" :rows="2" />
        <div class="grid grid-cols-1 gap-2">
            <x-admin.field-checkbox name="is_visible" label="দৃশ্যমান" :checked="(bool)$cat->is_visible" />
            <x-admin.field-checkbox name="show_on_home" label="হোমপেজে দেখান" :checked="(bool)$cat->show_on_home" />
            <x-admin.field-checkbox name="show_in_menu" label="মেনুতে দেখান" :checked="(bool)$cat->show_in_menu" />
        </div>
        <button type="submit" class="mc-btn mc-btn-primary w-full"><i class="ph-fill ph-floppy-disk"></i> আপডেট করুন</button>
    </form>
</x-admin.modal>
