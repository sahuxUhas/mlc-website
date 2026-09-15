<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** মন্তব্য মডারেশন — Pending/Approved/Rejected/Spam/Delete/Bulk/Reported */
class CommentController extends Controller
{
    public function index(Request $request)
    {
        $query = Comment::with('commentable')->newest();

        $status = $request->query('status', 'pending');
        if ($status === 'reported') {
            $query->reported();
        } elseif (array_key_exists($status, Comment::STATUSES)) {
            $query->where('status', $status);
        }

        if ($q = trim((string) $request->query('q'))) {
            $query->where(fn ($w) => $w->where('body', 'like', '%'.$q.'%')->orWhere('guest_name', 'like', '%'.$q.'%'));
        }

        return view('admin.comments.index', [
            'comments' => $query->paginate(20)->withQueryString(),
            'status'   => $status,
            'counts'   => [
                'pending'  => Comment::pending()->count(),
                'approved' => Comment::approved()->count(),
                'rejected' => Comment::where('status', 'rejected')->count(),
                'spam'     => Comment::where('status', 'spam')->count(),
                'reported' => Comment::reported()->count(),
                'all'      => Comment::count(),
            ],
        ]);
    }

    public function updateStatus(Request $request, Comment $comment, string $status)
    {
        abort_unless(array_key_exists($status, Comment::STATUSES), 404);

        $comment->update(['status' => $status]);
        $this->syncCount($comment);

        ActivityLogger::log('comment_'.$status, 'comment', 'মন্তব্য স্ট্যাটাস → '.Comment::STATUSES[$status], $comment);

        return back()->with('success', 'মন্তব্য '.Comment::STATUSES[$status].' হিসেবে চিহ্নিত হয়েছে।');
    }

    public function destroy(Comment $comment)
    {
        ActivityLogger::deleted($comment, 'comment', 'মন্তব্য ট্র্যাশে পাঠানো হয়েছে');
        $comment->delete();
        return back()->with('success', 'মন্তব্য মুছে ফেলা হয়েছে।');
    }

    /** বাল্ক অ্যাকশন — Bulk Delete ও একসাথে স্ট্যাটাস পরিবর্তন */
    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['approved', 'rejected', 'spam', 'pending', 'trash', 'restore', 'delete'])],
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['integer'],
        ]);

        $query = in_array($validated['action'], ['restore', 'delete'], true)
            ? Comment::onlyTrashed()->whereIn('id', $validated['ids'])
            : Comment::whereIn('id', $validated['ids']);

        $count = match ($validated['action']) {
            'trash'   => tap($query->count(), fn () => $query->get()->each->delete()),
            'restore' => tap($query->count(), fn () => $query->get()->each->restore()),
            'delete'  => tap($query->count(), fn () => $query->forceDelete()),
            default   => $query->update(['status' => $validated['action']]),
        };

        ActivityLogger::log('comment_bulk_'.$validated['action'], 'comment', 'বাল্ক মন্তব্য অ্যাকশন: '.bn_num((int) $count));

        return back()->with('success', bn_num((int) $count).'টি মন্তব্যে প্রয়োগ হয়েছে।');
    }

    /** মন্তব্য সংখ্যা সংশ্লিষ্ট সংবাদে সিঙ্ক রাখা */
    private function syncCount(Comment $comment): void
    {
        if ($comment->commentable_type !== Post::class) { return; }

        $post = Post::find($comment->commentable_id);
        if ($post) {
            $post->update(['comments_count' => $post->comments()->approved()->count()]);
        }
    }
}
