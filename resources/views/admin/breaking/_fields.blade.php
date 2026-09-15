@php $edit = $edit ?? false; $dt = fn($d) => $d ? \Illuminate\Support\Carbon::parse($d)->format('Y-m-d\TH:i') : ''; @endphp
<form action="{{ $edit ? route('admin.breaking.update',$b) : route('admin.breaking.store') }}" method="POST" class="space-y-3">@csrf @if($edit) @method('PUT') @endif
    <x-admin.field-select name="post_id" label="প্রকাশিত সংবাদ বেছে নিন" placeholder="— সরাসরি শিরোনাম লিখবেন —" :options="$posts->pluck('title','id')->all()" :selected="$b->post_id"
        hint="সংবাদ বেছে নিলে শিরোনাম ও লিংক স্বয়ংক্রিয়ভাবে বসবে" />
    <x-admin.field-input name="title" label="ব্রেকিং শিরোনাম" :value="$b->title" required />
    <x-admin.field-input name="url" label="কাস্টম লিংক (ঐচ্ছিক)" type="url" :value="$b->url" placeholder="https://…" />
    <div class="grid grid-cols-2 gap-3"><x-admin.field-input name="priority" label="অগ্রাধিকার" type="number" :value="$b->priority ?? 0" step="1" />
        <x-admin.field-input name="sort_order" label="ক্রম" type="number" :value="$b->sort_order ?? 0" step="1" /></div>
    <div class="grid grid-cols-2 gap-3"><x-admin.field-input name="starts_at" label="শুরু" type="datetime-local" :value="$dt($b->starts_at)" />
        <x-admin.field-input name="ends_at" label="শেষ" type="datetime-local" :value="$dt($b->ends_at)" hint="খালি = সবসময় দেখাবে" /></div>
    <x-admin.field-checkbox name="is_enabled" label="সক্রিয়" :checked="(bool)($b->is_enabled ?? true)" hint="বন্ধ করলে ব্রেকিং বারে দেখাবে না" />
    <button type="submit" class="mc-btn mc-btn-primary w-full"><i class="ph-fill ph-floppy-disk"></i> {{ $edit ? 'আপডেট' : 'সংরক্ষণ' }}</button>
</form>
