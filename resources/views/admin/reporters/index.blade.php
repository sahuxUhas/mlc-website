@extends('admin.layouts.master')
@section('title','রিপোর্টার')
@section('content')
<x-admin.page-head title="রিপোর্টার / লেখক" subtitle="প্রতিবেদক প্রোফাইল, যোগাযোগ ও দৃশ্যমানতা" action="addReporter" actionLabel="নতুন রিপোর্টার" />
<div class="mc-card overflow-hidden">
    <div class="overflow-x-auto"><table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-[11px] uppercase text-slate-500 dark:bg-slate-800/60 dark:text-slate-400">
            <tr><th class="px-4 py-3">রিপোর্টার</th><th class="px-4 py-3">পদবি / এলাকা</th><th class="px-4 py-3">যোগাযোগ</th>
                <th class="px-4 py-3 text-center">সংবাদ</th><th class="px-4 py-3 text-center">ক্রম</th><th class="px-4 py-3 text-right">অ্যাকশন</th></tr></thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($reporters as $r)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                    <td class="px-4 py-2.5"><div class="flex items-center gap-2.5">
                        <img src="{{ mc_image($r->photo, mc_placeholder_svg('ph-user-circle')) }}" alt="" class="h-9 w-9 shrink-0 rounded-full border border-slate-200 object-cover dark:border-slate-700" loading="lazy">
                        <div class="min-w-0"><p class="truncate font-serif text-sm font-bold text-slate-900 dark:text-white">{{ $r->name }}</p>
                            <code class="text-[10px] text-slate-400" dir="ltr">{{ $r->slug }}</code></div></div></td>
                    <td class="px-4 py-2.5 text-xs text-slate-600 dark:text-slate-300">{{ $r->designation }}@if($r->address)<span class="block text-[11px] text-slate-400">{{ $r->address }}</span>@endif</td>
                    <td class="px-4 py-2.5 text-[11px] text-slate-600 dark:text-slate-300">@if($r->phone)<span dir="ltr">{{ $r->phone }}</span>@endif @if($r->email)<span class="block truncate">{{ $r->email }}</span>@endif</td>
                    <td class="px-4 py-2.5 text-center"><span class="mc-badge bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ bn_count($r->publishedPostsCount()) }}</span></td>
                    <td class="px-4 py-2.5 text-center">{{ bn_num($r->sort_order) }}</td>
                    <td class="px-4 py-2.5 text-right"><x-admin.action-buttons :edit="'editRep-'.$r->id" :view="route('reporters.show',$r->slug)"
                        :destroy="route('admin.reporters.destroy',$r)" deleteConfirm="রিপোর্টার ‘{{ $r->name }}’ মুছে ফেলবেন? তাঁর সংবাদগুলো অ্যাসাইনমেন্ট হারাবে।" /></td>
                </tr>
            @empty <x-admin.empty-state colspan="6" icon="ph-users-three" message="কোনো রিপোর্টার যোগ করা হয়নি" hint="উপরের ‘নতুন রিপোর্টার’ বাটনে ক্লিক করুন।" /> @endforelse
        </tbody></table></div>
</div>

<x-admin.modal id="addReporter" title="নতুন রিপোর্টার"><form action="{{ route('admin.reporters.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">@csrf
    <x-admin.field-input name="name" label="নাম" required /><x-admin.field-input name="slug" label="স্লাগ" hint="খালি রাখলে নাম থেকে তৈরি হবে" />
    <x-admin.field-input name="designation" label="পদবি" placeholder="নিজেস্ব প্রতিবেদক" />
    <x-admin.field-textarea name="bio" label="জীবনী" :rows="3" /><x-admin.field-input name="email" label="ইমেইল" type="email" />
    <x-admin.field-input name="phone" label="ফোন" placeholder="01XXXXXXXXX" /><x-admin.field-input name="address" label="ঠিকানা / এলাকা" />
    <x-admin.field-input name="facebook" label="ফেসবুক প্রোফাইল URL" type="url" />
    <x-admin.field-image name="photo" label="প্রোফাইল ছবি" /><x-admin.field-input name="sort_order" label="ক্রম" type="number" value="0" step="1" />
    <x-admin.field-checkbox name="is_visible" label="ওয়েবসাইটে দেখান" :checked="true" />
    <button type="submit" class="mc-btn mc-btn-primary w-full"><i class="ph-fill ph-floppy-disk"></i> সংরক্ষণ</button></form></x-admin.modal>

@foreach($reporters as $r)
<x-admin.modal :id="'editRep-'.$r->id" :title="'এডিট: '.$r->name"><form action="{{ route('admin.reporters.update',$r) }}" method="POST" enctype="multipart/form-data" class="space-y-3">@csrf @method('PUT')
    <x-admin.field-input name="name" label="নাম" :value="$r->name" required /><x-admin.field-input name="slug" label="স্লাগ" :value="$r->slug" />
    <x-admin.field-input name="designation" label="পদবি" :value="$r->designation" /><x-admin.field-textarea name="bio" label="জীবনী" :value="$r->bio" :rows="3" />
    <x-admin.field-input name="email" label="ইমেইল" type="email" :value="$r->email" /><x-admin.field-input name="phone" label="ফোন" :value="$r->phone" />
    <x-admin.field-input name="address" label="ঠিকানা / এলাকা" :value="$r->address" /><x-admin.field-input name="facebook" label="ফেসবুক URL" type="url" :value="$r->facebook" />
    <x-admin.field-image name="photo" label="প্রোফাইল ছবি" :current="$r->photo" /><x-admin.field-input name="sort_order" label="ক্রম" type="number" :value="$r->sort_order" step="1" />
    <x-admin.field-checkbox name="is_visible" label="ওয়েবসাইটে দেখান" :checked="(bool)$r->is_visible" />
    <button type="submit" class="mc-btn mc-btn-primary w-full"><i class="ph-fill ph-floppy-disk"></i> আপডেট</button></form></x-admin.modal>
@endforeach
@endsection
