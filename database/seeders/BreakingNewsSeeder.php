<?php
namespace Database\Seeders;

use App\Models\BreakingNews;
use App\Models\Post;
use Illuminate\Database\Seeder;

class BreakingNewsSeeder extends Seeder
{
    public function run(): void
    {
        $posts = Post::where('is_breaking', true)->published()->latestFirst()->limit(5)->get();

        foreach ($posts as $i => $post) {
            BreakingNews::updateOrCreate(['post_id' => $post->id], [
                'title' => $post->title,
                'url' => route('news.show', $post->slug),
                'is_enabled' => true,
                'priority' => 10 - $i,
                'sort_order' => $i,
                'starts_at' => $post->published_at,
                'ends_at' => now()->addDays(3),
            ]);
        }
    }
}
