@php $edit = $edit ?? false; @endphp
<form action="{{ $edit ? route('admin.users.update',$u) : route('admin.users.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">@csrf @if($edit) @method('PUT') @endif
    <x-admin.field-input name="name" label="নাম" :value="$u->name" required />
    <x-admin.field-input name="email" label="ইমেইল" type="email" :value="$u->email" required />
    <x-admin.field-input name="password" :label="$edit ? 'পাসওয়ার্ড (পরিবর্তন করতে চাইলে)' : 'পাসওয়ার্ড'" type="password" :required="!$edit" hint="কমপক্ষে ৮ অক্ষর" />
    @if($edit)<x-admin.field-input name="password_confirmation" label="পাসওয়ার্ড নিশ্চিত করুন" type="password" />@endif
    <x-admin.field-select name="role" label="ভূমিকা" :options="\App\Models\User::ROLES" :selected="$u->role" required
        hint="সুপার অ্যাডমিন = সব কিছু, এডিটর = কনটেন্ট প্রকাশ, রিপোর্টার = নিজের সংবাদ, মডারেটর = মন্তব্য" />
    <div class="grid grid-cols-2 gap-3"><x-admin.field-input name="phone" label="ফোন" :value="$u->phone" />
        <x-admin.field-input name="designation" label="পদবি" :value="$u->designation" /></div>
    <x-admin.field-textarea name="bio" label="জীবনী" :value="$u->bio" :rows="2" />
    <x-admin.field-image name="photo" label="প্রোফাইল ছবি" :current="$u->photo" />
    <x-admin.field-checkbox name="is_active" label="অ্যাকাউন্ট সক্রিয়" :checked="(bool)($u->is_active ?? true)" hint="বন্ধ করলে লগইন করতে পারবে না" />
    <button type="submit" class="mc-btn mc-btn-primary w-full"><i class="ph-fill ph-floppy-disk"></i> {{ $edit ? 'আপডেট' : 'তৈরি করুন' }}</button>
</form>
