@extends('admin.layouts.master')
@section('title','ক্যাটাগরি ব্যবস্থাপনা')
@section('content')
<x-admin.page-head title="ক্যাটাগরি ব্যবস্থাপনা" subtitle="সংবাদ বিভাগ, সাব-ক্যাটাগরি, ক্রম ও দৃশ্যমানতা নিয়ন্ত্রণ"
    action="catModal" actionLabel="নতুন ক্যাটাগরি" />

<div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    <div class="xl:col-span-2">
        <div class="mc-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500 dark:bg-slate-800/60 dark:text-slate-400">
                        <tr><th class="px-4 py-3">ক্যাটাগরি</th><th class="px-4 py-3">স্লাগ</th><th class="px-4 py-3 text-center">সংবাদ</th>
                            <th class="px-4 py-3 text-center">ক্রম</th><th class="px-4 py-3 text-center">স্ট্যাটাস</th><th class="px-4 py-3 text-right">অ্যাকশন</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($categories as $cat)
                            @include('admin.categories._row', ['cat' => $cat, 'level' => 0])
                            @foreach($cat->children as $child)
                                @include('admin.categories._row', ['cat' => $child, 'level' => 1])
                            @endforeach
                        @empty
                            <x-admin.empty-state colspan="6" icon="ph-tree-structure" message="কোনো ক্যাটাগরি নেই" hint="ডান পাশের ফর্ম থেকে প্রথম ক্যাটাগরি যোগ করুন।" />
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <p class="mt-3 text-[11px] text-slate-500">সাব-ক্যাটাগরিগুলো মূল ক্যাটাগরির নিচে ইনডেন্ট করে দেখানো হয়েছে। মোট: {{ bn_count($categories->whereNull('parent_id')->count()) }} মূল + {{ bn_count($categories->whereNotNull('parent_id')->count()) }} সাব।</p>
    </div>

    {{-- নতুন ক্যাটাগরি ফর্ম (সাইড প্যানেল + মোডাল দুইভাবেই কাজ করে) --}}
    <div>
        <div class="mc-card p-4">
            <h2 class="mb-4 flex items-center gap-2 font-serif text-sm font-bold text-slate-900 dark:text-white"><i class="ph ph-plus-circle text-[#E21D2B]"></i> নতুন ক্যাটাগরি</h2>
            <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <x-admin.field-select name="parent_id" label="প্যারেন্ট ক্যাটাগরি" placeholder="— মূল ক্যাটাগরি —"
                    :options="$categories->whereNull('parent_id')->pluck('name','id')->all()" />
                <x-admin.field-input name="name" label="ক্যাটাগরি নাম" required placeholder="যেমন: খেলাধুলা" />
                <x-admin.field-input name="slug" label="স্লাগ" placeholder="kheladhula" hint="খালি রাখলে নাম থেকে তৈরি হবে" />
                <div class="grid grid-cols-2 gap-3">
                    <x-admin.field-input name="icon" label="আইকন" placeholder="ph-soccer-ball" hint="Phosphor আইকন ক্লাস" />
                    <div>
                        <label class="mc-label">রঙ</label>
                        <input type="color" name="color" value="{{ old('color','#E21D2B') }}" class="h-10 w-full cursor-pointer rounded-lg border border-slate-300 bg-white p-1 dark:border-slate-700 dark:bg-slate-900">
                    </div>
                </div>
                <x-admin.field-input name="union_name" label="ইউনিয়ন / এলাকার নাম" placeholder="মহালছড়ি সদর" />
                <x-admin.field-textarea name="description" label="বিবরণ" :rows="2" />
                <x-admin.field-image name="image" label="ক্যাটাগরি ছবি" />
                <x-admin.field-input name="sort_order" label="ক্রম" type="number" value="0" step="1" />
                <x-admin.field-input name="meta_title" label="মেটা টাইটেল" />
                <x-admin.field-textarea name="meta_description" label="মেটা বিবরণ" :rows="2" />
                <div class="grid grid-cols-1 gap-2">
                    <x-admin.field-checkbox name="is_visible" label="দৃশ্যমান" :checked="true" />
                    <x-admin.field-checkbox name="show_on_home" label="হোমপেজে দেখান" :checked="true" hint="হোমে এই ক্যাটাগরির সর্বোচ্চ ২টি সংবাদ" />
                    <x-admin.field-checkbox name="show_in_menu" label="মেনুতে দেখান" :checked="true" />
                </div>
                <button type="submit" class="mc-btn mc-btn-primary w-full"><i class="ph-fill ph-floppy-disk"></i> সংরক্ষণ করুন</button>
            </form>
        </div>
    </div>
</div>

{{-- প্রতিটি ক্যাটাগরির এডিট মোডাল --}}
@foreach($categories as $cat)
    @include('admin.categories._edit', ['cat' => $cat])
    @foreach($cat->children as $child) @include('admin.categories._edit', ['cat' => $child]) @endforeach
@endforeach
<x-admin.modal id="catModal" title="নতুন ক্যাটাগরি">
    <p class="text-xs text-slate-600 dark:text-slate-300">ডান পাশের <strong>নতুন ক্যাটাগরি</strong> ফর্ম ব্যবহার করুন — সেখানে সব ফিল্ড রয়েছে।</p>
</x-admin.modal>
@endsection
