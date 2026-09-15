<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Advertisement;
use App\Models\Album;
use App\Models\Announcement;
use App\Models\Category;
use App\Models\Comment;
use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Reporter;
use App\Models\Video;

/** ড্যাশবোর্ড — সব কাউন্ট, সাম্প্রতিক আইটেম ও দ্রুত অ্যাকশন */
class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_news'    => Post::count(),
            'published'     => Post::where('status', 'published')->count(),
            'draft'         => Post::where('status', 'draft')->count(),
            'pending'       => Post::where('status', 'pending')->count(),
            'scheduled'     => Post::where('status', 'scheduled')->count(),
            'archived'      => Post::where('status', 'archived')->count(),
            'trash'         => Post::onlyTrashed()->count(),
            'total_views'   => (int) Post::sum('views'),
            'comments'      => Comment::count(),
            'comments_pending' => Comment::pending()->count(),
            'videos'        => Video::count(),
            'announcements' => Announcement::count(),
            'categories'    => Category::count(),
            'reporters'     => Reporter::count(),
            'albums'        => Album::count(),
            'ads'           => Advertisement::count(),
            'messages_unread' => ContactMessage::unread()->count(),
        ];

        // সাম্প্রতিক সংবাদ
        $recentNews = Post::with('category:id,name,slug')
            ->latest()
            ->limit(8)
            ->get();

        // সাম্প্রতিক মন্তব্য
        $recentComments = Comment::with('commentable')
            ->newest()
            ->limit(6)
            ->get();

        // নির্ধারিত সংবাদ
        $scheduledNews = Post::where('status', 'scheduled')
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();

        // সর্বাধিক পঠিত
        $mostRead = Post::published()->popular()->limit(5)->get(['id', 'title', 'slug', 'views']);

        // সাম্প্রতিক অ্যাক্টিভিটি
        $activities = ActivityLog::newest()->limit(10)->get();

        // ৭ দিনের প্রকাশের ধারা (চার্ট)
        $trend = collect(range(6, 0))->map(function ($daysBack) {
            $date = now()->subDays($daysBack);

            return [
                'label' => bn_num($date->format('d/m')),
                'count' => Post::whereDate('published_at', $date->toDateString())->count(),
            ];
        });

        return view('admin.dashboard.index', compact(
            'stats', 'recentNews', 'recentComments', 'scheduledNews', 'mostRead', 'activities', 'trend'
        ));
    }
}
