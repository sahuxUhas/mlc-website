@extends('admin.layouts.master')
@section('title','ট্যাগ')
@section('content')
<x-admin.page-head title="ট্যাগ ব্যবস্থাপনা" subtitle="সংবাদের ট্যাগ — সার্চ ও এসইও লিংক তৈরি করে" />

<x-admin.filter-bar :action="route('admin.tags.index')"
    :fields="[['name'=>'q','placeholder'=>'ট্যাগ খুঁজুন…']]" />

<div class="mc-card p-4">
    <div class="flex flex-wrap gap-2">
        @forelse($tags as $tag)
            <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-300 bg-white py-1 pl-3 pr-1 text-xs font-bold text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                <a href="{{ route('admin.tags.index', ['q' => $tag->name]) }}">{{ $tag->name }}</a>
                <span class="font-normal text-slate-400">{{ bn_count($tag->posts_count) }}</span>
                @if(auth()->user()->can_manage('tags.manage'))
                    <form action="{{ route('admin.tags.destroy', $tag) }}" method="POST" data-confirm="‘{{ $tag->name }}’ ট্যাগটি মুছে ফেলবেন?">
                        @csrf @method('DELETE')
                        <button type="submit" class="rounded-full p-0.5 text-slate-400 hover:bg-red-50 hover:text-red-600" title="মুছুন"><i class="ph-bold ph-x text-[11px]"></i></button>
                    </form>
                @endif
            </span>
        @empty
            <x-admin.empty-state icon="ph-hash" message="কোনো ট্যাগ পাওয়া যায়নি" hint="সংবাদ সংরক্ষণের সময় ট্যাগ লিখলে স্বয়ংক্রিয়ভাবে তৈরি হয়।" />
        @endforelse
    </div>

    <div class="mt-4 border-t border-slate-200 pt-3 dark:border-slate-800">
        <p class="text-[11px] text-slate-500">মোট {{ bn_count($tags->total()) }}টি ট্যাগ। ট্যাগ সংবাদ থেকে স্বয়ংক্রিয়ভাবে তৈরি হয়; এখানে শুধু অপ্রয়োজনীয় ট্যাগ পরিষ্কার করা যায়।</p>
        {{ $tags->links() }}
    </div>
</div>
@endsection
