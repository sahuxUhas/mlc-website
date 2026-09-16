@extends('admin.layouts.master')
@section('title','ভিডিও')
@section('content')
<x-admin.page-head title="ভিডিও ব্যবস্থাপনা" subtitle="ফেসবুক/ইউটিউব ভিডিও, থাম্বনেইল ও স্ট্যাটাস" action="addVideo" actionLabel="নতুন ভিডিও" />
<x-admin.filter-bar :action="route('admin.videos.index')" :fields="[
    ['name'=>'q','placeholder'=>'শিরোনাম খুঁজুন…'],
    ['name'=>'status','type'=>'select','placeholder'=>'সব স্ট্যাটাস','options'=>['draft'=>'খসড়া','published'=>'প্রকাশিত','scheduled'=>'নির্ধারিত','archived'=>'আর্কাইভ']]]" />
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @forelse($videos as $v)
        <div class="mc-card overflow-hidden"><div class="relative aspect-video bg-slate-900">
            <img src="{{ mc_image($v->thumbnail) }}" alt="" class="h-full w-full object-cover opacity-90" loading="lazy">
            <span class="absolute inset-0 flex items-center justify-center"><i class="ph-fill ph-play-circle text-4xl text-white/90 drop-shadow"></i></span>
            @if($v->duration)<span class="absolute bottom-1.5 right-1.5 rounded bg-black/75 px-1.5 py-0.5 text-[10px] font-bold text-white" dir="ltr">{{ $v->duration }}</span>@endif
            <span class="absolute left-1.5 top-1.5 @if($v->is_reel) bg-purple-600 @else bg-black/70 @endif rounded px-1.5 py-0.5 text-[9px] font-black text-white">{{ $v->is_reel ? 'REEL' : 'VIDEO' }}</span>
            @php $sc=['draft'=>'bg-slate-500','pending'=>'bg-amber-500','published'=>'bg-emerald-500','scheduled'=>'bg-blue-500','archived'=>'bg-slate-400'][$v->status]??'bg-slate-500'; @endphp
            <span class="absolute right-1.5 top-1.5 rounded px-1.5 py-0.5 text-[9px] font-black text-white {{ $sc }}">{{ \App\Models\Video::STATUSES[$v->status] ?? $v->status }}</span>
        </div><div class="p-3"><p class="line-clamp-2 font-serif text-sm font-bold text-slate-900 dark:text-white">{{ $v->title }}</p>
            <p class="mt-1 truncate text-[11px] text-slate-500" dir="ltr">{{ $v->video_url }}</p>
            <p class="mt-1 text-[11px] text-slate-400"><i class="ph ph-eye"></i> {{ bn_count($v->views) }} ভিউ · {{ bn_date($v->published_at ?: $v->created_at) }}</p>
            <div class="mt-2.5 flex items-center justify-between gap-2"><x-admin.action-buttons :edit="'editVid-'.$v->id" :view="route('videos.show',$v->slug)"
                :toggle="null" :destroy="route('admin.videos.destroy',$v)" :restore="route('admin.videos.restore',$v)" />
                <a href="{{ route('admin.videos.index',['status'=>$v->status]) }}" class="text-[10px] font-bold text-slate-400 hover:text-[#E21D2B]">ফিল্টার</a></div></div></div>
    @empty <div class="sm:col-span-2 lg:col-span-3"><x-admin.empty-state icon="ph-video-camera" message="কোনো ভিডিও নেই" hint="উপরের ‘নতুন ভিডিও’ বাটনে ক্লিক করুন।" /></div> @endforelse
</div>
{{ $videos->links() }}
<x-admin.modal id="addVideo" title="নতুন ভিডিও" size="lg">@include('admin.videos._fields',['v'=>new \App\Models\Video(['status'=>'published','is_visible'=>true])])</x-admin.modal>
@foreach($videos as $v)<x-admin.modal :id="'editVid-'.$v->id" :title="'এডিট: '.$v->title">@include('admin.videos._fields',['v'=>$v,'edit'=>true])</x-admin.modal>@endforeach
@endsection
