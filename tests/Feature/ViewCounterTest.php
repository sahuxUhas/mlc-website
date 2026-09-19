<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * বাস্তব পঠনসংখ্যা (Real View Count):
 *   News Publish → 0 Views · Visitor দেখল → 1 · আরেকজন দেখল → 2
 * ডেমো/ফেক সংখ্যা নেই, রিফ্রেশে সংখ্যা ফুলে যায় না।
 */
class ViewCounterTest extends TestCase
{
    use RefreshDatabase;

    private const DESKTOP_UA = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 Chrome/126.0 Safari/537.36';

    /** ভিন্ন ভিজিটর হিসেবে পেজ খোলা (IP + ব্রাউজার আলাদা) */
    private function visit(string $slug, string $ip = '198.51.100.10', string $ua = self::DESKTOP_UA)
    {
        return $this->withHeaders(['User-Agent' => $ua])
            ->withServerVariables(['REMOTE_ADDR' => $ip])
            ->get('/news/'.$slug);
    }

    private function views(Post $post): int
    {
        return (int) $post->fresh()->views;
    }

    /* ---------------- পাবলিশ → ০ থেকে শুরু ---------------- */

    public function test_newly_published_news_starts_from_zero_views(): void
    {
        $post = Post::factory()->published()->create();

        $this->assertSame(0, $this->views($post));
    }

    public function test_admin_publishing_from_the_form_starts_at_zero_views(): void
    {
        $editor = User::factory()->editor()->create();

        $this->actingAs($editor)->post('/admin/news', [
            'title'          => 'ভিউ শূন্য থেকে শুরু হওয়া সংবাদ',
            'category_id'    => Category::factory()->create()->id,
            'content'        => '<p>প্রকাশের সময় পঠনসংখ্যা শূন্য থাকবে — এটাই প্রত্যাশিত।</p>',
            'action'         => 'publish',
            'published_date' => now()->format('Y-m-d'),
            'published_time' => now()->format('H:i'),
            'views'          => 99999,   // চেষ্টা করেও ফেক সংখ্যা বসানো যাবে না
        ]);

        $post = Post::firstOrFail();

        $this->assertSame('published', $post->status);
        $this->assertSame(0, $this->views($post));
    }

    /* ---------------- বাস্তব ভিজিট গোনা ---------------- */

    public function test_first_real_visit_counts_exactly_one_view(): void
    {
        $post = Post::factory()->published()->create();

        $this->visit($post->slug)->assertOk();

        $this->assertSame(1, $this->views($post));
    }

    public function test_another_visitor_increases_the_count(): void
    {
        $post = Post::factory()->published()->create();

        $this->visit($post->slug, '198.51.100.10')->assertOk();
        $this->assertSame(1, $this->views($post));

        // দ্বিতীয় ভিজিটর — নতুন সেশন + নতুন IP/ব্রাউজার
        $this->flushSession();
        $this->visit($post->slug, '203.0.113.22')->assertOk();

        $this->assertSame(2, $this->views($post));
    }

    /* ---------------- ডুপ্লিকেট ভিউ প্রোটেকশন ---------------- */

    public function test_refresh_does_not_increase_views_in_same_session(): void
    {
        $post = Post::factory()->published()->create();

        $this->visit($post->slug)->assertOk();
        $this->visit($post->slug)->assertOk();
        $this->visit($post->slug)->assertOk();

        $this->assertSame(1, $this->views($post), 'একই সেশনে বারবার রিফ্রেশে ভিউ বাড়া উচিত নয়');
    }

    public function test_cooldown_protects_even_after_the_session_is_gone(): void
    {
        config(['views.dedupe_minutes' => 720]);   // ১২ ঘণ্টা

        $post = Post::factory()->published()->create();

        $this->visit($post->slug, '198.51.100.77')->assertOk();
        $this->assertSame(1, $this->views($post));

        // সেশন মুছে গেলেও একই ভিজিটর কুলডাউনের মধ্যে আবার গোনা হবে না
        $this->flushSession();
        $this->visit($post->slug, '198.51.100.77')->assertOk();

        $this->assertSame(1, $this->views($post), 'কুলডাউনের মধ্যে একই ভিজিটর আবার গোনা যাবে না');
    }

    public function test_cooldown_expiry_allows_a_new_view_from_the_same_visitor(): void
    {
        config(['views.dedupe_minutes' => 720]);

        $post = Post::factory()->published()->create();

        $this->visit($post->slug, '198.51.100.88')->assertOk();
        $this->assertSame(1, $this->views($post));

        // কুলডাউন সময় পার (ক্যাশ কী মুছে ফেলা হলো) — এখন নতুন ভিজিট গোনা হবে
        $this->travel(13)->hours();
        $this->flushSession();
        $this->visit($post->slug, '198.51.100.88')->assertOk();

        $this->assertSame(2, $this->views($post));

        $this->travelBack();
    }

    /* ---------------- ডেমো/বট/খসড়া — গোনা হবে না ---------------- */

    public function test_bot_and_crawler_visits_are_not_counted(): void
    {
        $post = Post::factory()->published()->create();

        $this->visit($post->slug, '66.249.66.1', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')
            ->assertOk();

        $this->assertSame(0, $this->views($post), 'ক্রলার ভিজিট গোনা যাবে না');

        // ফেসবুক/হোয়াটসঅ্যাপ লিংক প্রিভিউও ভিজিট নয়
        $this->flushSession();
        $this->visit($post->slug, '57.141.0.10', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)')
            ->assertOk();

        $this->assertSame(0, $this->views($post));
    }

    public function test_draft_news_page_does_not_count_views(): void
    {
        $post = Post::factory()->draft()->create();

        $this->visit($post->slug, '198.51.100.30')->assertNotFound();

        $this->assertSame(0, $this->views($post));
    }

    public function test_admin_preview_does_not_count_views(): void
    {
        $editor = User::factory()->editor()->create();
        $post = Post::factory()->draft()->create(['author_id' => $editor->id]);

        $this->actingAs($editor)->get('/admin/news/'.$post->slug.'/preview')->assertOk();

        $this->assertSame(0, $this->views($post), 'প্রিভিউ ভিজিট হিসেবে গোনা যাবে না');
    }

    /* ---------------- DB-তে বাস্তব সংখ্যাই থাকে ---------------- */

    public function test_view_count_is_stored_in_database(): void
    {
        $post = Post::factory()->published()->create();

        $this->visit($post->slug, '198.51.100.40')->assertOk();
        $this->flushSession();
        $this->visit($post->slug, '198.51.100.41')->assertOk();

        $this->assertSame(2, (int) DB::table('posts')->where('id', $post->id)->value('views'));
    }

    public function test_views_are_not_mass_assignable_from_a_request(): void
    {
        $post = Post::factory()->published()->create();

        $post->fill(['views' => 5000]);

        $this->assertSame(0, $this->views($post), 'views ফিল্ড রিকোয়েস্ট থেকে বসানো যাবে না');
    }

    /* ---------------- পাবলিক সাইটে শুধু বাস্তব সংখ্যা ---------------- */

    public function test_public_page_shows_the_real_view_count(): void
    {
        $post = Post::factory()->published()->create();

        $this->visit($post->slug, '198.51.100.50')->assertOk();
        $this->flushSession();
        $this->visit($post->slug, '198.51.100.51')->assertOk();

        $this->flushSession();
        $this->withHeaders(['User-Agent' => self::DESKTOP_UA])
            ->withServerVariables(['REMOTE_ADDR' => '198.51.100.52'])
            ->get('/')
            ->assertOk()
            ->assertSee('২ বার', false);
    }

    /* ---------------- ভিডিও কাউন্টারও একই নিয়মে ---------------- */

    public function test_video_views_are_counted_once_per_visitor(): void
    {
        $video = Video::create([
            'title'        => 'ভিউ টেস্ট ভিডিও',
            'slug'         => 'view-test-video',
            'video_url'    => 'https://www.facebook.com/share/v/19Xg5B3P78/',
            'status'       => 'published',
            'is_visible'   => true,
            'published_at' => now(),
        ]);

        $this->withHeaders(['User-Agent' => self::DESKTOP_UA])
            ->withServerVariables(['REMOTE_ADDR' => '198.51.100.60'])
            ->get('/videos/'.$video->slug)
            ->assertOk();

        $this->assertSame(1, (int) $video->fresh()->views);

        // একই ভিজিটর আবার — বাড়বে না
        $this->withHeaders(['User-Agent' => self::DESKTOP_UA])
            ->withServerVariables(['REMOTE_ADDR' => '198.51.100.60'])
            ->get('/videos/'.$video->slug)
            ->assertOk();

        $this->assertSame(1, (int) $video->fresh()->views);
    }
}
