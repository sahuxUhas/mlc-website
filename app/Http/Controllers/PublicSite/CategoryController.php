<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;

class CategoryController extends Controller
{
    public function show(string $slug)
    {
        $category = Category::visible()->where('slug', $slug)->firstOrFail();

        $posts = Post::published()
            ->with(['category:id,name,slug,color', 'reporter:id,name,slug'])
            ->forCategory($category)
            ->latestFirst()
            ->paginate(15)
            ->withQueryString();

        $subcategories = $category->children()->visible()->get();

        return view('public.category', compact('category', 'posts', 'subcategories'));
    }
}
