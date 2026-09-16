<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Tag;

class TagController extends Controller
{
    public function show(string $slug)
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        $posts = $tag->posts()
            ->published()
            ->with(['category:id,name,slug,color', 'reporter:id,name,slug'])
            ->latestFirst()
            ->paginate(15);

        return view('public.tag', compact('tag', 'posts'));
    }
}
