<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;

/**
 * হোমপেজ — ডেমোর কাঠামো অনুযায়ী:
 * Header → Breaking → তারিখ/সময় → টপ হেডার অ্যাড (728x90)
 * → সর্বশেষ প্রকাশিত একটি News Highlight → Category-wise News (প্রতিটিতে সর্বোচ্চ ২টি) → Footer
 */
class HomeController extends Controller
{
    public function index()
    {
        $data = Cache::remember('site.home.data', now()->addMinutes(5), function () {
            // 🚀 Performance: children eager load করে N+1 query এড়ানো হচ্ছে
            $categories = Category::forHome()
                ->with('children:id,parent_id')
                ->get();

            // প্রতিটি ক্যাটাগরি থেকে সর্বোচ্চ ২টি — eager loading সহ
            $sections = $categories->map(function (Category $category) {
                // ✅ Children relation আগে থেকেই লোড করা, তাই নতুন query হবে না
                $categoryIds = $category->children->pluck('id')->push($category->id)->all();

                $posts = Post::published()
                    ->with(['category:id,name,slug,color', 'reporter:id,name,slug'])
                    ->whereIn('category_id', $categoryIds)
                    ->latestFirst()
                    ->limit(2)
                    ->get();

                return $posts->isNotEmpty() ? ['category' => $category, 'posts' => $posts] : null;
            })->filter()->values();

            // সর্বশেষ প্রকাশিত একটি News Highlight
            $highlight = Post::published()
                ->with(['category:id,name,slug,color', 'reporter:id,name,slug', 'images'])
                ->latestFirst()
                ->first();

            return ['sections' => $sections, 'highlight' => $highlight];
        });

        return view('public.home', [
            'sections'  => $data['sections'],
            'highlight' => $data['highlight'],
        ]);
    }
}
