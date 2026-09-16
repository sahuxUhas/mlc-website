@extends('admin.layouts.master')
@section('title','আমার প্রোফাইল')
@section('content')
<x-admin.page-head title="আমার প্রোফাইল" :subtitle="'ভূমিকা: '.(\App\Models\User::ROLES[$user->role] ?? $user->role)" />
<div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
    <div class="mc-card p-4 sm:p-5">
        <h2 class="mb-4 flex items-center gap-2 font-serif text-sm font-bold text-slate-900 dark:text-white"><i class="ph ph-user-circle text-[#E21D2B]"></i> প্রোফাইল তথ্য</h2>
        <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-3">@csrf @method('PUT')
            <div class="flex items-center gap-3"><img src="{{ mc_image($user->photo, mc_placeholder_svg('ph-user-circle')) }}" alt="" class="h-14 w-14 rounded-full border border-slate-200 object-cover dark:border-slate-700">
                <p class="text-[11px] text-slate-500">শেষ লগইন: {{ $user->last_login_at ? bn_date($user->last_login_at, true).' '.bn_date($user->last_login_at, false) : '—' }}</p></div>
            <x-admin.field-input name="name" label="নাম" :value="$user->name" required />
            <x-admin.field-input name="email" label="ইমেইল" type="email" :value="$user->email" required />
            <x-admin.field-input name="phone" label="ফোন" :value="$user->phone" />
            <x-admin.field-input name="designation" label="পদবি" :value="$user->designation" />
            <x-admin.field-textarea name="bio" label="জীবনী" :value="$user->bio" :rows="3" />
            <x-admin.field-image name="photo" label="প্রোফাইল ছবি" :current="$user->photo" />
            <button type="submit" class="mc-btn mc-btn-primary w-full"><i class="ph-fill ph-floppy-disk"></i> প্রোফাইল আপডেট</button>
        </form>
    </div>
    <div class="mc-card p-4 sm:p-5">
        <h2 class="mb-4 flex items-center gap-2 font-serif text-sm font-bold text-slate-900 dark:text-white"><i class="ph ph-lock-key text-[#E21D2B]"></i> পাসওয়ার্ড পরিবর্তন</h2>
        <form action="{{ route('admin.profile.password') }}" method="POST" class="space-y-3">@csrf @method('PUT')
            <x-admin.field-input name="current_password" label="বর্তমান পাসওয়ার্ড" type="password" required />
            <x-admin.field-input name="password" label="নতুন পাসওয়ার্ড" type="password" required hint="কমপক্ষে ৮ অক্ষর, একটি সংখ্যা ও একটি বিশেষ চিহ্ন" />
            <x-admin.field-input name="password_confirmation" label="নতুন পাসওয়ার্ড নিশ্চিত করুন" type="password" required />
            <button type="submit" class="mc-btn mc-btn-primary w-full"><i class="ph-fill ph-shield-check"></i> পাসওয়ার্ড পরিবর্তন</button>
        </form>
        <div class="mt-5 rounded-xl bg-slate-50 p-3 dark:bg-slate-800/50">
            <p class="text-[11px] font-bold text-slate-700 dark:text-slate-200"><i class="ph ph-shield-warning text-amber-600"></i> নিরাপত্তা পরামর্শ</p>
            <ul class="mt-1.5 space-y-1 text-[11px] text-slate-600 dark:text-slate-400">
                <li>• ডিফল্ট পাসওয়ার্ড অবশ্যই বদলান।</li><li>• অন্য সাইটের পাসওয়ার্ড পুনর্ব্যবহার করবেন না।</li>
                <li>• অজানা ডিভাইসে লগইন করলে কাজ শেষে লগআউট করুন।</li>
            </ul>
        </div>
    </div>
</div>
@endsection
