<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\Reporter;
use App\Models\Tag;
use App\Services\ActivityLogger;
use App\Services\MediaUploader;
use App\Support\ContentRenderer;
use App\Support\HtmlSanitizer;
use App\Support\PublicCache;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * সংবাদ ব্যবস্থাপনা — Add/Edit/Delete/Restore/Draft/Pending/Publish/Schedule/
 * Archive/Search/Filter/Sort/Pagination + Featured Image + একাধিক ছবি (Reorder/
 * Remove/Set Featured) + Tags + Reporter + SEO + Preview।
 *
 * ছবি: কখনো MySQL-এ BLOB নয় — ImgBB API তে আপলোড হয়ে URL/ID রেফারেন্স সংরক্ষিত
 * হয় (Media টেবিল), আর সাইটে signed proxy URL (`/img/…`) দিয়ে দেখানো হয়।
 */
class NewsController extends Controller
{
    public function __construct(private MediaUploader $uploader)
    {
    }

    /* ============================ তালিকা ============================ */

    public function index(Request $request)
    {
        $user = $request->user();

        $query = Post::withCount('images')
            ->with(['category:id,name,slug', 'subcategory:id,name', 'reporter:id,name', 'featuredMedia:id,path,thumb_url']);

        // রিপোর্টার শুধু নিজের সংবাদ দেখবে
        if ($user->role === 'reporter') {
            $query->where('author_id', $user->id);
        }

        // স্ট্যাটাস ফিল্টার (ট্যাব)
        $status = (string) $request->query('status', 'all');
        if ($status === 'trash') {
            $query->onlyTrashed();
        } elseif (array_key_exists($status, Post::STATUSES)) {
            $query->where('status', $status);
        } else {
            $status = 'all';
        }

        // ক্যাটাগরি ফিল্টার
        if ($categoryId = $request->integer('category')) {
            $query->where('category_id', $categoryId);
        }

        // রিপোর্টার ফিল্টার
        if ($reporterId = $request->integer('reporter')) {
            $query->where('reporter_id', $reporterId);
        }

        // ফিচার্ড / ব্রেকিং ফিল্টার
        if ($request->filled('flag')) {
            $query->where(match ($request->query('flag')) {
                'featured' => 'is_featured',
                'breaking' => 'is_breaking',
                default    => 'id',
            }, true);
        }

        // সার্চ
        if ($q = trim((string) $request->query('q'))) {
            $query->search($q);
        }

        // তারিখ পরিসর
        if ($from = $request->date('from')) {
            $query->whereDate('published_at', '>=', $from);
        }
        if ($to = $request->date('to')) {
            $query->whereDate('published_at', '<=', $to);
        }

        // সর্টিং
        match ($request->query('sort', 'newest')) {
            'oldest'    => $query->orderBy('published_at'),
            'views'     => $query->orderByDesc('views'),
            'title'     => $query->orderBy('title'),
            'status'    => $query->orderBy('status'),
            default     => $query->latest(),
        };

        $perPage = min(100, max(5, $request->integer('per_page', 20)));

        $posts = $query->paginate($perPage)->withQueryString();

        // ট্যাব কাউন্ট
        $counts = [
            'all'       => Post::count(),
            'published' => Post::where('status', 'published')->count(),
            'draft'     => Post::where('status', 'draft')->count(),
            'pending'   => Post::where('status', 'pending')->count(),
            'scheduled' => Post::where('status', 'scheduled')->count(),
            'archived'  => Post::where('status', 'archived')->count(),
            'trash'     => Post::onlyTrashed()->count(),
        ];

        return view('admin.news.index', [
            'posts'       => $posts,
            'counts'      => $counts,
            'status'      => $status,
            'categories'  => Category::root()->ordered()->get(),
            'reporters'   => Reporter::ordered()->get(),
            'filters'     => $request->only(['q', 'category', 'reporter', 'sort', 'flag', 'from', 'to', 'status']),
        ]);
    }

    /* ========================== Create / Edit ========================== */

    public function create()
    {
        return view('admin.news.form', $this->formData(
            new Post(['status' => 'draft', 'allow_comments' => true, 'published_at' => now()])
        ));
    }

    public function edit(Post $post)
    {
        $this->authorizeEdit($post);

        return view('admin.news.form', $this->formData($post->load(['images.media', 'tags', 'featuredMedia'])));
    }

    /**
     * সংবাদ প্রাকদর্শন — সেভ না করেই পাবলিক আর্টিকেল কেমন দেখাবে (নতুন ট্যাবে)।
     * শুধু অ্যাডমিন/অনুমতিপ্রাপ্ত ব্যক্তি দেখতে পারেন, ভিউ কাউন্ট বাড়ে না।
     */
    public function preview(Post $post)
    {
        $this->authorizeEdit($post);

        $post->load([
            'category', 'subcategory:id,name,slug', 'reporter', 'author:id,name,avatar',
            'images.media', 'tags', 'featuredMedia', 'ogMedia',
            'comments' => fn ($q) => $q->approved()->topLevel()->with('replies')->newest()->limit(50),
        ]);

        $schema = $this->schemaFor($post);

        return view('public.article', [
            'post'        => $post,
            'related'     => collect(),
            'latest'      => collect(),
            'schema'      => $schema,
            'previewMode' => true,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedPayload($request, null);
        $user = $request->user();

        return DB::transaction(function () use ($request, $data, $user) {
            $post = new Post($data);
            $post->author_id = $user->id;
            $post->views = 0;
            $post->save();

            $this->applyFeaturedMedia($request, $post);
            $this->applyGalleryImages($request, $post);
            $this->handleTags($post, $request->input('tags'));
            $this->syncBreakingFlag($post);
            $this->applyOrder($request, $post);

            ActivityLogger::created($post, 'news', 'নতুন সংবাদ তৈরি: '.$post->title);

            return redirect()->route('admin.news.edit', $post)
                ->with('success', $this->statusMessage($post, true));
        });
    }

    public function update(Request $request, Post $post)
    {
        $this->authorizeEdit($post);

        $data = $this->validatedPayload($request, $post);

        return DB::transaction(function () use ($request, $post, $data) {
            $post->fill($data);
            $changed = array_keys($post->getDirty());
            $post->save();

            $this->applyFeaturedMedia($request, $post);
            $this->applyGalleryImages($request, $post);
            $this->handleTags($post, $request->input('tags'));
            $this->syncBreakingFlag($post);
            $this->applyOrder($request, $post);

            ActivityLogger::updated($post, 'news', 'সংবাদ হালনাগাদ: '.$post->title, [
                'fields' => $changed,
            ]);

            // পাবলিক সাইটে সঙ্গে সঙ্গে পরিবর্তন দেখা যাবে (ক্যাশ রিফ্রেশ)
            PublicCache::flushForContent();

            return redirect()->route('admin.news.edit', $post)
                ->with('success', $this->statusMessage($post, false));
        });
    }

    /* ============================ স্ট্যাটাস ============================ */

    /** স্ট্যাটাস দ্রুত পরিবর্তন (draft/pending/publish/unpublish/schedule/archive) */
    public function changeStatus(Request $request, Post $post, string $status)
    {
        abort_unless(array_key_exists($status, Post::STATUSES), 404);
        $this->authorizeEdit($post);

        // প্রকাশ করার অনুমতি না থাকলে ব্লক
        if (in_array($status, ['published', 'scheduled'], true) && ! $request->user()->can_manage('news.publish')) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'প্রকাশের অনুমতি আপনার নেই — এডিটরের অনুমোদন প্রয়োজন।'], 403)
                : back()->with('error', 'প্রকাশের অনুমতি আপনার নেই — এডিটরের অনুমোদন প্রয়োজন।');
        }

        $attributes = ['status' => $status];

        if ($status === 'published') {
            $attributes['published_at'] = $post->published_at ?? now();
        }

        if ($status === 'scheduled') {
            $validated = $request->validate(
                ['scheduled_at' => ['required', 'date', 'after:now']],
                [],
                ['scheduled_at' => 'প্রকাশের সময়']
            );
            $attributes['scheduled_at'] = $validated['scheduled_at'];
        }

        $post->update($attributes);

        ActivityLogger::log($status, 'news', 'স্ট্যাটাস পরিবর্তন → '.(Post::STATUSES[$status]).': '.$post->title, $post);

        $message = 'স্ট্যাটাস পরিবর্তিত হয়েছে → '.Post::STATUSES[$status];

        return $request->expectsJson()
            ? response()->json(['success' => true, 'status' => $status, 'message' => $message])
            : back()->with('success', $message);
    }

    /* ============================ ডিলিট ============================ */

    /** ট্র্যাশে পাঠানো (soft delete) */
    public function destroy(Request $request, Post $post)
    {
        abort_unless($request->user()->can_manage('news.delete'), 403);

        $post->delete();
        ActivityLogger::deleted($post, 'news', 'সংবাদ ট্র্যাশে পাঠানো হয়েছে: '.$post->title);

        return back()->with('success', 'সংবাদটি রিসাইকল বিনে পাঠানো হয়েছে।');
    }

    /** বাল্ক অ্যাকশন — প্রকাশ/আনপাবলিশ/ট্র্যাশ/স্থায়ীভাবে মুছে ফেলা */
    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['publish', 'unpublish', 'draft', 'archive', 'trash', 'restore', 'delete'])],
            'ids'    => ['required', 'array', 'min:1', 'max:200'],
            'ids.*'  => ['integer'],
        ]);

        $ids = $validated['ids'];
        $user = $request->user();

        $query = match ($validated['action']) {
            'restore' => Post::onlyTrashed()->whereIn('id', $ids),
            'delete'  => Post::onlyTrashed()->whereIn('id', $ids),
            default   => Post::whereIn('id', $ids),
        };

        // রিপোর্টার শুধু নিজের সংবাদে বাল্ক অ্যাকশন চালাতে পারবে
        if ($user->role === 'reporter') {
            $query->where('author_id', $user->id);
        }

        if ($validated['action'] === 'publish' && ! $user->can_manage('news.publish')) {
            abort(403, 'প্রকাশের অনুমতি আপনার নেই।');
        }

        if (in_array($validated['action'], ['delete', 'trash'], true) && ! $user->can_manage('news.delete')) {
            abort(403, 'মুছে ফেলার অনুমতি আপনার নেই।');
        }

        $count = match ($validated['action']) {
            'publish'   => $query->update(['status' => 'published', 'published_at' => DB::raw('COALESCE(published_at, NOW())')]),
            'unpublish' => $query->update(['status' => 'draft']),
            'draft'     => $query->update(['status' => 'draft']),
            'archive'   => $query->update(['status' => 'archived']),
            'trash'     => tap($models = $query->get(), fn ($models) => $models->each->delete())->count(),
            'restore'   => tap($models = $query->get(), fn ($models) => $models->each->restore())->count(),
            'delete'    => tap($query->get(), fn ($models) => $models->each->forceDelete())->count(),
        };

        PublicCache::flushForContent();

        ActivityLogger::log('bulk_'.$validated['action'], 'news', 'বাল্ক অ্যাকশন: '.bn_num((int) $count).'টি সংবাদ');

        return back()->with('success', bn_num((int) $count).'টি সংবাদে প্রয়োগ হয়েছে।');
    }

    /* ============================ ছবি ============================ */

    /** ছবির ক্রম পরিবর্তন (drag & drop / তীর বাটন) — AJAX ও সাধারণ ফর্ম দুটোই */
    public function reorderImages(Request $request, Post $post)
    {
        $this->authorizeEdit($post);

        // দুই ফরম্যাটই গ্রহণযোগ্য: order[]=1&order[]=2 (array) অথবা order=1,2 (comma string)
        $raw = $request->input('order');

        if (is_string($raw)) {
            $raw = array_values(array_filter(explode(',', $raw), fn ($v) => $v !== ''));
        }

        $validated = validator(['order' => $raw], ['order' => ['required', 'array', 'min:1'], 'order.*' => ['integer']])->validate();
        $validated['order'] = array_map('intval', $validated['order']);

        DB::transaction(function () use ($post, $validated) {
            foreach ($validated['order'] as $index => $imageId) {
                // অন্য সংবাদের ছবি যাতে সরানো না যায়
                $post->images()->where('id', $imageId)->update(['sort_order' => $index]);
            }
        });

        ActivityLogger::log('reorder_images', 'news', 'ছবির ক্রম পরিবর্তন: '.$post->title, $post);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'ছবির ক্রম হালনাগাদ হয়েছে।']);
        }

        return back()->with('success', 'ছবির ক্রম হালনাগাদ হয়েছে।');
    }

    /**
     * এডিট পেজ থেকে সরাসরি নতুন ছবি আপলোড (ফর্ম রিসাবমিট ছাড়াই গ্যালারিতে যোগ হয়)।
     * ImgBB আপলোড ব্যর্থ হলে সেটি স্পষ্ট বার্তা আকারে ফেরত আসে (বাকি ছবি ঠিকভাবে যুক্ত হয়)।
     */
    public function addImages(Request $request, Post $post)
    {
        $this->authorizeEdit($post);

        $request->validate([
            'images'   => ['required', 'array', 'min:1', 'max:'.max(1, (int) config('images.max_files', 20))],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:'.(int) config('images.max_kb', 4096)],
        ], [], ['images' => 'ছবি']);

        $next = (int) $post->images()->max('sort_order') + 1;
        $result = $this->uploader->storeManyCollect(
            $request->file('images'),
            'news/gallery',
            MediaUploader::NEWS_MIMES
        );

        $added = [];

        foreach ($result['saved'] as $media) {
            $image = $post->images()->create([
                'media_id'   => $media->id,
                'path'       => $media->path,
                'alt_text'   => $media->file_name,
                'sort_order' => $next++,
            ]);

            $added[] = $this->imagePayload($image, $post);
        }

        ActivityLogger::log('add_images', 'news', bn_num(count($added)).'টি ছবি যোগ: '.$post->title, $post);

        $message = count($added) > 0
            ? bn_num(count($added)).'টি ছবি গ্যালারিতে যোগ হয়েছে।'
            : 'কোনো ছবি যোগ করা যায়নি।';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => count($added) > 0,
                'images'  => $added,
                'errors'  => $result['errors'],
                'message' => $message,
            ], count($added) > 0 ? 200 : 422);
        }

        return back()
            ->with(count($added) > 0 ? 'success' : 'error', $message)
            ->with('upload_errors', $result['errors']);
    }

    /**
     * ভেতরের ছবি (Content Editor) আপলোড — DB-তে শুধু মিডিয়া রেফারেন্স যায়,
     * কনটেন্টে বসে `{{media:ID}}` শর্টকোড ⇒ raw URL কোথাও সংরক্ষিত/দেখানো হয় না।
     */
    public function uploadEditorMedia(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'images'   => ['required', 'array', 'min:1', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:'.(int) config('images.max_kb', 4096)],
            'post_id'  => ['nullable', 'integer', 'exists:posts,id'],
        ], [], ['images' => 'ছবি']);

        $post = ! empty($validated['post_id']) ? Post::withTrashed()->find($validated['post_id']) : null;

        if ($post) {
            $this->authorizeEdit($post);
        } elseif (! $user->can_manage('news.create')) {
            abort(403, 'সংবাদ তৈরির অনুমতি আপনার নেই।');
        }

        $result = $this->uploader->storeManyCollect($request->file('images'), 'news/content', MediaUploader::NEWS_MIMES);

        $items = [];
        foreach ($result['saved'] as $media) {
            $media->update(['used_in' => 'post_content', 'used_id' => $post?->id]);
            $items[] = $this->mediaPayload($media);
        }

        return response()->json([
            'success' => count($items) > 0,
            'images'  => $items,
            'errors'  => $result['errors'],
            'message' => count($items) > 0
                ? bn_num(count($items)).'টি ছবি লেখায় যোগ করা হয়েছে।'
                : 'ছবি যোগ করা যায়নি।',
        ], count($items) > 0 ? 200 : 422);
    }

    /** গ্যালারির ছবির ক্যাপশন/ক্রেডিট/Alt এডিট */
    public function updateImage(Request $request, Post $post, PostImage $image)
    {
        $this->authorizeEdit($post);
        abort_unless((int) $image->post_id === (int) $post->id, 404);

        $data = $request->validate([
            'caption'  => ['nullable', 'string', 'max:190'],
            'credit'   => ['nullable', 'string', 'max:120'],
            'alt_text' => ['nullable', 'string', 'max:190'],
        ]);

        $image->update($data);

        if ($image->media) {
            $image->media->update(['alt_text' => $data['alt_text'] ?? $image->media->alt_text]);
        }

        ActivityLogger::log('update_image', 'news', 'ছবির তথ্য হালনাগাদ: '.$post->title, $post);

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => 'ছবির তথ্য সংরক্ষিত হয়েছে।'])
            : back()->with('success', 'ছবির তথ্য সংরক্ষিত হয়েছে।');
    }

    /** গ্যালারির যেকোনো ছবিকে Featured Image বানানো */
    public function setFeaturedImage(Request $request, Post $post)
    {
        $this->authorizeEdit($post);

        $validated = $request->validate(['image_id' => ['required', 'integer']]);

        $image = $post->images()->with('media')->findOrFail($validated['image_id']);

        $post->update([
            'featured_image'    => $image->media?->path ?: $image->path,
            'featured_media_id' => $image->media_id,
            'image_caption'     => $image->caption ?: $post->image_caption,
            'image_credit'      => $image->credit ?: $post->image_credit,
        ]);

        ActivityLogger::log('set_featured_image', 'news', 'ফিচার্ড ছবি পরিবর্তন: '.$post->title, $post);

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => 'এই ছবিটি ফিচার্ড ইমেজ হিসেবে সেট হয়েছে।'])
            : back()->with('success', 'এই ছবিটি ফিচার্ড ইমেজ হিসেবে সেট হয়েছে।');
    }

    /** গ্যালারির ছবি মুছে ফেলা (ডাটাবেসের রেফারেন্স অনুযায়ী মিডিয়াও পরিষ্কার হয়) */
    public function destroyImage(Request $request, Post $post, PostImage $image)
    {
        $this->authorizeEdit($post);
        abort_unless((int) $image->post_id === (int) $post->id, 404);

        $media = $image->media;
        $wasFeatured = $post->featured_media_id && (int) $post->featured_media_id === (int) $image->media_id;

        $image->delete();

        // ফিচার্ড ছবিটিই মুছে গেলে পরের ছবি ফিচার্ড করা হয়
        if ($wasFeatured) {
            $next = $post->images()->with('media')->first();
            $post->update([
                'featured_media_id' => $next?->media_id,
                'featured_image'    => $next ? ($next->media?->path ?: $next->path) : null,
            ]);
        }

        $this->removeMediaIfUnused($media, $post->id);
        PublicCache::flushForContent();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'ছবি মুছে ফেলা হয়েছে।']);
        }

        return back()->with('success', 'ছবি মুছে ফেলা হয়েছে।');
    }

    /* ====================== প্রাইভেট হেল্পার ====================== */

    /** Create/Edit ফর্মের ডেটা */
    private function formData(Post $post): array
    {
        return [
            'post'          => $post,
            'categories'    => Category::ordered()->get(['id', 'parent_id', 'name', 'slug']),
            'reporters'     => Reporter::ordered()->get(['id', 'name', 'designation']),
            'allTags'       => Tag::orderByDesc('posts_count')->limit(300)->get(['name', 'slug']),
            'contentMedia'  => $this->contentMediaMap($post->content),
            'imageProvider' => $this->uploader->providerStatus(),
            'maxUploadKb'   => (int) config('images.max_kb', 4096),
            'maxUploads'    => (int) config('images.max_files', 20),
        ];
    }

    /** কনটেন্টে ব্যবহৃত ছবির রেফারেন্স → প্রিভিউ URL (raw URL নয়) */
    private function contentMediaMap(?string $content): array
    {
        $ids = ContentRenderer::mediaIds($content);

        if ($ids === []) {
            return [];
        }

        return Media::whereIn('id', $ids)->get()
            ->mapWithKeys(fn (Media $media) => [
                $media->id => [
                    'id'    => $media->id,
                    'thumb' => $media->thumb,
                    'name'  => $media->display_name,
                ],
            ])
            ->all();
    }

    private function authorizeEdit(Post $post): void
    {
        $user = request()->user();

        if ($user->can_manage('news.edit')) {
            return;
        }

        // রিপোর্টার শুধু নিজের সংবাদ এডিট করতে পারবে
        if ($user->ownsPost($post) && $user->can_manage('news.edit.own')) {
            return;
        }

        abort(403, 'এই সংবাদের সম্পাদনার অনুমতি আপনার নেই।');
    }

    /**
     * ফর্ম ভ্যালিডেশন + DB-যোগ্য অ্যাট্রিবিউট তৈরি।
     * (ছবি আপলোড আলাদা মেথডে হয় — যাতে ImgBB ব্যর্থতার বার্তা নির্দিষ্ট হয়)
     */
    private function validatedPayload(Request $request, ?Post $post): array
    {
        $user = $request->user();
        $canPublish = $user->can_manage('news.publish');

        // ছবি কেবল সরাসরি আপলোড থেকেই নেওয়া হয় — Image URL/লিংক ইনপুট সম্পূর্ণ বাদ।
        $this->rejectImageUrlInput($request);

        $validated = $request->validate([
            'title'               => ['required', 'string', 'min:3', 'max:191'],
            'slug'                => ['nullable', 'string', 'max:191', 'regex:/^[\p{L}\p{N}\-_]+$/u',
                                      Rule::unique('posts', 'slug')->ignore($post?->id)->withoutTrashed()],
            'category_id'         => ['required', 'integer', 'exists:categories,id'],
            'subcategory_id'      => ['nullable', 'integer', 'exists:categories,id', $this->subcategoryRule($request)],
            'reporter_id'         => ['nullable', 'integer', 'exists:reporters,id'],
            'excerpt'             => ['nullable', 'string', 'max:600'],
            'content'             => ['required', 'string', 'min:20', 'max:100000'],
            'video_url'           => ['nullable', 'url', 'max:500'],
            'location'            => ['nullable', 'string', 'max:120'],
            'action'              => ['nullable', 'string', Rule::in(['save_draft', 'draft', 'pending', 'publish', 'published', 'schedule', 'scheduled', 'archive', 'update'])],
            'status'              => ['nullable', Rule::in(array_keys(Post::STATUSES))],
            'published_at'        => ['nullable', 'date'],
            'published_date'      => ['nullable', 'date_format:Y-m-d'],
            'published_time'      => ['nullable', 'date_format:H:i'],
            'scheduled_at'        => ['nullable', 'date'],
            'scheduled_date'      => ['nullable', 'date_format:Y-m-d'],
            'scheduled_time'      => ['nullable', 'date_format:H:i'],
            'is_featured'         => ['nullable', 'boolean'],
            'is_breaking'         => ['nullable', 'boolean'],
            'allow_comments'      => ['nullable', 'boolean'],
            'remove_featured_image' => ['nullable', 'boolean'],
            'remove_og_image'     => ['nullable', 'boolean'],
            'image_caption'       => ['nullable', 'string', 'max:190'],
            'image_credit'        => ['nullable', 'string', 'max:120'],
            'tags'                => ['nullable', 'string', 'max:600'],
            'order'               => ['nullable'],
            // SEO
            'meta_title'          => ['nullable', 'string', 'max:191'],
            'meta_description'    => ['nullable', 'string', 'max:500'],
            'meta_keywords'       => ['nullable', 'string', 'max:400'],
            'og_title'            => ['nullable', 'string', 'max:191'],
            'og_description'      => ['nullable', 'string', 'max:500'],
            'canonical_url'       => ['nullable', 'url', 'max:500'],
        ], [], [
            'title'            => 'শিরোনাম',
            'content'          => 'সংবাদের বিস্তারিত',
            'category_id'      => 'ক্যাটাগরি',
            'subcategory_id'   => 'সাব-ক্যাটাগরি',
            'reporter_id'      => 'রিপোর্টার',
            'excerpt'          => 'সংক্ষিপ্ত বিবরণ',
            'status'           => 'স্ট্যাটাস',
            'scheduled_at'     => 'নির্ধারিত প্রকাশের সময়',
            'published_at'     => 'প্রকাশের সময়',
            'tags'             => 'ট্যাগ',
            'meta_title'       => 'মেটা টাইটেল',
            'meta_description' => 'মেটা বিবরণ',
        ]);

        // ---- স্ট্যাটাস নির্ধারণ (বাটনের action → status) ----
        $status = $this->resolveStatus($request, $post);

        // ---- প্রকাশ/শিডিউলের সময় ----
        $publishedAt = $this->composeDate($request, 'published_at', 'published_date', 'published_time');
        $scheduledAt = $this->composeDate($request, 'scheduled_at', 'scheduled_date', 'scheduled_time');

        if ($status === 'scheduled') {
            if (! $scheduledAt) {
                throw ValidationException::withMessages([
                    'scheduled_date' => 'নির্ধারিত প্রকাশের তারিখ ও সময় দিন (স্ট্যাটাস: নির্ধারিত)।',
                ]);
            }

            if ($scheduledAt->isPast()) {
                throw ValidationException::withMessages([
                    'scheduled_time' => 'নির্ধারিত সময় ভবিষ্যতে হতে হবে (বর্তমান সময়ের পরে)।',
                ]);
            }
        }

        // প্রকাশের সময় কেবল "প্রকাশিত" অবস্থাতেই সেট/আপডেট হয়।
        // খসড়া/রিভিউ/শিডিউলে পুরোনো মান অপরিবর্তিত থাকে (নতুন হলে null) —
        // ফলে খসড়া সেভ করার সময় ভুল করে published_at বসে না।
        if ($status === 'published') {
            $publishedAt = $publishedAt ?: ($post?->published_at ?: now());
        } else {
            $publishedAt = $post?->published_at;
        }

        // ---- কনটেন্ট নিরাপদ করা (XSS) + খালি পরিচ্ছন্নতা ----
        $content = HtmlSanitizer::clean($validated['content']);

        if (mb_strlen(trim(mc_plain($content))) < 20 && ! ContentRenderer::mediaIds($content)) {
            throw ValidationException::withMessages([
                'content' => 'সংবাদের বিস্তারিত অংশ খুব ছোট — অন্তত ২০ অক্ষর লিখুন।',
            ]);
        }

        $excerpt = trim((string) ($validated['excerpt'] ?? ''));
        if ($excerpt === '') {
            // সংক্ষিপ্ত বিবরণ না দিলে কনটেন্ট থেকে তৈরি (ছবির রেফারেন্স বাদ দিয়ে)
            $excerpt = mc_excerpt($content, 220);
        }

        $payload = [
            'title'            => trim($validated['title']),
            'slug'             => isset($validated['slug']) && trim($validated['slug']) !== '' ? trim($validated['slug']) : null,
            'category_id'      => (int) $validated['category_id'],
            'subcategory_id'   => $validated['subcategory_id'] ? (int) $validated['subcategory_id'] : null,
            'reporter_id'      => $validated['reporter_id'] ? (int) $validated['reporter_id'] : null,
            'excerpt'          => mb_substr($excerpt, 0, 600),
            'content'          => $content,
            'video_url'        => $validated['video_url'] ?? null,
            'location'         => $validated['location'] ?? null,
            'status'           => $status,
            'published_at'     => $publishedAt,
            'scheduled_at'     => $scheduledAt ?: null,
            'allow_comments'   => $request->boolean('allow_comments'),
            'is_featured'      => $canPublish ? $request->boolean('is_featured') : (bool) ($post->is_featured ?? false),
            'is_breaking'      => $canPublish ? $request->boolean('is_breaking') : (bool) ($post->is_breaking ?? false),
            'image_caption'    => $validated['image_caption'] ?? null,
            'image_credit'     => $validated['image_credit'] ?? null,
            'meta_title'       => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'meta_keywords'    => $validated['meta_keywords'] ?? null,
            'og_title'         => $validated['og_title'] ?? null,
            'og_description'   => $validated['og_description'] ?? null,
            'canonical_url'    => $validated['canonical_url'] ?? null,
        ];

        // slug খালি রাখলে টাইটেল থেকে তৈরি হবে (Post মডেলে)
        if ($payload['slug'] === null) {
            unset($payload['slug']);
        }

        return $payload;
    }

    /**
     * ছবির ক্ষেত্রে টেক্সট/URL পাঠানোর চেষ্টা প্রত্যাখ্যান।
     *
     * কেন: অ্যাডমিন প্যানেল থেকে ছবি দেওয়ার একমাত্র উপায় সরাসরি ফাইল আপলোড
     * (Backend → ImgBB → DB রেফারেন্স)। পুরোনো “Image URL / Link” ইনপুট ও তার
     * লজিক সম্পূর্ণ সরানো হয়েছে, তাই হাতে করা রিকোয়েস্টেও raw URL সেভ হবে না।
     */
    private function rejectImageUrlInput(Request $request): void
    {
        $offenders = [];

        foreach (['featured_image', 'og_image', 'image', 'image_url', 'featured_image_url', 'thumbnail', 'photo'] as $field) {
            $value = $request->input($field);

            if (is_string($value) && trim($value) !== '') {
                $offenders[$field] = 'ছবি সরাসরি আপলোড করুন — Image URL/লিংক বসানোর সুবিধা নেই।';
            }
        }

        if ($request->filled('images') && ! $request->hasFile('images')) {
            $offenders['images'] = 'গ্যালারির ছবি ফাইল হিসেবে নির্বাচন করুন (লিংক নয়)।';
        }

        if ($offenders !== []) {
            throw ValidationException::withMessages($offenders);
        }
    }

    /** সাব-ক্যাটাগরি নির্বাচিত ক্যাটাগরির অধীন কি না */
    private function subcategoryRule(Request $request): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($request) {
            $categoryId = $request->integer('category_id');

            if (! $value || ! $categoryId) {
                return;
            }

            $parentId = Category::whereKey($value)->value('parent_id');

            if ((int) $parentId !== $categoryId) {
                $fail('নির্বাচিত সাব-ক্যাটাগরিটি এই ক্যাটাগরির অন্তর্গত নয়।');
            }
        };
    }

    /** action/status → চূড়ান্ত স্ট্যাটাস (অনুমতি অনুযায়ী) */
    private function resolveStatus(Request $request, ?Post $post): string
    {
        $user = $request->user();
        $requested = strtolower(trim((string) ($request->input('action') ?: $request->input('status') ?: $post?->status ?: 'draft')));

        $status = match ($requested) {
            'save_draft', 'draft', ''       => 'draft',
            'pending', 'review', 'submit'   => 'pending',
            'publish', 'published'          => 'published',
            'schedule', 'scheduled'         => 'scheduled',
            'archive', 'archived'           => 'archived',
            'update', 'save'                => $post?->status ?: 'draft',
            default                         => array_key_exists($requested, Post::STATUSES) ? $requested : 'draft',
        };

        if (in_array($status, ['published', 'scheduled'], true) && ! $user->can_manage('news.publish')) {
            // অনুমতি না থাকলে সংবাদটি রিভিউতে রেখে দেওয়া হয় (তথ্য হারায় না)
            session()->flash('error', 'প্রকাশের অনুমতি আপনার নেই — সংবাদটি "রিভিউতে" রাখা হয়েছে, এডিটর অনুমোদন দেবেন।');

            return 'pending';
        }

        return $status;
    }

    /** তারিখ + সময় (আলাদা ইনপুট) → Carbon, নয়তো datetime-local/ISO ইনপুট */
    private function composeDate(Request $request, string $field, string $dateField, string $timeField): ?Carbon
    {
        $date = trim((string) $request->input($dateField, ''));
        $time = trim((string) $request->input($timeField, ''));

        if ($date !== '') {
            $time = preg_match('~^\d{2}:\d{2}$~', $time) ? $time : '00:00';

            try {
                return Carbon::createFromFormat(
                    'Y-m-d H:i',
                    $date.' '.$time,
                    (string) config('app.timezone', 'Asia/Dhaka')
                ) ?: null;
            } catch (\Throwable) {
                return null;
            }
        }

        $raw = trim((string) $request->input($field, ''));

        if ($raw === '') {
            return null;
        }

        try {
            return Carbon::parse($raw, (string) config('app.timezone', 'Asia/Dhaka'));
        } catch (\Throwable) {
            return null;
        }
    }

    /** Featured Image / OG Image আপলোড ও রিমুভ */
    private function applyFeaturedMedia(Request $request, Post $post): void
    {
        if ($request->boolean('remove_featured_image')) {
            $this->detachMedia($post, 'featured_media_id', 'featured_image');
        }

        if ($request->hasFile('featured_image')) {
            $request->validate(
                ['featured_image' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:'.(int) config('images.max_kb', 4096)]],
                [],
                ['featured_image' => 'প্রধান ছবি']
            );

            $media = $this->uploader->store($request->file('featured_image'), 'news/featured', MediaUploader::NEWS_MIMES);

            $post->update([
                'featured_image'    => $media->path,
                'featured_media_id' => $media->id,
            ]);
        }

        if ($request->boolean('remove_og_image')) {
            $this->detachMedia($post, 'og_media_id', 'og_image');
        }

        if ($request->hasFile('og_image')) {
            $request->validate(
                ['og_image' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:'.(int) config('images.max_kb', 4096)]],
                [],
                ['og_image' => 'সোশ্যাল শেয়ার ছবি']
            );

            $media = $this->uploader->store($request->file('og_image'), 'news/og', MediaUploader::NEWS_MIMES);

            $post->update([
                'og_image'    => $media->path,
                'og_media_id' => $media->id,
            ]);
        }
    }

    /** নতুন গ্যালারি ছবি (ফর্ম সাবমিটের সময়) */
    private function applyGalleryImages(Request $request, Post $post): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $request->validate([
            'images'   => ['array', 'max:'.max(1, (int) config('images.max_files', 20))],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:'.(int) config('images.max_kb', 4096)],
        ], [], ['images' => 'ছবি']);

        $next = (int) $post->images()->max('sort_order') + 1;

        $result = $this->uploader->storeManyCollect($request->file('images'), 'news/gallery', MediaUploader::NEWS_MIMES);

        foreach ($result['saved'] as $media) {
            $post->images()->create([
                'media_id'   => $media->id,
                'path'       => $media->path,
                'alt_text'   => $media->file_name,
                'sort_order' => $next++,
            ]);
        }

        if ($result['errors'] !== []) {
            session()->flash('upload_errors', $result['errors']);

            if ($result['saved'] === []) {
                throw ValidationException::withMessages(['images' => $result['errors']]);
            }
        }
    }

    /** ছবির ক্রম ফর্ম থেকে এলে প্রয়োগ (drag & drop / তীর বাটন) */
    private function applyOrder(Request $request, Post $post): void
    {
        $raw = $request->input('order');

        if (is_string($raw)) {
            $raw = array_values(array_filter(explode(',', $raw), fn ($v) => $v !== ''));
        }

        if (! is_array($raw) || $raw === []) {
            return;
        }

        foreach (array_map('intval', $raw) as $index => $imageId) {
            $post->images()->where('id', $imageId)->update(['sort_order' => $index]);
        }
    }

    private function handleTags(Post $post, ?string $rawTags): void
    {
        Tag::syncFromString($post, $rawTags);
    }

    /** is_breaking ফ্ল্যাগ ও breaking_news টেবিল সিঙ্ক + ক্যাশ রিফ্রেশ */
    private function syncBreakingFlag(Post $post): void
    {
        if ($post->is_breaking) {
            $post->breakingEntry()->updateOrCreate(
                ['post_id' => $post->id],
                ['title' => $post->title, 'is_enabled' => true]
            );
        } else {
            $post->breakingEntry()->delete();
        }

        PublicCache::flushForContent();
    }

    /** Featured/OG ছবির রেফারেন্স সরানো (ছবিটি অন্য কোথাও ব্যবহৃত না হলে মিডিয়াও মুছে যায়) */
    private function detachMedia(Post $post, string $idColumn, string $pathColumn): void
    {
        $media = $post->{$idColumn} ? Media::find($post->{$idColumn}) : null;

        $post->update([$idColumn => null, $pathColumn => null]);

        $this->removeMediaIfUnused($media, $post->id);
    }

    /** মিডিয়া অন্য কোথাও ব্যবহৃত না হলে রেকর্ড ও হোস্টিং/ডিস্কের ফাইল মুছে ফেলা */
    private function removeMediaIfUnused(?Media $media, ?int $ignorePostId): void
    {
        if (! $media) {
            return;
        }

        try {
            if (! $media->isReferenced($ignorePostId)) {
                $media->deleteFile();
                $media->delete();
            }
        } catch (\Throwable $e) {
            Log::info('Media cleanup skipped', ['media' => $media->id, 'error' => $e->getMessage()]);
        }
    }

    /** AJAX response-এর ছবির তথ্য (raw URL নয় — শুধু signed proxy URL) */
    private function imagePayload(PostImage $image, Post $post): array
    {
        return [
            'id'          => $image->id,
            'media_id'    => $image->media_id,
            'thumb'       => $image->url(),
            'full'        => $image->fullUrl(),
            'label'       => $image->label(),
            'caption'     => $image->caption,
            'credit'      => $image->credit,
            'alt_text'    => $image->alt_text,
            'is_featured' => (int) $post->featured_media_id === (int) $image->media_id && $image->media_id,
            'update_url'  => route('admin.news.images.update', [$post, $image]),
            'delete_url'  => route('admin.news.images.destroy', [$post, $image]),
            'feature_url' => route('admin.news.featured', $post),
        ];
    }

    /** কনটেন্ট এডিটরের আপলোড response — শুধু রেফারেন্স ও proxy URL */
    private function mediaPayload(Media $media): array
    {
        return [
            'id'    => $media->id,
            'key'   => '{{media:'.$media->id.'}}',
            'thumb' => $media->thumb,
            'name'  => $media->display_name,
        ];
    }

    /** সংরক্ষণের পর ব্যবহারকারীকে কী হলো তা জানানো */
    private function statusMessage(Post $post, bool $created): string
    {
        $action = $created ? 'সংবাদ তৈরি হয়েছে' : 'সংবাদ হালনাগাদ হয়েছে';

        return match ($post->status) {
            'published' => $action.' ও প্রকাশিত হয়েছে ✓ (ওয়েবসাইটে সঙ্গে সঙ্গে দেখা যাবে)',
            'scheduled' => $action.' ও শিডিউল করা হয়েছে — '.bn_date($post->scheduled_at).' এ স্বয়ংক্রিয়ভাবে প্রকাশ হবে',
            'pending'   => $action.' — এখন এডিটরের অনুমোদনের অপেক্ষায় (রিভিউতে)',
            'archived'  => $action.' — আর্কাইভে রাখা হয়েছে',
            default     => $action.' — খসড়া হিসেবে সংরক্ষিত',
        };
    }

    /** Article Structured Data (প্রাকদর্শনেও ব্যবহৃত) */
    private function schemaFor(Post $post): array
    {
        return [
            '@context'         => 'https://schema.org',
            '@type'            => 'NewsArticle',
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => route('news.show', $post->slug)],
            'headline'         => mb_substr($post->title, 0, 110),
            'description'      => mc_excerpt($post->excerpt ?: $post->content, 300),
            'image'            => trim((string) $post->featured_image) !== '' ? [$post->featured_image_url] : [],
            'datePublished'    => optional($post->published_at)->toIso8601String(),
            'dateModified'     => optional($post->updated_at)->toIso8601String(),
            'author'           => ['@type' => 'Person', 'name' => $post->reporter?->name ?? $post->author?->name ?? site_setting('site_name')],
            'publisher'        => [
                '@type' => 'Organization',
                'name'  => site_setting('site_name', config('app.name')),
                'logo'  => ['@type' => 'ImageObject', 'url' => mc_image(site_setting('site_logo'))],
            ],
            'articleSection'   => $post->category?->name,
            'keywords'         => $post->tags->pluck('name')->implode(', '),
            'inLanguage'       => 'bn',
        ];
    }
}
