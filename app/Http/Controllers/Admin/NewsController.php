<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\Reporter;
use App\Models\Tag;
use App\Services\ActivityLogger;
use App\Services\MediaUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * সংবাদ ব্যবস্থাপনা — Add/Edit/Delete/Restore/Draft/Pending/Publish/
 * Unpublish/Schedule/Archive/Search/Filter/Sort/Pagination + একাধিক ছবি ও Image Order।
 */
class NewsController extends Controller
{
    public function __construct(private MediaUploader $uploader)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $query = Post::with(['category:id,name,slug', 'reporter:id,name']);

        // রিপোর্টার শুধু নিজের সংবাদ দেখবে
        if ($user->role === 'reporter') {
            $query->where('author_id', $user->id);
        }

        // স্ট্যাটাস ফিল্টার (ট্যাব)
        $status = $request->query('status', 'all');
        if ($status === 'trash') {
            $query->onlyTrashed();
        } elseif ($status !== 'all' && array_key_exists($status, Post::STATUSES)) {
            $query->where('status', $status);
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

        $posts = $query->paginate($request->integer('per_page', 20))->withQueryString();

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

    public function create()
    {
        return view('admin.news.form', [
            'post'        => new Post(['status' => 'draft', 'allow_comments' => true]),
            'categories'  => Category::ordered()->get(),
            'reporters'   => Reporter::ordered()->get(),
            'allTags'     => Tag::orderBy('name')->limit(300)->get(['name', 'slug']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePost($request);

        return DB::transaction(function () use ($request, $data) {
            $post = Post::create($data + [
                'author_id' => $request->user()->id,
                'views'     => 0,
            ]);

            $this->handleTags($post, $request->input('tags'));
            $this->handleImages($request, $post);
            $this->syncBreakingFlag($post);

            ActivityLogger::created($post, 'news', 'নতুন সংবাদ তৈরি: '.$post->title);

            return redirect()->route('admin.news.edit', $post)
                ->with('success', 'সংবাদ সংরক্ষিত হয়েছে।');
        });
    }

    public function edit(Post $post)
    {
        $this->authorizeEdit($post);

        return view('admin.news.form', [
            'post'       => $post->load(['images', 'tags']),
            'categories' => Category::ordered()->get(),
            'reporters'  => Reporter::ordered()->get(),
            'allTags'    => Tag::orderBy('name')->limit(300)->get(['name', 'slug']),
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $this->authorizeEdit($post);

        $data = $this->validatePost($request, $post);

        return DB::transaction(function () use ($request, $post, $data) {
            $changed = array_intersect_key($data, $post->getDirty());
            $post->update($data);

            $this->handleTags($post, $request->input('tags'));
            $this->handleImages($request, $post);
            $this->syncBreakingFlag($post);

            ActivityLogger::updated($post, 'news', 'সংবাদ হালনাগাদ: '.$post->title, $data);

            return redirect()->route('admin.news.edit', $post)
                ->with('success', 'সংবাদ হালনাগাদ হয়েছে।');
        });
    }

    /** স্ট্যাটাস দ্রুত পরিবর্তন (draft/pending/publish/unpublish/schedule/archive) */
    public function changeStatus(Request $request, Post $post, string $status)
    {
        abort_unless(array_key_exists($status, Post::STATUSES), 404);
        $this->authorizeEdit($post);

        // প্রকাশ করার অনুমতি না থাকলে ব্লক
        if (in_array($status, ['published', 'scheduled'], true) && ! $request->user()->can_manage('news.publish')) {
            return back()->with('error', 'প্রকাশের অনুমতি আপনার নেই — এডিটরের অনুমোদন প্রয়োজন।');
        }

        $attributes = ['status' => $status];

        if ($status === 'published') {
            $attributes['published_at'] = $post->published_at ?? now();
        }

        if ($status === 'scheduled') {
            $validated = $request->validate(['scheduled_at' => ['required', 'date', 'after:now']], [], ['scheduled_at' => 'প্রকাশের সময়']);
            $attributes['scheduled_at'] = $validated['scheduled_at'];
        }

        $post->update($attributes);

        ActivityLogger::log($status, 'news', 'স্ট্যাটাস পরিবর্তন → '.(Post::STATUSES[$status]).': '.$post->title, $post);

        return back()->with('success', 'স্ট্যাটস পরিবর্তিত হয়েছে → '.Post::STATUSES[$status]);
    }

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
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['integer'],
        ]);

        $ids = $validated['ids'];
        $user = $request->user();

        $query = match ($validated['action']) {
            'restore' => Post::onlyTrashed()->whereIn('id', $ids),
            'delete'  => Post::onlyTrashed()->whereIn('id', $ids),
            default   => Post::whereIn('id', $ids),
        };

        $count = match ($validated['action']) {
            'publish'   => abort_unless($user->can_manage('news.publish'), 403) ?: $query->update(['status' => 'published', 'published_at' => DB::raw('COALESCE(published_at, NOW())')]),
            'unpublish' => $query->update(['status' => 'draft']),
            'draft'     => $query->update(['status' => 'draft']),
            'archive'   => $query->update(['status' => 'archived']),
            'trash'     => tap($query->get()->count(), fn () => $query->get()->each->delete()),
            'restore'   => tap($query->get()->count(), fn () => $query->get()->each->restore()),
            'delete'    => tap($query->count(), fn () => $query->forceDelete()),
        };

        ActivityLogger::log('bulk_'.$validated['action'], 'news', 'বাল্ক অ্যাকশন: '.bn_num((int) $count).'টি সংবাদ');

        return back()->with('success', bn_num((int) $count).'টি সংবাদে প্রয়োগ হয়েছে।');
    }

    /** ছবির ক্রম পরিবর্তন (drag & drop থেকে আসা order) */
    public function reorderImages(Request $request, Post $post)
    {
        $validated = $request->validate(['order' => ['required', 'array'], 'order.*' => ['integer']]);

        foreach ($validated['order'] as $index => $imageId) {
            $post->images()->where('id', $imageId)->update(['sort_order' => $index]);
        }

        ActivityLogger::log('reorder_images', 'news', 'ছবির ক্রম পরিবর্তন: '.$post->title, $post);

        return back()->with('success', 'ছবির ক্রম হালনাগাদ হয়েছে।');
    }

    public function destroyImage(Request $request, Post $post, PostImage $image)
    {
        abort_unless($image->post_id === $post->id, 404);

        @unlink(public_path('uploads/'.$image->path));
        $image->delete();

        return back()->with('success', 'ছবি মুছে ফেলা হয়েছে।');
    }

    /* ---------------- প্রাইভেট হেল্পার ---------------- */

    private function authorizeEdit(Post $post): void
    {
        $user = request()->user();

        // রিপোর্টার শুধু নিজের সংবাদ এডিট করতে পারবে
        if ($user->role === 'reporter' && ! $user->ownsPost($post)) {
            abort(403, 'শুধু নিজের সংবাদ সম্পাদনা করতে পারবেন।');
        }
    }

    private function validatePost(Request $request, ?Post $post = null): array
    {
        $slugRule = [
            'nullable', 'string', 'max:191',
            Rule::unique('posts', 'slug')->ignore($post?->id)->withoutTrashed(),
        ];

        $validated = $request->validate([
            'title'            => ['required', 'string', 'min:5', 'max:191'],
            'slug'             => $slugRule,
            'category_id'      => ['required', 'integer', 'exists:categories,id'],
            'subcategory_id'   => ['nullable', 'integer', 'exists:categories,id'],
            'reporter_id'      => ['nullable', 'integer', 'exists:reporters,id'],
            'excerpt'          => ['nullable', 'string', 'max:600'],
            'content'          => ['required', 'string', 'min:20'],
            'video_url'        => ['nullable', 'url', 'max:500'],
            'location'         => ['nullable', 'string', 'max:120'],
            'status'           => ['required', Rule::in(array_keys(Post::STATUSES))],
            'published_at'     => ['nullable', 'date'],
            'scheduled_at'     => ['nullable', 'date'],
            'is_featured'      => ['nullable', 'boolean'],
            'is_breaking'      => ['nullable', 'boolean'],
            'allow_comments'   => ['nullable', 'boolean'],
            'featured_image'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'images'           => ['nullable', 'array', 'max:20'],
            'images.*'         => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_caption'    => ['nullable', 'string', 'max:190'],
            'image_credit'     => ['nullable', 'string', 'max:120'],
            'tags'             => ['nullable', 'string', 'max:600'],
            // SEO
            'meta_title'       => ['nullable', 'string', 'max:191'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords'    => ['nullable', 'string', 'max:400'],
            'og_title'         => ['nullable', 'string', 'max:191'],
            'og_description'   => ['nullable', 'string', 'max:500'],
            'og_image'         => ['nullable', 'image', 'max:4096'],
            'canonical_url'    => ['nullable', 'url', 'max:500'],
        ], [], [
            'title' => 'শিরোনাম', 'content' => 'সংবাদের বিবরণ', 'category_id' => 'ক্যাটাগরি',
        ]);

        // ব্রেকিং/ফিচার্ড শুধু অনুমতি থাকলে
        $user = $request->user();
        if (! $user->can_manage('news.publish')) {
            unset($validated['is_featured'], $validated['is_breaking']);
        }

        // ছবি আপলোড → পাথ সংরক্ষণ
        if ($request->hasFile('featured_image')) {
            $media = $this->uploader->store($request->file('featured_image'), 'news');
            $validated['featured_image'] = $media->path;
        }

        if ($request->hasFile('og_image')) {
            $media = $this->uploader->store($request->file('og_image'), 'news/og');
            $validated['og_image'] = $media->path;
        }

        // সংক্ষিপ্ত বিবরণ না দিলে কনটেন্ট থেকে তৈরি
        if (empty($validated['excerpt'])) {
            $validated['excerpt'] = mc_excerpt($validated['content'], 220);
        }

        // স্ট্যাটাস অনুযায়ী প্রকাশের সময় ঠিক করা
        if (($validated['status'] ?? null) === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $validated['is_featured']    = $request->boolean('is_featured');
        $validated['is_breaking']    = $request->boolean('is_breaking');
        $validated['allow_comments'] = $request->boolean('allow_comments');

        return $validated;
    }

    private function handleTags(Post $post, ?string $rawTags): void
    {
        Tag::syncFromString($post, $rawTags);
    }

    /** নতুন আপলোড হওয়া একাধিক ছবি গ্যালারিতে যোগ */
    private function handleImages(Request $request, Post $post): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $next = (int) $post->images()->max('sort_order') + 1;

        foreach ($this->uploader->storeMany($request->file('images'), 'news/gallery') as $media) {
            $post->images()->create([
                'path'       => $media->path,
                'sort_order' => $next++,
            ]);
        }
    }

    /** is_breaking ফ্ল্যাগ ও breaking_news টেবিল সিঙ্ক রাখা */
    private function syncBreakingFlag(Post $post): void
    {
        if ($post->is_breaking) {
            $post->breakingEntry()->firstOrCreate(
                ['post_id' => $post->id],
                ['title' => $post->title, 'is_enabled' => true, 'priority' => 1]
            );
        } else {
            $post->breakingEntry()->delete();
        }

        \Illuminate\Support\Facades\Cache::forget('site.breaking.active');
    }
}
