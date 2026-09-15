@php $edit = $edit ?? false; @endphp
<form action="{{ $edit ? route('admin.videos.update',$v) : route('admin.videos.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
    @csrf @if($edit) @method('PUT') @endif
    <x-admin.field-input name="title" label="ভিডিওর শিরোনাম" :value="$v->title" required />
    <x-admin.field-input name="slug" label="স্লাগ" :value="$v->slug" hint="খালি রাখলে শিরোনাম থেকে তৈরি হবে" />
    <x-admin.field-input name="video_url" label="ভিডিও URL (ফেসবুক / ইউটিউব)" type="url" :value="$v->video_url" required
        placeholder="https://www.facebook.com/…/videos/…" hint="Facebook ভিডিও লিংক দিলে প্লেয়ারে এমবেড হবে" />
    <x-admin.field-textarea name="description" label="বিবরণ" :value="$v->description" :rows="3" />
    <div class="grid grid-cols-2 gap-3"><x-admin.field-input name="duration" label="দৈর্ঘ্য" :value="$v->duration" placeholder="12:35" />
        <x-admin.field-select name="status" label="স্ট্যাটাস" :options="\App\Models\Video::STATUSES" :selected="$v->status" required /></div>
    <x-admin.field-image name="thumbnail" label="থাম্বনেইল ছবি" :current="$v->thumbnail" />
    <div class="grid grid-cols-2 gap-3"><x-admin.field-input name="published_at" label="প্রকাশের সময়" type="datetime-local"
        :value="$v->published_at ? \Illuminate\Support\Carbon::parse($v->published_at)->format('Y-m-d\TH:i') : ''" />
    <x-admin.field-input name="scheduled_at" label="নির্ধারিত সময়" type="datetime-local"
        :value="$v->scheduled_at ? \Illuminate\Support\Carbon::parse($v->scheduled_at)->format('Y-m-d\TH:i') : ''" /></div>
    <div class="grid grid-cols-1 gap-2"><x-admin.field-checkbox name="is_reel" label="রিল / শর্ট ভিডিও" :checked="(bool)$v->is_reel" />
        <x-admin.field-checkbox name="is_visible" label="ওয়েবসাইটে দেখান" :checked="(bool)($v->is_visible ?? true)" /></div>
    <x-admin.field-input name="meta_title" label="মেটা টাইটেল" :value="$v->meta_title" />
    <x-admin.field-textarea name="meta_description" label="মেটা বিবরণ" :value="$v->meta_description" :rows="2" />
    <button type="submit" class="mc-btn mc-btn-primary w-full"><i class="ph-fill ph-floppy-disk"></i> {{ $edit ? 'আপডেট' : 'সংরক্ষণ' }}</button>
</form>
