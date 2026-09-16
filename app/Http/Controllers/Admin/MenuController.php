<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Page;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

/** মেনু ম্যানেজার — Internal/External Link, Order, Enable-Disable, Mobile Bottom Nav */
class MenuController extends Controller
{
    public function index(Request $request)
    {
        $location = $request->query('location', 'main');

        return view('admin.menus.index', [
            'menus'      => Menu::with('children')->where('location', $location)->whereNull('parent_id')->orderBy('sort_order')->get(),
            'location'   => $location,
            'locations'  => Menu::LOCATIONS,
            'categories' => Category::root()->ordered()->get(),
            'pages'      => Page::orderBy('title')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $menu = Menu::create($this->validateMenu($request));
        $this->flush();
        ActivityLogger::created($menu, 'menus', 'মেনু যোগ: '.$menu->label);
        return back()->with('success', 'মেনু আইটেম যোগ হয়েছে।');
    }

    public function update(Request $request, Menu $menu)
    {
        $menu->update($this->validateMenu($request, $menu));
        $this->flush();
        ActivityLogger::updated($menu, 'menus', 'মেনু হালনাগাদ: '.$menu->label);
        return back()->with('success', 'মেনু আইটেম হালনাগাদ হয়েছে।');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();
        $this->flush();
        return back()->with('success', 'মেনু আইটেম মুছে ফেলা হয়েছে।');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate(['order' => ['required', 'array'], 'order.*' => ['integer']]);

        foreach ($validated['order'] as $index => $id) {
            Menu::where('id', $id)->update(['sort_order' => $index]);
        }

        $this->flush();
        return back()->with('success', 'মেনুর ক্রম হালনাগাদ হয়েছে।');
    }

    private function validateMenu(Request $request, ?Menu $menu = null): array
    {
        $data = $request->validate([
            'location'        => ['required', Rule::in(array_keys(Menu::LOCATIONS))],
            'parent_id'       => ['nullable', 'integer', 'exists:menus,id'],
            'label'           => ['required', 'string', 'min:1', 'max:60'],
            'icon'            => ['nullable', 'string', 'max:60'],
            'link_type'       => ['required', Rule::in(['internal', 'external', 'category', 'page'])],
            'url'             => ['nullable', 'string', 'max:500'],
            'reference_id'    => ['nullable', 'integer'],
            'is_enabled'      => ['nullable', 'boolean'],
            'open_in_new_tab' => ['nullable', 'boolean'],
            'sort_order'      => ['nullable', 'integer', 'min:0'],
        ], [], ['label' => 'মেনুর লেবেল']);

        $data['is_enabled']      = $request->boolean('is_enabled', true);
        $data['open_in_new_tab'] = $request->boolean('open_in_new_tab');

        return $data;
    }

    private function flush(): void
    {
        foreach (array_keys(Menu::LOCATIONS) as $location) {
            Cache::forget('site.menus.'.$location);
        }
    }
}
