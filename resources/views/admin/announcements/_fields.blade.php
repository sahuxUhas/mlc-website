@php $edit = $edit ?? false; $dt = fn($d) => $d ? \Illuminate\Support\Carbon::parse($d)->format('Y-m-d\TH:i') : ''; @endphp
<form action="{{ $edit ? route('admin.announcements.update',$a) : route('admin.announcements.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
    @csrf @if($edit) @method('PUT') @endif
    <x-admin.field-input name="title" label="শিরোনাম" :value="$a->title" required />
    <x-admin.field-input name="slug" label="স্লাগ" :value="$a->slug" hint="খালি রাখলে শিরোনাম থেকে তৈরি হবে" />
    <x-admin.field-textarea name="body" label="ঘোষণার বিস্তারিত" :value="$a->body" :rows="5" required />
    <div class="grid grid-cols-2 gap-3"><x-admin.field-select name="type" label="ধরন" :options="\App\Models\Announcement::TYPES" :selected="$a->type" required />
        <x-admin.field-select name="status" label="স্ট্যাটাস" :options="\App\Models\Announcement::STATUSES" :selected="$a->status" required /></div>
    <div class="grid grid-cols-2 gap-3"><x-admin.field-input name="starts_at" label="শুরু" type="datetime-local" :value="$dt($a->starts_at)" />
        <x-admin.field-input name="expires_at" label="মেয়াদ শেষ" type="datetime-local" :value="$dt($a->expires_at)" hint="খালি = অসীম" /></div>
    <div class="grid grid-cols-2 gap-3"><x-admin.field-input name="link" label="বিস্তারিত লিংক" type="url" :value="$a->link" />
        <x-admin.field-input name="link_text" label="লিংকের লেখা" :value="$a->link_text" placeholder="বিস্তারিত পড়ুন" /></div>
    <x-admin.field-image name="image" label="ছবি" :current="$a->image" />
    <div class="grid grid-cols-2 gap-3"><x-admin.field-input name="priority" label="অগ্রাধিকার" type="number" :value="$a->priority ?? 0" step="1" hint="বড় সংখ্যা = আগে দেখাবে" />
        <div class="self-end"><x-admin.field-checkbox name="is_visible" label="দৃশ্যমান" :checked="(bool)($a->is_visible ?? true)" /></div></div>
    <button type="submit" class="mc-btn mc-btn-primary w-full"><i class="ph-fill ph-floppy-disk"></i> {{ $edit ? 'আপডেট' : 'সংরক্ষণ' }}</button>
</form>
