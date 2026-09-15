<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Services\ActivityLogger;
use App\Services\MediaUploader;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** ভিডিও ব্যবস্থাপনা — Facebook/YouTube URL, Thumbnail, Schedule, Status */
class VideoController extends Controller
{
    public function __construct(private MediaUploader $uploader) {}

    public function index(Request $request)
    {
        $query = Video::withTrashed()->latest();
        if ($status = $request->query('status')) { $query->where('status', $status); }
        if ($q = trim((string) $request->query('q'))) { $query->where('title', 'like', '%'.$q.'%'); }

        return view('admin.videos.index', ['videos' => $query->paginate(15)->withQueryString()]);
    }

    public function store(Request $request)
    {
        $video = Video::create($this->validateVideo($request));
        ActivityLogger::created($video, 'video', 'ভিডিও যোগ: '.$video->title);
        return back()->with('success', 'ভিডিও যোগ হয়েছে।');
    }

    public function update(Request $request, Video $video)
    {
        $video->update($this->validateVideo($request, $video));
        ActivityLogger::updated($video, 'video', 'ভিডিও হালনাগাদ: '.$video->title);
        return back()->with('success', 'ভিডিও হালনাগাদ হয়েছে।');
    }

    public function destroy(Video $video)
    {
        ActivityLogger::deleted($video, 'video', 'ভিডিও ট্র্যাশে: '.$video->title);
        $video->delete();
        return back()->with('success', 'ভিডিও রিসাইকল বিনে পাঠানো হয়েছে।');
    }

    public function restore(Video $video)
    {
        $video->restore();
        return back()->with('success', 'ভিডিও পুনরুদ্ধার হয়েছে।');
    }

    private function validateVideo(Request $request, ?Video $video = null): array
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'min:4', 'max:191'],
            'slug'        => ['nullable', 'string', 'max:191', Rule::unique('videos', 'slug')->ignore($video?->id)],
            'video_url'   => ['required', 'url', 'max:500'],
            'description' => ['nullable', 'string', 'max:2000'],
            'duration'    => ['nullable', 'string', 'max:20'],
            'is_reel'     => ['nullable', 'boolean'],
            'is_visible'  => ['nullable', 'boolean'],
            'status'      => ['required', Rule::in(['draft', 'scheduled', 'published', 'archived'])],
            'published_at'=> ['nullable', 'date'],
            'scheduled_at'=> ['nullable', 'date'],
            'thumbnail'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'meta_title'  => ['nullable', 'string', 'max:191'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ], [], ['title' => 'ভিডিওর শিরোনাম', 'video_url' => 'ভিডিও লিংক']);

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $this->uploader->store($request->file('thumbnail'), 'videos')->path;
        }
        $data['is_reel']    = $request->boolean('is_reel');
        $data['is_visible'] = $request->boolean('is_visible', true);
        if ($data['status'] === 'published' && empty($data['published_at'])) { $data['published_at'] = now(); }

        return $data;
    }
}
