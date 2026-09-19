<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\AlbumPhoto;
use App\Services\ActivityLogger;
use App\Services\MediaUploader;
use Illuminate\Http\Request;

/** ফটো গ্যালারি / অ্যালবাম — Create/Edit/Delete, Multiple Photos, Reorder, Cover, Hide-Show */
class AlbumController extends Controller
{
    public function __construct(private MediaUploader $uploader) {}

    public function index()
    {
        return view('admin.albums.index', [
            'albums' => Album::withCount('photos')->ordered()->paginate(15),
        ]);
    }

    public function store(Request $request)
    {
        $album = Album::create($this->validateAlbum($request));
        $this->addPhotos($request, $album);
        ActivityLogger::created($album, 'album', 'অ্যালবাম তৈরি: '.$album->title);
        return redirect()->route('admin.albums.edit', $album)->with('success', 'অ্যালবাম তৈরি হয়েছে।');
    }

    public function edit(Album $album)
    {
        return view('admin.albums.edit', ['album' => $album->load('photos')]);
    }

    public function update(Request $request, Album $album)
    {
        $album->update($this->validateAlbum($request, $album));
        $this->addPhotos($request, $album);

        // কভার নির্ধারণ — ফর্ম থেকে কখনো raw URL আসে না, শুধু ফটোর id
        // (ছবি ImgBB তে থাকলেও path সার্ভার-সাইডেই বসে, তাই লিংক কোথাও প্রকাশ পায় না)
        if ($request->filled('cover_photo_id')) {
            $photo = $album->photos()->whereKey((int) $request->input('cover_photo_id'))->first();

            if ($photo) {
                $album->update(['cover_image' => $photo->path]);
            }
        }

        ActivityLogger::updated($album, 'album', 'অ্যালবাম হালনাগাদ: '.$album->title);
        return back()->with('success', 'অ্যালবাম হালনাগাদ হয়েছে।');
    }

    public function destroy(Album $album)
    {
        ActivityLogger::deleted($album, 'album', 'অ্যালবাম ট্র্যাশে: '.$album->title);
        $album->delete();
        return back()->with('success', 'অ্যালবাম রিসাইকল বিনে পাঠানো হয়েছে।');
    }

    /** একাধিক ফটো যোগ */
    public function addPhotos(Request $request, Album $album)
    {
        if (! $request->hasFile('photos')) { return; }

        $request->validate(['photos' => ['array', 'max:40'], 'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:6144']]);

        $next = (int) $album->photos()->max('sort_order') + 1;

        foreach ($this->uploader->storeMany($request->file('photos'), 'albums/'.$album->id) as $media) {
            $album->photos()->create(['path' => $media->path, 'sort_order' => $next++]);
        }

        $album->update(['photos_count' => $album->photos()->count()]);

        // কভার না থাকলে প্রথম ছবিই কভার হবে
        if (empty($album->cover_image)) {
            $album->update(['cover_image' => $album->photos()->first()?->path]);
        }

        if ($request->expectsJson()) { return response()->json(['success' => true]); }
        return back()->with('success', 'ফটো যোগ হয়েছে।');
    }

    /** ফটোর ক্রম পরিবর্তন (drag & drop) */
    public function reorderPhotos(Request $request, Album $album)
    {
        $validated = $request->validate(['order' => ['required', 'array'], 'order.*' => ['integer']]);

        foreach ($validated['order'] as $index => $photoId) {
            $album->photos()->where('id', $photoId)->update(['sort_order' => $index]);
        }

        return back()->with('success', 'ফটোর ক্রম হালনাগাদ হয়েছে।');
    }

    public function destroyPhoto(Album $album, AlbumPhoto $photo)
    {
        abort_unless($photo->album_id === $album->id, 404);

        // remote (ImgBB) ছবির ক্ষেত্রে লোকাল unlink করার কিছু নেই — শুধু DB ফাইল মুছে ফেলা হয়
        if (! preg_match('~^https?://~i', (string) $photo->path)) {
            $file = public_path('uploads/'.ltrim((string) $photo->path, '/'));

            if (is_file($file)) {
                @unlink($file);
            }
        }

        $wasCover = $album->cover_image === $photo->path;

        $photo->delete();

        $album->update([
            'photos_count' => $album->photos()->count(),
            'cover_image'  => $wasCover ? $album->photos()->first()?->path : $album->cover_image,
        ]);

        return back()->with('success', 'ফটো মুছে ফেলা হয়েছে।');
    }

    private function validateAlbum(Request $request, ?Album $album = null): array
    {
        $data = $request->validate([
            'title'          => ['required', 'string', 'min:3', 'max:191'],
            'slug'           => ['nullable', 'string', 'max:191', \Illuminate\Validation\Rule::unique('albums', 'slug')->ignore($album?->id)],
            'description'    => ['nullable', 'string', 'max:1000'],
            'is_visible'     => ['nullable', 'boolean'],
            'sort_order'     => ['nullable', 'integer'],
            'published_at'   => ['nullable', 'date'],
            'cover_image'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'meta_title'     => ['nullable', 'string', 'max:191'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ], [], ['title' => 'অ্যালবামের নাম']);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->uploader->store($request->file('cover_image'), 'albums/covers')->path;
        }
        $data['is_visible']   = $request->boolean('is_visible', true);
        $data['published_at'] = $data['published_at'] ?? now();

        return $data;
    }
}
