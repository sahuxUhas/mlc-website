<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

/** ট্যাগ ম্যানেজার */
class TagController extends Controller
{
    public function index(Request $request)
    {
        $query = Tag::withCount('posts')->orderBy('name');
        if ($q = trim((string) $request->query('q'))) { $query->where('name', 'like', '%'.$q.'%'); }

        return view('admin.categories.tags', ['tags' => $query->paginate(40)->withQueryString()]);
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();
        return back()->with('success', 'ট্যাগ মুছে ফেলা হয়েছে।');
    }
}
