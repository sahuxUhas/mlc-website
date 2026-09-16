<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Announcement;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Media;
use App\Models\Post;
use App\Models\Reporter;
use App\Models\Video;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

/**
 * রিসাইকল বিন — News, Comments, Media, Videos, Announcements, Albums ইত্যাদির
 * Restore / Permanent Delete / Empty Trash।
 */
class TrashController extends Controller
{
    /** ট্র্যাশযোগ্য মডেল ম্যাপ (ডেমোর MC_TRASH_KINDS অনুযায়ী) */
    public const KINDS = [
        'news'          => ['label' => 'সংবাদ',      'model' => Post::class],
        'comments'      => ['label' => 'মন্তব্য',     'model' => Comment::class],
        'media'         => ['label' => 'মিডিয়া',      'model' => Media::class],
        'videos'        => ['label' => 'ভিডিও',      'model' => Video::class],
        'announcements' => ['label' => 'ঘোষণা',      'model' => Announcement::class],
        'albums'        => ['label' => 'অ্যালবাম',     'model' => Album::class],
        'reporters'     => ['label' => 'রিপোর্টার',   'model' => Reporter::class],
        'pages'         => ['label' => 'পেজ',        'model' => \App\Models\Page::class],
        'ads'           => ['label' => 'বিজ্ঞাপন',    'model' => \App\Models\Advertisement::class],
    ];

    public function index(Request $request)
    {
        $type = $request->query('type', 'news');

        if (! isset(self::KINDS[$type])) { $type = 'news'; }

        $items = self::KINDS[$type]['model']::onlyTrashed()->latest()->paginate(20)->withQueryString();

        // প্রতিটি ট্র্যাশের সংখ্যা (ট্যাব ব্যাজ)
        $counts = [];
        foreach (self::KINDS as $key => $meta) {
            $counts[$key] = $meta['model']::onlyTrashed()->count();
        }

        return view('admin.trash.index', [
            'items' => $items, 'type' => $type, 'counts' => $counts, 'kinds' => self::KINDS,
        ]);
    }

    public function restore(Request $request, string $type, int $id)
    {
        $model = $this->resolve($type, $id);

        $model->restore();
        ActivityLogger::log('restore', $type, 'ট্র্যাশ থেকে পুনরুদ্ধার', $model);

        return back()->with('success', 'পুনরুদ্ধার সফল হয়েছে।');
    }

    public function forceDelete(Request $request, string $type, int $id)
    {
        $model = $this->resolve($type, $id);

        ActivityLogger::log('force_delete', $type, 'স্থায়ীভাবে মুছে ফেলা হয়েছে', $model);
        $model->forceDelete();

        return back()->with('success', 'স্থায়ীভাবে মুছে ফেলা হয়েছে।');
    }

    public function emptyTrash(Request $request, string $type)
    {
        if (! isset(self::KINDS[$type])) { abort(404); }

        $count = self::KINDS[$type]['model']::onlyTrashed()->count();
        self::KINDS[$type]['model']::onlyTrashed()->forceDelete();

        ActivityLogger::log('empty_trash', $type, 'ট্র্যাশ খালি করা হয়েছে ('.bn_num($count).'টি)');

        return back()->with('success', bn_num($count).'টি আইটেম স্থায়ীভাবে মুছে ফেলা হয়েছে।');
    }

    private function resolve(string $type, int $id)
    {
        abort_unless(isset(self::KINDS[$type]), 404);

        return self::KINDS[$type]['model']::onlyTrashed()->findOrFail($id);
    }
}
