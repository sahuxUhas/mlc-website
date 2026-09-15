<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Album;

class GalleryController extends Controller
{
    public function index()
    {
        $albums = Album::visible()->with('photos')->ordered()->paginate(12);

        return view('public.gallery', compact('albums'));
    }

    public function show(string $slug)
    {
        $album = Album::visible()->with('photos')->where('slug', $slug)->firstOrFail();

        $more = Album::visible()->whereKeyNot($album->id)->ordered()->limit(6)->get();

        return view('public.album', compact('album', 'more'));
    }
}
