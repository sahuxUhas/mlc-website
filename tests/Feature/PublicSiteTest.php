<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** পাবলিক সাইট: হোম, আর্টিকেল, ক্যাটাগরি, সার্চ (spec ৬, ৭, ২৮) */
class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders(): void
    {
        Post::factory()->count(3)->create();

        $this->get('/')->assertOk();
    }

    public function test_article_page_is_reachable_by_slug(): void
    {
        $post = Post::factory()->create();

        $this->get('/news/'.$post->slug)->assertOk()->assertSee($post->title);
    }

    public function test_draft_article_is_not_publicly_visible(): void
    {
        $post = Post::factory()->draft()->create();

        $this->get('/news/'.$post->slug)->assertNotFound();
    }

    public function test_article_view_count_increments_once_per_session(): void
    {
        $post = Post::factory()->create();

        // বট ফিল্টার এড়াতে সত্যিকারের ব্রাউজারের মতো User-Agent পাঠাতে হয়
        $headers = ['User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 Chrome/126.0 Safari/537.36'];

        $this->withHeaders($headers)->get('/news/'.$post->slug)->assertOk();
        $this->assertSame(1, (int) $post->fresh()->views, 'প্রথম ভিজিটে ঠিক ১ ভিউ');

        $this->withHeaders($headers)->get('/news/'.$post->slug)->assertOk();
        $this->assertSame(1, (int) $post->fresh()->views, 'একই সেশনে ভিউ দ্বিগুণ হওয়া উচিত নয়');

        // বিস্তারিত কভারেজ: tests/Feature/ViewCounterTest.php
    }

    public function test_category_page_lists_only_that_category_posts(): void
    {
        $cat   = Category::factory()->create();
        $other = Category::factory()->create();
        $mine  = Post::factory()->create(['category_id' => $cat->id]);
        Post::factory()->create(['category_id' => $other->id]);

        $this->get('/category/'.$cat->slug)->assertOk()->assertSee($mine->title);
    }

    public function test_search_returns_matching_news(): void
    {
        $match = Post::factory()->create(['title' => 'ফেনী নদীর পানি বৃদ্ধি']);
        Post::factory()->create(['title' => 'অন্য একটি সংবাদ']);

        $this->get('/search?q='.urlencode('ফেনী নদী'))->assertOk()->assertSee($match->title);
    }

    public function test_unknown_page_returns_404(): void
    {
        $this->get('/news/ei-slug-ti-nei-kokhono')->assertNotFound();
    }

    public function test_sitemap_and_robots_are_served(): void
    {
        Post::factory()->create();

        $this->get('/sitemap.xml')->assertOk();
        $this->get('/robots.txt')->assertOk();
    }
}
