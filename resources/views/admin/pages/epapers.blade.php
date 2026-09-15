@extends('admin.layouts.master')
@section('title','ই-পেপার')
@section('content')
<x-admin.page-head title="ই-পেপার ব্যবস্থাপনা" subtitle="প্রতিদিনের পত্রিকা PDF আপলোড ও ডাউনলোড" action="addEpaper" actionLabel="নতুন ই-পেপার" />
<div class="mc-card overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left text-sm">
    <thead class="bg-slate-50 text-[11px] uppercase text-slate-500 dark:bg-slate-800/60 dark:text-slate-400"><tr>
        <th class="px-4 py-3">সংখ্যা</th><th class="px-4 py-3">তারিখ</th><th class="px-4 py-3">ফাইল</th>
        <th class="px-4 py-3 text-center">ডাউনলোড</th><th class="px-4 py-3 text-right">অ্যাকশন</th></tr></thead>
    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        @forelse($epapers as $e)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 {{ $e->trashed() ? 'opacity-60' : '' }}">
                <td class="px-4 py-2.5"><div class="flex items-center gap-2.5">
                    @if($e->cover_image)<img src="{{ mc_image($e->cover_image) }}" alt="" class="h-12 w-9 shrink-0 rounded border border-slate-200 object-cover" loading="lazy">
                    @else<span class="flex h-12 w-9 shrink-0 items-center justify-center rounded border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-800"><i class="ph ph-file-pdf text-red-500"></i></span>@endif
                    <p class="font-serif text-sm font-bold text-slate-900 dark:text-white">{{ $e->title }}</p></div></td>
                <td class="px-4 py-2.5 text-xs">{{ bn_date($e->issue_date) }}</td>
                <td class="px-4 py-2.5"><code class="text-[10px] text-slate-500" dir="ltr">{{ $e->file_path }}</code>
                    <span class="block text-[10px] text-slate-400">{{ number_format(($e->file_size ?? 0)/1048576,2) }} MB</span></td>
                <td class="px-4 py-2.5 text-center">{{ bn_count($e->downloads) }}</td>
                <td class="px-4 py-2.5 text-right"><div class="flex justify-end gap-1">
                    <a href="{{ route('epaper.download',$e) }}" target="_blank" title="ডাউনলোড" class="rounded border border-slate-300 p-1.5 text-slate-600 hover:border-blue-500 hover:text-blue-600 dark:border-slate-700 dark:text-slate-300"><i class="ph ph-download-simple"></i></a>
                    <form action="{{ route('admin.epapers.destroy',$e) }}" method="POST" data-confirm="ই-পেপারটি মুছে ফেলবেন?">@csrf @method('DELETE')
                        <button type="submit" title="মুছুন" class="rounded border border-red-300 p-1.5 text-red-600 hover:bg-red-50 dark:border-red-800"><i class="ph ph-trash-simple"></i></button></form>
                </div></td></tr>
        @empty <x-admin.empty-state colspan="5" icon="ph-newspaper-clipping" message="কোনো ই-পেপার আপলোড করা হয়নি" hint="উপরের ‘নতুন ই-পেপার’ বাটনে ক্লিক করুন।" /> @endforelse
    </tbody></table></div></div>
{{ $epapers->links() }}
<x-admin.modal id="addEpaper" title="নতুন ই-পেপার আপলোড">
    <form action="{{ route('admin.epapers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">@csrf
        <x-admin.field-input name="title" label="সংখ্যার নাম" required placeholder="১৬ সেপ্টেম্বর ২০২৬, বৃহস্পতিবার" />
        <x-admin.field-input name="issue_date" label="প্রকাশের তারিখ" type="date" required />
        <div><label for="file" class="mc-label">PDF ফাইল <span class="text-[#E21D2B]">*</span></label>
            <input id="file" name="file" type="file" accept="application/pdf" required class="block w-full text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-bold dark:file:bg-slate-800">
            <p class="mt-1 text-[11px] text-slate-500">সর্বোচ্চ ৫০ MB</p>@error('file')<p class="mt-1 text-[11px] font-semibold text-red-600">{{ $message }}</p>@enderror</div>
        <x-admin.field-image name="cover_image" label="কভার ছবি (ঐচ্ছিক)" />
        <button type="submit" class="mc-btn mc-btn-primary w-full"><i class="ph-fill ph-upload-simple"></i> আপলোড করুন</button>
    </form>
</x-admin.modal>
@endsection
