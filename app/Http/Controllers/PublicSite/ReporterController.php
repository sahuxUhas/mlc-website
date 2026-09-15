<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Reporter;

class ReporterController extends Controller
{
    public function index()
    {
        $reporters = Reporter::visible()->ordered()->paginate(24);

        return view('public.reporters', compact('reporters'));
    }

    public function show(string $slug)
    {
        $reporter = Reporter::visible()->where('slug', $slug)->firstOrFail();

        $posts = Post::published()
            ->with('category:id,name,slug,color')
            ->where('reporter_id', $reporter->id)
            ->latestFirst()
            ->paginate(12);

        return view('public.reporter', compact('reporter', 'posts'));
    }
}
