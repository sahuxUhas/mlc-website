<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Support\ViewCounter;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function index()
    {
        $videos = Video::published()->ordered()->paginate(8)->withQueryString();

        return view('public.videos', compact('videos'));
    }

    public function show(string $slug)
    {
        $video = Video::published()->where('slug', $slug)->firstOrFail();

        $more = Video::published()->whereKeyNot($video->id)->ordered()->limit(8)->get();

        return view('public.video', compact('video', 'more'));
    }

    /** গ্যালারিতে প্লে/দেখা — বাস্তব ভিউ, এক ভিজিটর একবার। Raw URL ফেরত দেয় না। */
    public function recordView(Request $request, string $slug)
    {
        $video = Video::published()->where('slug', $slug)->firstOrFail();
        $views = (int) $video->views;

        if (config('views.count_bots', false) || ! ViewCounter::isBot($request->userAgent())) {
            app(ViewCounter::class)->record($request, $video);
            $views = (int) $video->fresh()->views;
        }

        return response()->json([
            'slug'  => $video->slug,
            'views' => $views,
            'label' => bn_count($views),
        ]);
    }
}
