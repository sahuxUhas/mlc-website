<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $categoryId = $request->integer('category') ?: null;
        $sort = $request->query('sort', 'latest');

        $query = Post::published()->with(['category:id,name,slug,color', 'reporter:id,name,slug']);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($q !== '') {
            $query->search($q);
        }

        match ($sort) {
            'popular' => $query->popular(),
            'oldest'  => $query->orderBy('published_at'),
            default   => $query->latestFirst(),
        };

        $posts = $query->paginate(15)->withQueryString();

        return view('public.search', compact('posts', 'q', 'sort', 'categoryId'));
    }

    /** হেডার সার্চের লাইভ সাজেশন (AJAX) */
    public function suggest(Request $request)
    {
        $validated = $request->validate(['q' => ['nullable', 'string', 'min:2', 'max:80']]);
        $q = trim((string) ($validated['q'] ?? ''));

        if ($q === '') {
            return response()->json(['results' => []]);
        }

        $results = Post::published()
            ->search($q)
            ->latestFirst()
            ->limit(8)
            ->get(['id', 'slug', 'title', 'featured_image', 'published_at', 'views'])
            ->map(fn (Post $p) => [
                'title' => $p->title,
                'url'   => route('news.show', $p->slug),
                'image' => $p->featured_image ? asset($p->featured_image) : null,
                'date'  => bn_date($p->published_at),
                'views' => bn_num($p->views),
            ]);

        return response()->json(['results' => $results]);
    }
}
