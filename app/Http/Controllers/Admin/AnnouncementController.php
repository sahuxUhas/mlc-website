<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\ActivityLogger;
use App\Services\MediaUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

/** ঘোষণা ব্যবস্থাপনা — Priority, Schedule, Expiry, Status */
class AnnouncementController extends Controller
{
    public function __construct(private MediaUploader $uploader) {}

    public function index(Request $request)
    {
        $query = Announcement::withTrashed()->latest();
        if ($status = $request->query('status')) { $query->where('status', $status); }
        return view('admin.announcements.index', ['announcements' => $query->paginate(15)->withQueryString()]);
    }

    public function store(Request $request)
    {
        $item = Announcement::create($this->validateAnnouncement($request));
        $this->flush();
        ActivityLogger::created($item, 'announcement', 'ঘোষণা যোগ: '.$item->title);
        return back()->with('success', 'ঘোষণা যোগ হয়েছে।');
    }

    public function update(Request $request, Announcement $announcement)
    {
        $announcement->update($this->validateAnnouncement($request, $announcement));
        $this->flush();
        ActivityLogger::updated($announcement, 'announcement', 'ঘোষণা হালনাগাদ');
        return back()->with('success', 'ঘোষণা হালনাগাদ হয়েছে।');
    }

    public function destroy(Announcement $announcement)
    {
        ActivityLogger::deleted($announcement, 'announcement', 'ঘোষণা ট্র্যাশে');
        $announcement->delete();
        $this->flush();
        return back()->with('success', 'ঘোষণা রিসাইকল বিনে পাঠানো হয়েছে।');
    }

    public function restore(Announcement $announcement)
    {
        $announcement->restore();
        $this->flush();
        return back()->with('success', 'ঘোষণা পুনরুদ্ধার হয়েছে।');
    }

    private function validateAnnouncement(Request $request, ?Announcement $item = null): array
    {
        $data = $request->validate([
            'title'      => ['required', 'string', 'min:4', 'max:191'],
            'slug'       => ['nullable', 'string', 'max:191', Rule::unique('announcements', 'slug')->ignore($item?->id)],
            'body'       => ['nullable', 'string', 'max:5000'],
            'link'       => ['nullable', 'url', 'max:500'],
            'link_text'  => ['nullable', 'string', 'max:60'],
            'type'       => ['required', Rule::in(array_keys(Announcement::TYPES))],
            'priority'   => ['nullable', 'integer', 'min:0', 'max:999'],
            'status'     => ['required', Rule::in(['draft', 'published', 'expired'])],
            'is_visible' => ['nullable', 'boolean'],
            'starts_at'  => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'image'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [], ['title' => 'ঘোষণার শিরোনাম']);

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploader->store($request->file('image'), 'announcements')->path;
        }
        $data['is_visible'] = $request->boolean('is_visible', true);

        return $data;
    }

    private function flush(): void { Cache::forget('site.announcements.active'); }
}
