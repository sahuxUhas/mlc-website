<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Post;

/** /latest-news — সর্বশেষ সংবাদ, পেজিনেশন সহ */
class LatestNewsController extends Controller
{
    public function index()
    {
        $posts = Post::published()
            ->with(['category:id,name,slug,color', 'reporter:id,name,slug'])
            ->latestFirst()
            ->paginate(15)
            ->withQueryString();

        return view('public.latest', compact('posts'));
    }
}
