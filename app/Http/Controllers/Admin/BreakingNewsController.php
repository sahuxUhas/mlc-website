<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BreakingNews;
use App\Models\Post;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/** ব্রেকিং নিউজ — Add/Edit/Delete/Enable-Disable/Priority/Start-End Time/Order */
class BreakingNewsController extends Controller
{
    public function index()
    {
        return view('admin.breaking.index', [
            'items' => BreakingNews::with('post:id,slug,title')->orderByDesc('priority')->orderBy('sort_order')->paginate(20),
            'posts' => Post::published()->latestFirst()->limit(200)->get(['id', 'title', 'slug']),
        ]);
    }

    public function store(Request $request)
    {
        $item = BreakingNews::create($this->validateBreaking($request));
        $this->flush();
        ActivityLogger::created($item, 'breaking', 'ব্রেকিং নিউজ যোগ: '.$item->title);
        return back()->with('success', 'ব্রেকিং নিউজ যোগ হয়েছে।');
    }

    public function update(Request $request, BreakingNews $breaking)
    {
        $breaking->update($this->validateBreaking($request));
        $this->flush();
        ActivityLogger::updated($breaking, 'breaking', 'ব্রেকিং নিউজ হালনাগাদ');
        return back()->with('success', 'ব্রেকিং নিউজ হালনাগাদ হয়েছে।');
    }

    public function destroy(BreakingNews $breaking)
    {
        $breaking->delete();
        $this->flush();
        return back()->with('success', 'ব্রেকিং নিউজ মুছে ফেলা হয়েছে।');
    }

    public function toggle(BreakingNews $breaking)
    {
        $breaking->update(['is_enabled' => ! $breaking->is_enabled]);
        $this->flush();
        return back()->with('success', $breaking->is_enabled ? 'ব্রেকিং নিউজ চালু হয়েছে।' : 'ব্রেকিং নিউজ বন্ধ হয়েছে।');
    }

    private function validateBreaking(Request $request): array
    {
        $data = $request->validate([
            'post_id'    => ['nullable', 'integer', 'exists:posts,id'],
            'title'      => ['required', 'string', 'min:4', 'max:191'],
            'url'        => ['nullable', 'string', 'max:500'],
            'is_enabled' => ['nullable', 'boolean'],
            'priority'   => ['nullable', 'integer', 'min:0', 'max:999'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'starts_at'  => ['nullable', 'date'],
            'ends_at'    => ['nullable', 'date', 'after_or_equal:starts_at'],
        ], [], ['title' => 'ব্রেকিং নিউজের টেক্সট']);

        $data['is_enabled'] = $request->boolean('is_enabled', true);

        // নিউজ নির্বাচন করলে টাইটেল/URL স্বয়ংক্রিয়ভাবে বসে
        if (! empty($data['post_id'])) {
            $post = Post::find($data['post_id']);
            if ($post) {
                $data['title'] = $data['title'] ?: $post->title;
                $data['url']   = $data['url'] ?: route('news.show', $post->slug);
            }
        }

        return $data;
    }

    private function flush(): void { Cache::forget('site.breaking.active'); Cache::forget('site.home.data'); }
}
