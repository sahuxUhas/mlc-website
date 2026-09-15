@extends('admin.layouts.master')
@section('title','ব্যবহারকারী')
@section('content')
<x-admin.page-head title="ব্যবহারকারী ও ভূমিকা" subtitle="সুপার অ্যাডমিন, এডিটর, রিপোর্টার ও মডারেটর — অনুমতি অনুযায়ী প্যানেল অ্যাক্সেস" action="addUser" actionLabel="নতুন ব্যবহারকারী" />
<x-admin.filter-bar :action="route('admin.users.index')" :fields="[
    ['name'=>'q','placeholder'=>'নাম বা ইমেইল খুঁজুন…'],
    ['name'=>'role','type'=>'select','placeholder'=>'সব ভূমিকা','options'=>\App\Models\User::ROLES]]" />
<div class="mc-card overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left text-sm">
    <thead class="bg-slate-50 text-[11px] uppercase text-slate-500 dark:bg-slate-800/60 dark:text-slate-400"><tr>
        <th class="px-4 py-3">ব্যবহারকারী</th><th class="px-4 py-3">ভূমিকা</th><th class="px-4 py-3">পদবি</th>
        <th class="px-4 py-3">শেষ লগইন</th><th class="px-4 py-3 text-center">স্ট্যাটাস</th><th class="px-4 py-3 text-right">অ্যাকশন</th></tr></thead>
    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        @forelse($users as $u)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                <td class="px-4 py-2.5"><div class="flex items-center gap-2.5">
                    <img src="{{ mc_image($u->photo, mc_placeholder_svg('ph-user-circle')) }}" alt="" class="h-9 w-9 shrink-0 rounded-full border border-slate-200 object-cover dark:border-slate-700" loading="lazy">
                    <div class="min-w-0"><p class="truncate font-serif text-sm font-bold text-slate-900 dark:text-white">{{ $u->name }}</p>
                        <p class="truncate text-[11px] text-slate-500">{{ $u->email }}</p></div></div></td>
                <td class="px-4 py-2.5">@php $rc=['super_admin'=>'bg-[#E21D2B] text-white','editor'=>'bg-blue-600 text-white','reporter'=>'bg-emerald-600 text-white','moderator'=>'bg-purple-600 text-white'][$u->role]??'bg-slate-500 text-white'; @endphp
                    <span class="mc-badge {{ $rc }}">{{ \App\Models\User::ROLES[$u->role] ?? $u->role }}</span></td>
                <td class="px-4 py-2.5 text-xs text-slate-600 dark:text-slate-300">{{ $u->designation ?: '—' }}</td>
                <td class="px-4 py-2.5 text-[11px] text-slate-500">{{ $u->last_login_at ? bn_ago($u->last_login_at) : 'কখনো নয়' }}</td>
                <td class="px-4 py-2.5 text-center">@if($u->is_active)<span class="mc-badge bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">সক্রিয়</span>@else<span class="mc-badge bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300">বন্ধ</span>@endif</td>
                <td class="px-4 py-2.5 text-right">
                    @if($u->id !== auth()->id())
                        <x-admin.action-buttons :edit="'editUser-'.$u->id"
                            :destroy="auth()->user()->isSuperAdmin() ? route('admin.users.destroy',$u) : null"
                            deleteConfirm="‘{{ $u->name }}’ ব্যবহারকারী মুছে ফেলবেন?" />
                    @else <span class="mc-badge bg-slate-100 text-slate-500 dark:bg-slate-800">আপনি</span> @endif</td></tr>
        @empty <x-admin.empty-state colspan="6" icon="ph-users" message="কোনো ব্যবহারকারী নেই" hint="উপরের ‘নতুন ব্যবহারকারী’ বাটনে ক্লিক করুন।" /> @endforelse
    </tbody></table></div></div>
{{ $users->links() }}
<x-admin.modal id="addUser" title="নতুন ব্যবহারকারী">@include('admin.users._fields',['u'=>new \App\Models\User(['role'=>'reporter','is_active'=>true])])</x-admin.modal>
@foreach($users as $u)@if($u->id !== auth()->id())<x-admin.modal :id="'editUser-'.$u->id" :title="'এডিট: '.$u->name">@include('admin.users._fields',['u'=>$u,'edit'=>true])</x-admin.modal>@endif @endforeach
@endsection
