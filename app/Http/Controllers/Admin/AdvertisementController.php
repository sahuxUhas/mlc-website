<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Services\ActivityLogger;
use App\Services\MediaUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

/** বিজ্ঞাপন ম্যানেজার — Position, Image/HTML, Link, Start/End Date, Priority, Enable-Disable */
class AdvertisementController extends Controller
{
    public function __construct(private MediaUploader $uploader) {}

    public function index(Request $request)
    {
        $query = Advertisement::withTrashed()->latest();
        if ($position = $request->query('position')) { $query->where('position', $position); }

        return view('admin.ads.index', [
            'ads'       => $query->paginate(15)->withQueryString(),
            'positions' => Advertisement::POSITIONS,
        ]);
    }

    public function store(Request $request)
    {
        $ad = Advertisement::create($this->validateAd($request));
        $this->flush();
        ActivityLogger::created($ad, 'ads', 'বিজ্ঞাপন যোগ: '.$ad->title);
        return back()->with('success', 'বিজ্ঞাপন যোগ হয়েছে।');
    }

    public function update(Request $request, Advertisement $advertisement)
    {
        $advertisement->update($this->validateAd($request, $advertisement));
        $this->flush();
        ActivityLogger::updated($advertisement, 'ads', 'বিজ্ঞাপন হালনাগাদ: '.$advertisement->title);
        return back()->with('success', 'বিজ্ঞাপন হালনাগাদ হয়েছে।');
    }

    public function destroy(Advertisement $advertisement)
    {
        ActivityLogger::deleted($advertisement, 'ads', 'বিজ্ঞাপন ট্র্যাশে');
        $advertisement->delete();
        $this->flush();
        return back()->with('success', 'বিজ্ঞাপন রিসাইকল বিনে পাঠানো হয়েছে।');
    }

    public function toggle(Advertisement $advertisement)
    {
        $advertisement->update(['is_enabled' => ! $advertisement->is_enabled]);
        $this->flush();
        return back()->with('success', $advertisement->is_enabled ? 'বিজ্ঞাপন চালু।' : 'বিজ্ঞাপন বন্ধ।');
    }

    private function validateAd(Request $request, ?Advertisement $ad = null): array
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'min:2', 'max:191'],
            'position'    => ['required', Rule::in(array_keys(Advertisement::POSITIONS))],
            'type'        => ['required', Rule::in(['image', 'html'])],
            'link'        => ['nullable', 'url', 'max:500'],
            'link_target' => ['nullable', Rule::in(['_blank', '_self'])],
            'is_enabled'  => ['nullable', 'boolean'],
            'priority'    => ['nullable', 'integer', 'min:0', 'max:999'],
            'starts_at'   => ['nullable', 'date'],
            'ends_at'     => ['nullable', 'date', 'after_or_equal:starts_at'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'html_code'   => ['nullable', 'string', 'max:8000'],
        ], [], ['title' => 'বিজ্ঞাপনের নাম']);

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploader->store($request->file('image'), 'ads')->path;
        }
        $data['is_enabled']  = $request->boolean('is_enabled', true);
        $data['link_target'] = $data['link_target'] ?? '_blank';

        // টাইপ অনুযায়ী অপ্রয়োজনীয় ফিল্ড বাদ
        if (($data['type'] ?? '') === 'html') { unset($data['image']); } else { unset($data['html_code']); }

        return $data;
    }

    private function flush(): void
    {
        foreach (array_keys(Advertisement::POSITIONS) as $position) {
            Cache::forget('site.ads.'.$position);
        }
    }
}
