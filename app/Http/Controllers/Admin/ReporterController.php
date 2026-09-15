<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reporter;
use App\Services\ActivityLogger;
use App\Services\MediaUploader;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** রিপোর্টার ও লেখক ব্যবস্থাপনা */
class ReporterController extends Controller
{
    public function __construct(private MediaUploader $uploader) {}

    public function index()
    {
        $reporters = Reporter::withCount(['posts as published_count' => fn ($q) => $q->where('status', 'published')])
            ->ordered()->paginate(20);
        return view('admin.reporters.index', compact('reporters'));
    }

    public function store(Request $request)
    {
        $reporter = Reporter::create($this->validateReporter($request));
        ActivityLogger::created($reporter, 'reporter', 'রিপোর্টার যোগ: '.$reporter->name);
        return back()->with('success', 'রিপোর্টার যোগ হয়েছে।');
    }

    public function update(Request $request, Reporter $reporter)
    {
        $reporter->update($this->validateReporter($request, $reporter));
        ActivityLogger::updated($reporter, 'reporter', 'রিপোর্টার হালনাগাদ: '.$reporter->name);
        return back()->with('success', 'রিপোর্টার হালনাগাদ হয়েছে।');
    }

    public function destroy(Reporter $reporter)
    {
        ActivityLogger::deleted($reporter, 'reporter', 'রিপোর্টার মুছে ফেলা: '.$reporter->name);
        $reporter->delete();
        return back()->with('success', 'রিপোর্টার ট্র্যাশে পাঠানো হয়েছে।');
    }

    private function validateReporter(Request $request, ?Reporter $reporter = null): array
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'min:2', 'max:100'],
            'slug'        => ['nullable', 'string', 'max:191', Rule::unique('reporters', 'slug')->ignore($reporter?->id)],
            'designation' => ['nullable', 'string', 'max:120'],
            'bio'         => ['nullable', 'string', 'max:2000'],
            'email'       => ['nullable', 'email', 'max:190'],
            'phone'       => ['nullable', 'string', 'max:30'],
            'address'     => ['nullable', 'string', 'max:190'],
            'facebook'    => ['nullable', 'url', 'max:300'],
            'photo'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_visible'  => ['nullable', 'boolean'],
            'sort_order'  => ['nullable', 'integer'],
        ], [], ['name' => 'রিপোর্টারের নাম']);

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->uploader->store($request->file('photo'), 'reporters')->path;
        }
        $data['is_visible'] = $request->boolean('is_visible', true);

        return $data;
    }
}
