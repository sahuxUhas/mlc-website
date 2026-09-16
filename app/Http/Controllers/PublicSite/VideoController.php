<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Video;

class VideoController extends Controller
{
    public function index()
    {
        $videos = Video::published()->ordered()->paginate(12)->withQueryString();

        return view('public.videos', compact('videos'));
    }

    public function show(string $slug)
    {
        $video = Video::published()->where('slug', $slug)->firstOrFail();

        $more = Video::published()->whereKeyNot($video->id)->ordered()->limit(8)->get();

        return view('public.video', compact('video', 'more'));
    }
}
