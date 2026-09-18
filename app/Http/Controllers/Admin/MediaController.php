<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\ActivityLogger;
use App\Services\MediaUploader;
use Illuminate\Http\Request;

/** মিডিয়া লাইব্রেরি — Single/Multiple Upload, Search, Delete, Details, Copy URL */
class MediaController extends Controller
{
    public function __construct(private MediaUploader $uploader) {}

    public function index(Request $request)
    {
        $query = Media::with('uploader:id,name')->latest();

        if ($q = trim((string) $request->query('q'))) {
            $query->search($q);
        }
        if ($request->query('type') === 'image') { $query->images(); }
        if ($ext = $request->query('ext'))     { $query->where('extension', $ext); }

        $media = $query->paginate(24)->withQueryString();

        $imgbbService = new \App\Services\ImgbbService();

        return view('admin.media.index', [
            'media'  => $media,
            'stats'  => [
                'total' => Media::count(),
                'images'=> Media::images()->count(),
                'size'  => (int) Media::sum('size'),
                'imgbb' => Media::where('disk','imgbb')->count(),
            ],
            'imgbbEnabled' => $imgbbService->isEnabled(),
            'imgbbKey' => substr($imgbbService->getApiKey(), 0, 8).'****',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'files'   => ['required', 'array', 'min:1', 'max:20'],
            'files.*' => ['file', 'mimes:jpg,jpeg,png,webp,gif,avif,pdf', 'max:8192'],
            'folder'  => ['nullable', 'string', 'max:60'],
        ]);

        $folder = preg_replace('/[^a-z0-9\-_]/i', '', $request->input('folder', 'general')) ?: 'general';

        // আপলোড করা ফাইলের ধরন অনুযায়ী অনুমোদিত তালিকা ঠিক করা
        // (আগে PDF আপলোডে IMAGE_MIMES চেক ব্যর্থ হয়ে এরর হতো)
        $allowed = \App\Services\MediaUploader::IMAGE_MIMES;
        foreach ($request->file('files') as $file) {
            if (strtolower($file->getClientOriginalExtension()) === 'pdf') {
                $allowed = $allowed + \App\Services\MediaUploader::DOC_MIMES;
                break;
            }
        }

        $saved = [];

        foreach ($this->uploader->storeMany($request->file('files'), $folder, $allowed) as $media) {
            $saved[] = ['id' => $media->id, 'url' => $media->url, 'name' => $media->file_name];
        }

        ActivityLogger::log('upload', 'media', bn_num(count($saved)).'টি ফাইল আপলোড');

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'files' => $saved]);
        }

        return back()->with('success', bn_num(count($saved)).'টি ফাইল আপলোড হয়েছে।');
    }

    public function update(Request $request, Media $media)
    {
        $data = $request->validate([
            'alt_text' => ['nullable', 'string', 'max:190'],
            'caption'  => ['nullable', 'string', 'max:600'],
        ]);
        $media->update($data);
        return back()->with('success', 'মিডিয়া তথ্য হালনাগাদ হয়েছে।');
    }

    public function destroy(Media $media)
    {
        ActivityLogger::deleted($media, 'media', 'মিডিয়া মুছে ফেলা: '.$media->file_name);
        $this->uploader->delete($media);
        return back()->with('success', 'ফাইল মুছে ফেলা হয়েছে।');
    }
}
