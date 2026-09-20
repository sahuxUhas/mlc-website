<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\ActivityLogger;
use App\Services\MediaUploader;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** স্ট্যাটিক পেজ ব্যবস্থাপনা — About/Contact/Privacy/Terms/Editorial/Advertise/Custom */
class PageController extends Controller
{
    public function __construct(private MediaUploader $uploader) {}

    public function index()
    {
        return view('admin.pages.index', ['pages' => Page::withTrashed()->orderBy('sort_order')->latest()->paginate(20)]);
    }

    public function create()
    {
        return view('admin.pages.form', ['page' => new Page(['is_visible' => true, 'template' => 'default'])]);
    }

    public function store(Request $request)
    {
        $page = Page::create($this->validatePage($request));
        ActivityLogger::created($page, 'pages', 'স্ট্যাটিক পেজ তৈরি: '.$page->title);
        return redirect()->route('admin.pages.edit', $page)->with('success', 'পেজ তৈরি হয়েছে।');
    }

    public function edit(Page $page)
    {
        return view('admin.pages.form', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $page->update($this->validatePage($request, $page));
        ActivityLogger::updated($page, 'pages', 'পেজ হালনাগাদ: '.$page->title);
        return back()->with('success', 'পেজ হালনাগাদ হয়েছে।');
    }

    public function destroy(Page $page)
    {
        ActivityLogger::deleted($page, 'pages', 'পেজ ট্র্যাশে: '.$page->title);
        $page->delete();
        return back()->with('success', 'পেজ রিসাইকল বিনে পাঠানো হয়েছে।');
    }

    private function validatePage(Request $request, ?Page $page = null): array
    {
        $data = $request->validate([
            'title'            => ['required', 'string', 'min:2', 'max:191'],
            'slug'             => ['nullable', 'string', 'max:191', Rule::unique('pages', 'slug')->ignore($page?->id)],
            'content'          => ['nullable', 'string'],
            'excerpt'          => ['nullable', 'string', 'max:600'],
            'template'         => ['required', 'string', 'max:30'],
            'is_visible'       => ['nullable', 'boolean'],
            'show_in_footer'   => ['nullable', 'boolean'],
            'sort_order'       => ['nullable', 'integer'],
            'featured_image'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'og_image'         => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_featured_image' => ['nullable', 'boolean'],
            'remove_og_image'  => ['nullable', 'boolean'],
            'meta_title'       => ['nullable', 'string', 'max:191'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords'    => ['nullable', 'string', 'max:400'],
            'canonical_url'    => ['nullable', 'url', 'max:500'],
        ], [], ['title' => 'পেজের শিরোনাম']);

        // ছবি কেবল সরাসরি আপলোড থেকেই আসে (URL/লিংক ইনপুট নেই) —
        // হোস্টিং API (ImgBB) তে যায়, DB-তে শুধু রেফারেন্স বসে।
        if ($request->boolean('remove_featured_image')) {
            $data['featured_image'] = null;
        } elseif ($request->hasFile('featured_image')) {
            $data['featured_image'] = $this->uploader->store($request->file('featured_image'), 'pages')->path;
        }

        if ($request->boolean('remove_og_image')) {
            $data['og_image'] = null;
        } elseif ($request->hasFile('og_image')) {
            $data['og_image'] = $this->uploader->store($request->file('og_image'), 'pages/og')->path;
        }

        unset($data['remove_featured_image'], $data['remove_og_image']);
        $data['is_visible']     = $request->boolean('is_visible', true);
        $data['show_in_footer'] = $request->boolean('show_in_footer');

        return $data;
    }
}
