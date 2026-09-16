<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Epaper;
use Illuminate\Support\Facades\Storage;

class EpaperController extends Controller
{
    public function index()
    {
        $epapers = Epaper::visible()->orderByDesc('issue_date')->paginate(12);

        return view('public.epaper', compact('epapers'));
    }

    public function download(Epaper $epaper)
    {
        abort_unless($epaper->is_visible, 404);

        $disk = Storage::disk('uploads');

        abort_unless($disk->exists($epaper->file_path), 404);

        return $disk->download($epaper->file_path, basename($epaper->file_path));
    }
}
