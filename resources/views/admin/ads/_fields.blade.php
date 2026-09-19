@php $edit = $edit ?? false; $dt = fn($d) => $d ? \Illuminate\Support\Carbon::parse($d)->format('Y-m-d\TH:i') : ''; @endphp
<form action="{{ $edit ? route('admin.ads.update',$ad) : route('admin.ads.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">@csrf @if($edit) @method('PUT') @endif
    <x-admin.field-input name="title" label="বিজ্ঞাপনের নাম" :value="$ad->title" required hint="শুধু অ্যাডমিন প্যানেলে দেখা যায়" />
    <div class="grid grid-cols-2 gap-3"><x-admin.field-select name="position" label="পজিশন" :options="\App\Models\Advertisement::POSITIONS" :selected="$ad->position" required />
        <x-admin.field-select name="type" label="ধরন" :options="['image'=>'ছবি (ব্যানার)','html'=>'HTML কোড']" :selected="$ad->type" required /></div>
    <x-admin.field-image name="image" label="ব্যানার ছবি" :current="$ad->image"
        accept="image/jpeg,image/png,image/webp,image/gif" hint="লিডারবোর্ড ৭২৮×৯০, সাইডবার ৩০০×২৫০ প্রস্তাবিত" />
    <x-admin.field-textarea name="html_code" label="HTML / এমবেড কোড" :value="$ad->html_code" :rows="4" hint="ধরন ‘HTML’ নির্বাচন করলে এই কোডটি দেখানো হবে" />
    <div class="grid grid-cols-2 gap-3"><x-admin.field-input name="link" label="ক্লিক লিংক" type="url" :value="$ad->link" placeholder="https://…" />
        <x-admin.field-select name="link_target" label="লিংক খোলার নিয়ম" :options="['_self'=>'একই ট্যাবে','_blank'=>'নতুন ট্যাবে']" :selected="$ad->link_target" /></div>
    <div class="grid grid-cols-2 gap-3"><x-admin.field-input name="starts_at" label="শুরু" type="datetime-local" :value="$dt($ad->starts_at)" />
        <x-admin.field-input name="ends_at" label="শেষ" type="datetime-local" :value="$dt($ad->ends_at)" hint="খালি = সবসময় সক্রিয়" /></div>
    <div class="grid grid-cols-2 gap-3"><x-admin.field-input name="priority" label="অগ্রাধিকার" type="number" :value="$ad->priority ?? 0" step="1" hint="বড় সংখ্যা আগে দেখাবে" />
        <div class="self-end"><x-admin.field-checkbox name="is_enabled" label="সক্রিয়" :checked="(bool)($ad->is_enabled ?? true)" /></div></div>
    <button type="submit" class="mc-btn mc-btn-primary w-full"><i class="ph-fill ph-floppy-disk"></i> {{ $edit ? 'আপডেট' : 'সংরক্ষণ' }}</button>
</form>
