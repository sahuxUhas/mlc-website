<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\NewsReport;
use App\Services\MediaUploader;
use Illuminate\Http\Request;

/** পাঠকের প্রতিবেদন জমা (/submit-report) */
class ReportSubmitController extends Controller
{
    public function store(Request $request, MediaUploader $uploader)
    {
        $validated = $request->validate([
            'reporter_name'  => ['required', 'string', 'min:2', 'max:80'],
            'reporter_phone' => ['nullable', 'string', 'max:30'],
            'reporter_email' => ['nullable', 'email', 'max:190'],
            'location'       => ['nullable', 'string', 'max:120'],
            'title'          => ['required', 'string', 'min:5', 'max:190'],
            'details'        => ['required', 'string', 'min:20', 'max:8000'],
            'attachment'     => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,mp4', 'max:10240'],
        ], [], ['reporter_name' => 'আপনার নাম', 'title' => 'শিরোনাম', 'details' => 'বিবরণ']);

        $path = null;
        if ($request->hasFile('attachment')) {
            $media = $uploader->store($request->file('attachment'), 'reports');
            $path  = $media->path;
        }

        NewsReport::create($validated + ['attachment' => $path]);

        return back()->with('success', 'আপনার প্রতিবেদন জমা হয়েছে। নিউজরুম যাচাই করে প্রকাশ করবে।');
    }
}
