<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\ActivityLogger;
use App\Services\MediaUploader;
use Illuminate\Http\Request;

/**
 * মিডিয়া লাইব্রেরি — Single/Multiple Upload, Search, Filter, Details, Delete।
 *
 * ছবি ImgBB API তে যায় (backend key), DB-তে শুধু রেফারেন্স থাকে এবং UI-তে
 * শুধু থাম্বনেইল/প্রিভিউ দেখা যায় — raw hosting URL বা API Key কোথাও দেখানো হয় না।
 */
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
        if ($ext = $request->query('ext'))       { $query->where('extension', $ext); }
        if ($folder = $request->query('folder')) { $query->where('folder', $folder); }

        $media = $query->paginate(24)->withQueryString();

        return view('admin.media.index', [
            'media'  => $media,
            'folders' => $this->folderOptions(),
            'isImgbbMedia' => Media::where('provider', 'imgbb')->orWhere('disk', 'imgbb')->exists(),
            'stats'  => [
                'total'  => Media::count(),
                'images' => Media::images()->count(),
                'size'   => (int) Media::sum('size'),
                'imgbb'  => Media::where('provider', 'imgbb')->orWhere('disk', 'imgbb')->count(),
            ],
            // নিরাপদ সারাংশ — API Key কখনো ভিউতে যায় না
            'provider' => $this->uploader->providerStatus(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'files'   => ['required', 'array', 'min:1', 'max:'.max(1, (int) config('images.max_files', 20))],
            'files.*' => ['file', 'mimes:jpg,jpeg,png,webp,gif,avif,pdf', 'max:8192'],
            'folder'  => ['nullable', 'string', 'max:60'],
        ], [], ['files' => 'ফাইল']);

        $folder = preg_replace('/[^a-z0-9\-_]/i', '', (string) $request->input('folder', 'general')) ?: 'general';

        // আপলোড করা ফাইলের ধরন অনুযায়ী অনুমোদিত তালিকা ঠিক করা (PDF আলাদা)
        $allowed = MediaUploader::IMAGE_MIMES;
        foreach ($request->file('files') as $file) {
            if (strtolower((string) $file->getClientOriginalExtension()) === 'pdf') {
                $allowed = $allowed + MediaUploader::DOC_MIMES;
                break;
            }
        }

        $result = $this->uploader->storeManyCollect($request->file('files'), $folder, $allowed);

        $saved = array_map(fn (Media $media) => [
            'id'    => $media->id,
            'name'  => $media->display_name,
            // raw পাথ নয় — ব্রাউজারে ব্যবহারযোগ্য signed proxy URL
            'url'   => $media->url,
            'thumb' => $media->thumb,
            'image' => $media->is_image,
        ], $result['saved']);

        if ($saved !== []) {
            ActivityLogger::log('upload', 'media', bn_num(count($saved)).'টি ফাইল আপলোড ('.$this->uploader->providerStatus()['provider_label'].')');
        }

        $message = $saved !== []
            ? bn_num(count($saved)).'টি ফাইল আপলোড হয়েছে।'
            : 'কোনো ফাইল আপলোড করা যায়নি।';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $saved !== [],
                'files'   => $saved,
                'errors'  => $result['errors'],
                'message' => $message,
            ], $saved !== [] ? 200 : 422);
        }

        return back()
            ->with($saved !== [] ? 'success' : 'error', $message)
            ->with('upload_errors', $result['errors']);
    }

    public function update(Request $request, Media $media)
    {
        $data = $request->validate([
            'alt_text' => ['nullable', 'string', 'max:190'],
            'caption'  => ['nullable', 'string', 'max:600'],
            'folder'   => ['nullable', 'string', 'max:60'],
        ]);

        if (isset($data['folder'])) {
            $data['folder'] = preg_replace('/[^a-z0-9\-_]/i', '', (string) $data['folder']) ?: 'general';
        }

        $media->update($data);

        return back()->with('success', 'মিডিয়া তথ্য হালনাগাদ হয়েছে।');
    }

    public function destroy(Media $media)
    {
        ActivityLogger::deleted($media, 'media', 'মিডিয়া মুছে ফেলা: '.$media->display_name);
        $this->uploader->delete($media);

        return back()->with('success', 'ফাইল মুছে ফেলা হয়েছে।');
    }

    /** ফোল্ডার তালিকা (কলাম না থাকলেও পেজ ভাঙবে না) */
    private function folderOptions(): array
    {
        try {
            return Media::query()
                ->whereNotNull('folder')
                ->distinct()
                ->orderBy('folder')
                ->pluck('folder', 'folder')
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }
}
