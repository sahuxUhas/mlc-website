<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ActivityLogger;
use App\Services\MediaUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

/** ক্যাটাগরি ও সাব-ক্যাটাগরি — Add/Edit/Delete/Hide-Show/Order/Slug/Description/Image */
class CategoryController extends Controller
{
    public function __construct(private MediaUploader $uploader) {}

    public function index(Request $request)
    {
        $categories = Category::with('children')->root()->ordered()->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validateCategory($request);
        $category = Category::create($data);
        $this->flush();
        ActivityLogger::created($category, 'category', 'ক্যাটাগরি যোগ: '.$category->name);
        return back()->with('success', 'ক্যাটাগরি যোগ হয়েছে।');
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validateCategory($request, $category);
        $category->update($data);
        $this->flush();
        ActivityLogger::updated($category, 'category', 'ক্যাটাগরি হালনাগাদ: '.$category->name);
        return back()->with('success', 'ক্যাটাগরি হালনাগাদ হয়েছে।');
    }

    public function destroy(Request $request, Category $category)
    {
        if ($category->posts()->exists()) {
            return back()->with('error', 'এই ক্যাটাগরিতে সংবাদ আছে — আগে সেগুলো সরান।');
        }
        $category->delete();
        $this->flush();
        ActivityLogger::deleted($category, 'category', 'ক্যাটাগরি মুছে ফেলা হয়েছে: '.$category->name);
        return back()->with('success', 'ক্যাটাগরি মুছে ফেলা হয়েছে।');
    }

    public function toggle(Category $category)
    {
        $category->update(['is_visible' => ! $category->is_visible]);
        $this->flush();
        return back()->with('success', $category->is_visible ? 'ক্যাটাগরি দেখানো হচ্ছে।' : 'ক্যাটাগরি লুকানো হয়েছে।');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate(['order' => ['required', 'array'], 'order.*' => ['integer']]);
        foreach ($validated['order'] as $index => $id) {
            Category::where('id', $id)->update(['sort_order' => $index]);
        }
        $this->flush();
        return back()->with('success', 'ক্যাটাগরির ক্রম হালনাগাদ হয়েছে।');
    }

    private function validateCategory(Request $request, ?Category $category = null): array
    {
        $data = $request->validate([
            'parent_id'        => ['nullable', 'integer', 'exists:categories,id', Rule::notIn([$category?->id])],
            'name'             => ['required', 'string', 'min:2', 'max:100'],
            'slug'             => ['nullable', 'string', 'max:191', Rule::unique('categories', 'slug')->ignore($category?->id)],
            'icon'             => ['nullable', 'string', 'max:60'],
            'color'            => ['nullable', 'string', 'max:20'],
            'union_name'       => ['nullable', 'string', 'max:100'],
            'description'      => ['nullable', 'string', 'max:600'],
            'image'            => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_visible'       => ['nullable', 'boolean'],
            'show_on_home'     => ['nullable', 'boolean'],
            'show_in_menu'     => ['nullable', 'boolean'],
            'sort_order'       => ['nullable', 'integer', 'min:0'],
            'meta_title'       => ['nullable', 'string', 'max:191'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ], [], ['name' => 'ক্যাটাগরির নাম']);

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploader->store($request->file('image'), 'categories')->path;
        }

        $data['is_visible']   = $request->boolean('is_visible', true);
        $data['show_on_home'] = $request->boolean('show_on_home', true);
        $data['show_in_menu'] = $request->boolean('show_in_menu', true);

        return $data;
    }

    private function flush(): void
    {
        Cache::forget('site.nav.categories');
        Cache::forget('site.footer.categories');
        Cache::forget('site.home.data');
    }
}
