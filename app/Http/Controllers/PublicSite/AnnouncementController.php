<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Announcement;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::active()->ordered()->paginate(12)->withQueryString();

        return view('public.announcements', compact('announcements'));
    }

    public function show(string $slug)
    {
        $announcement = Announcement::active()->where('slug', $slug)->firstOrFail();

        $more = Announcement::active()->whereKeyNot($announcement->id)->ordered()->limit(6)->get();

        return view('public.announcement', compact('announcement', 'more'));
    }
}
