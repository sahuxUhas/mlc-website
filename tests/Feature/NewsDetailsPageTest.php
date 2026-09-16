<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * নিউজ ডিটেইলস (article) পেজের নতুন কাঠামো:
 * শিরোনাম → ফিচার্ড ছবি → কনটেন্ট → শেয়ার → ফেসবুক ফলো → মন্তব্য।
 * আর্টিকেল সেকশন থেকে ব্রেডক্রাম্ব/ব্যাজ/রিপোর্টার/তারিখ-ভিউ/ট্যাগ/পুরোনো শেয়ার
 * ও ক্যাটাগরি লিংক সম্পূর্ণ বাদ যাওয়া যাচাই করা হয়।
 */
class NewsDetailsPageTest extends TestCase
{
    use RefreshDatabase;

    private function makePost(array $attrs = []): Post
    {
        return Post::factory()->create(array_merge([
            'title'          => 'মহালছড়িতে টানা বৃষ্টি: নদীতে ভাঙন, বাজার এলাকায় হাঁটুপানি',
            'slug'           => 'mahalchhari-brishti',
            'featured_image' => 'news/rain.jpg',
            'image_caption'  => null,
            'image_credit'   => null,
            'excerpt'        => null,
            'reporter_id'    => null,
        ], $attrs));
    }

    /** পেজের <article> অংশটুকু আলাদা করে নেওয়া (হেডার/ফুটার বাদ) */
    private function articleHtml(string $html): string
    {
        if (preg_match('/<article\b.*?<\/article>/s', $html, $m)) {
            return $m[0];
        }

        $this->fail('আর্টিকেল এলিমেন্ট পাওয়া যায়নি');
    }

    /** @return array{0:string,1:string} [article html, article text] */
    private function articleParts(Post $post): array
    {
        $html = $this->get('/news/'.$post->slug)->assertOk()->getContent();
        $article = $this->articleHtml($html);

        return [$article, preg_replace('/\s+/u', ' ', strip_tags($article))];
    }

    public function test_title_is_printed_exactly_once_in_the_article(): void
    {
        $post = $this->makePost();
        [, $text] = $this->articleParts($post);

        $this->assertSame(
            1,
            substr_count($text, $post->title),
            'নিউজ শিরোনাম আর্টিকেলে ঠিক একবারই দেখানো উচিত (duplicate নয়)'
        );
    }

    public function test_breadcrumb_badges_reporter_meta_tags_and_old_share_block_are_gone(): void
    {
        $category = Category::factory()->create(['name' => 'মহালছড়ি', 'slug' => 'mahalchhari']);
        $post = $this->makePost(['category_id' => $category->id]);

        [$article] = $this->articleParts($post);

        foreach ([
            'ব্রেডক্রাম্ব',        // breadcrumb
            '/category/',        // ক্যাটাগরি ব্যাজ/লিংক
            'ডেমো',              // ডেমো কনটেন্ট
            'বার পঠিত',           // ভিউ কাউন্ট
            'স্টাফ রিপোর্টার',     // রিপোর্টার designation
            'ph-printer',        // প্রিন্ট বাটন
            'ট্যাগ:',            // ট্যাগ সেকশন
            'বিভাগসমূহ',          // ক্যাটাগরি নেভিগেশন
        ] as $needle) {
            $this->assertStringNotContainsString($needle, $article, "«{$needle}» থাকা উচিত নয়");
        }
    }

    public function test_share_section_contains_all_platforms_with_canonical_url(): void
    {
        $post = $this->makePost();
        $url  = route('news.show', $post->slug);

        [$article] = $this->articleParts($post);

        $this->assertStringContainsString('https://www.facebook.com/sharer/sharer.php?u='.urlencode($url), $article);
        $this->assertStringContainsString('https://wa.me/?text='.urlencode($post->title.' '.$url), $article);
        $this->assertStringContainsString('https://t.me/share/url?url='.urlencode($url), $article);
        $this->assertStringContainsString('https://twitter.com/intent/tweet?url='.urlencode($url), $article);
        $this->assertStringContainsString('data-mc-share-messenger', $article);
        $this->assertStringContainsString('data-mc-share-copy', $article);
        $this->assertStringContainsString('data-mc-share-native', $article);
        $this->assertStringContainsString('কপি লিংক', $article);
    }

    public function test_facebook_follow_section_uses_configured_page_url(): void
    {
        Setting::put('social_facebook', 'https://www.facebook.com/mahalcharinews');
        Setting::flushCache();

        $post = $this->makePost();

        $this->get('/news/'.$post->slug)
            ->assertOk()
            ->assertSee('মহালছড়ির প্রতিটি খবর সবার আগে পেতে মহালছড়ি নিউজ-এর ফেসবুক পেজ ফলো করুন।')
            ->assertSee('ফেসবুকে ফলো করুন')
            ->assertSee('https://www.facebook.com/mahalcharinews', false);
    }

    public function test_facebook_follow_is_hidden_when_no_page_url_is_configured(): void
    {
        $post = $this->makePost();

        [$article] = $this->articleParts($post);

        // সেটিংসে পেজ URL না থাকলে বাটনটিই রেন্ডার হয় না (hardcode করা ডেমো URL নেই)
        $this->assertStringNotContainsString('ফেসবুকে ফলো করুন', $article);
    }

    public function test_footer_category_column_stays_on_other_pages_but_is_hidden_on_news_details(): void
    {
        $category = Category::factory()->create(['name' => 'মাইসছড়ি', 'slug' => 'maichhari', 'is_visible' => true]);
        $post = $this->makePost(['category_id' => $category->id]);
        Post::factory()->count(2)->create();

        $this->get('/news/'.$post->slug)->assertOk()->assertDontSee('বিভাগসমূহ');
        $this->get('/')->assertOk()->assertSee('বিভাগসমূহ');
    }

    public function test_comment_area_is_compact_and_asks_only_for_name_and_comment(): void
    {
        $post = $this->makePost();

        [$article] = $this->articleParts($post);

        $this->assertStringContainsString('data-mc-comment-toggle', $article);
        $this->assertStringContainsString('aria-controls="mc-comment-form"', $article);
        $this->assertStringContainsString('name="guest_name"', $article);
        $this->assertStringContainsString('name="body"', $article);
        $this->assertStringContainsString('name="website"', $article);   // honeypot
        $this->assertStringContainsString('মন্তব্য পাঠান', $article);
        $this->assertStringContainsString('এখনও কোনো মন্তব্য নেই। প্রথম মন্তব্যটি করুন।', $article);
        // ইমেইল ফিল্ড ও বড় comment লেআউট থাকবে না
        $this->assertStringNotContainsString('name="guest_email"', $article);
        $this->assertStringNotContainsString('ইমেইল', $article);
    }

    public function test_comment_section_is_hidden_when_comments_are_disabled_for_the_post(): void
    {
        $post = $this->makePost(['allow_comments' => false]);

        [$article] = $this->articleParts($post);

        $this->assertStringNotContainsString('data-mc-comment-toggle', $article);
        $this->assertStringNotContainsString('মন্তব্য পাঠান', $article);
    }

    public function test_missing_database_data_renders_no_demo_content(): void
    {
        $post = $this->makePost([
            'featured_image' => null,
            'excerpt'        => null,
            'image_caption'  => null,
            'image_credit'   => null,
        ]);

        [$article] = $this->articleParts($post);

        $this->assertStringNotContainsString('ডেমো', $article);
        $this->assertStringNotContainsString('figcaption', $article);
        $this->assertStringContainsString($post->title, $article);
    }

    public function test_seo_metadata_and_structured_data_are_preserved(): void
    {
        $post = $this->makePost();

        $html = $this->get('/news/'.$post->slug)->assertOk()->getContent();

        $this->assertStringContainsString('<link rel="canonical" href="'.route('news.show', $post->slug).'">', $html);
        $this->assertStringContainsString('<meta property="og:type" content="article">', $html);
        $this->assertStringContainsString('"@type":"NewsArticle"', $html);
        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $html);
    }

    public function test_tag_and_category_systems_are_untouched_outside_the_details_page(): void
    {
        $category = Category::factory()->create(['name' => 'খেলার খবর', 'slug' => 'khela']);
        $post = $this->makePost(['category_id' => $category->id]);

        // ট্যাগ যুক্ত থাকলেও ডিটেইলস পেজে ট্যাগ সেকশন দেখাবে না
        Tag::syncFromString($post, 'বন্যা,বৃষ্টি');
        $this->assertGreaterThan(0, $post->tags()->count());
        $this->get('/news/'.$post->slug)->assertOk()->assertDontSee('ট্যাগ:');

        // ক্যাটাগরি ও ট্যাগ পেজ আগের মতোই কাজ করে
        $this->get('/category/'.$category->slug)->assertOk()->assertSee($post->title);
        $this->get('/tag/'.$post->tags()->first()->slug)->assertOk()->assertSee($post->title);
    }

    public function test_report_endpoint_only_accepts_approved_comments_of_that_news(): void
    {
        $post  = $this->makePost();
        $other = $this->makePost(['slug' => 'another-news', 'title' => 'অন্য একটি সংবাদ']);

        $approved = Comment::factory()->approved()->create(['commentable_id' => $post->id]);
        $pending  = Comment::factory()->create(['commentable_id' => $post->id]);

        // এই সংবাদের অনুমোদিত মন্তব্য রিপোর্ট করা যায়
        $this->post(route('news.report', [$post->slug, $approved->id]))->assertRedirect();
        $this->assertSame(1, $approved->fresh()->report_count);

        // অন্য সংবাদের slug বা অননুমোদিত মন্তব্য দিয়ে রিপোর্ট করা যায় না
        $this->post(route('news.report', [$other->slug, $approved->id]))->assertNotFound();
        $this->post(route('news.report', [$post->slug, $pending->id]))->assertNotFound();
    }
}
