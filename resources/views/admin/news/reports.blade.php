@extends('admin.layouts.master')
@section('title','পাঠকের সংবাদ')
@section('content')
<x-admin.page-head title="পাঠকের সংবাদ / রিপোর্ট" subtitle="পাবলিক সাইট থেকে পাঠানো খবর — গ্রহণ করলে সংবাদ তৈরির জন্য প্রস্তুত" action="addReport" actionLabel="নতুন রিপোর্ট" />
<x-admin.filter-bar :action="route('admin.reports.index')" :fields="[
    ['name'=>'q','placeholder'=>'শিরোনাম বা পাঠকের নাম খুঁজুন…'],
    ['name'=>'status','type'=>'select','placeholder'=>'সব স্ট্যাটাস','options'=>\App\Models\NewsReport::STATUSES]]" />
<div class="space-y-3">
    @forelse($reports as $r)
        <div class="mc-card p-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="font-serif text-sm font-bold text-slate-900 dark:text-white">{{ $r->title }}</p>
                        @php $sc=['new'=>'bg-blue-500','reviewing'=>'bg-amber-500','accepted'=>'bg-emerald-500','rejected'=>'bg-slate-500'][$r->status]??'bg-slate-500'; @endphp
                        <span class="mc-badge {{ $sc }} text-white">{{ \App\Models\NewsReport::STATUSES[$r->status] ?? $r->status }}</span>
                    </div>
                    <p class="mt-1.5 text-xs leading-relaxed text-slate-600 dark:text-slate-300">{{ $r->description }}</p>
                    @if($r->location)<p class="mt-1 text-[11px] text-slate-500"><i class="ph ph-map-pin"></i> {{ $r->location }}</p>@endif
                    <p class="mt-2 text-[11px] text-slate-400">পাঠক: <strong>{{ $r->reporter_name }}</strong>
                        @if($r->reporter_phone) · <code dir="ltr">{{ $r->reporter_phone }}</code>@endif
                        @if($r->reporter_email) · {{ $r->reporter_email }}@endif
                        · {{ bn_ago($r->created_at) }}</p>
                    @if($r->admin_note)<p class="mt-2 rounded-lg bg-slate-50 p-2 text-[11px] text-slate-600 dark:bg-slate-800/60 dark:text-slate-300"><strong>মন্তব্য:</strong> {{ $r->admin_note }}</p>@endif
                </div>
                <div class="flex shrink-0 flex-col gap-1.5">
                    @if($r->status === 'accepted')<a href="{{ route('admin.news.create') }}" class="mc-btn mc-btn-primary text-xs"><i class="ph ph-newspaper"></i> সংবাদ তৈরি করুন</a>@endif
                    <button type="button" data-mc-modal-open="editRep-{{ $r->id }}" class="mc-btn mc-btn-ghost text-xs"><i class="ph ph-pencil-simple"></i> স্ট্যাটাস/মন্তব্য</button>
                    <form action="{{ route('admin.reports.destroy',$r) }}" method="POST" data-confirm="রিপোর্টটি মুছে ফেলবেন?">@csrf @method('DELETE')
                        <button type="submit" class="mc-btn mc-btn-ghost text-xs text-red-600"><i class="ph ph-trash-simple"></i> মুছুন</button></form>
                </div>
            </div>
        </div>
    @empty <x-admin.empty-state icon="ph-megaphone-simple" message="কোনো রিপোর্ট নেই" hint="পাবলিক সাইটের ‘সংবাদ পাঠান’ ফর্ম থেকে রিপোর্ট এলে এখানে দেখা যাবে।" /> @endforelse
</div>
{{ $reports->links() }}
@foreach($reports as $r)
<x-admin.modal :id="'editRep-'.$r->id" :title="'রিপোর্ট: '.Str::limit($r->title,40)">
    <form action="{{ route('admin.reports.update',$r) }}" method="POST" class="space-y-3">@csrf @method('PUT')
        <x-admin.field-select name="status" label="স্ট্যাটাস" :options="\App\Models\NewsReport::STATUSES" :selected="$r->status" required />
        <x-admin.field-textarea name="admin_note" label="অ্যাডমিন মন্তব্য" :value="$r->admin_note" :rows="3" hint="গ্রহণ/বাতিলের কারণ লিখুন" />
        <button type="submit" class="mc-btn mc-btn-primary w-full"><i class="ph-fill ph-floppy-disk"></i> সংরক্ষণ</button>
    </form>
</x-admin.modal>
@endforeach
<x-admin.modal id="addReport" title="নিজে থেকে রিপোর্ট যোগ">
    <p class="text-xs text-slate-600 dark:text-slate-300">রিপোর্ট সাধারণত পাবলিক সাইটের <a href="{{ route('submit-report') }}" target="_blank" class="font-bold text-[#E21D2B] underline">সংবাদ পাঠান</a> ফর্ম থেকে আসে। অ্যাডমিন প্যানেল থেকে সরাসরি রিপোর্ট তৈরির সুবিধা নেই — নতুন সংবাদ লিখতে <a href="{{ route('admin.news.create') }}" class="font-bold text-[#E21D2B] underline">সংবাদ যোগ করুন</a> ব্যবহার করুন।</p>
</x-admin.modal>
@endsection
