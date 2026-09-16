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
        // N+1 এড়াতে একই কুয়েরিতে ক্যাটাগরি + সর্বশেষ ২টি সংবাদ
        $data = Cache::remember('site.home.data', now()->addMinutes(5), function () {
            $categories = Category::forHome()->get();

            // প্রতিটি ক্যাটাগরি থেকে সর্বোচ্চ ২টি — eager loading সহ
            $sections = $categories->map(function (Category $category) {
                $posts = Post::published()
                    ->with(['category:id,name,slug,color', 'reporter:id,name,slug'])
                    ->where(fn ($q) => $q->where('category_id', $category->id)
                        ->orWhereIn('category_id', $category->children()->pluck('id')))
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
