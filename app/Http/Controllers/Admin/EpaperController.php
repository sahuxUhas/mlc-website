<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Epaper;
use App\Services\ActivityLogger;
use App\Services\MediaUploader;
use Illuminate\Http\Request;

/** ই-পেপার ব্যবস্থাপনা */
class EpaperController extends Controller
{
    public function __construct(private MediaUploader $uploader) {}

    public function index()
    {
        return view('admin.pages.epapers', ['epapers' => Epaper::withTrashed()->orderByDesc('issue_date')->paginate(20)]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'min:2', 'max:191'],
            'issue_date'  => ['required', 'date'],
            'file'        => ['required', 'file', 'mimes:pdf', 'max:51200'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'pages'       => ['nullable', 'integer', 'min:0', 'max:200'],
            'is_visible'  => ['nullable', 'boolean'],
        ], [], ['title' => 'শিরোনাম', 'file' => 'পিডিএফ ফাইল']);

        $data['file_path']   = $this->uploader->store($request->file('file'), 'epapers', MediaUploader::DOC_MIMES)->path;
        $data['is_visible']  = $request->boolean('is_visible', true);
        $data['size_label']  = round($request->file('file')->getSize() / 1048576, 1).' MB';

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->uploader->store($request->file('cover_image'), 'epapers/covers')->path;
        }

        $epaper = Epaper::create($data);
        ActivityLogger::created($epaper, 'epaper', 'ই-পেপার যোগ: '.$epaper->title);

        return back()->with('success', 'ই-পেপার যোগ হয়েছে।');
    }

    public function destroy(Epaper $epaper)
    {
        ActivityLogger::deleted($epaper, 'epaper', 'ই-পেপার ট্র্যাশে');
        $epaper->delete();
        return back()->with('success', 'ই-পেপার মুছে ফেলা হয়েছে।');
    }
}
