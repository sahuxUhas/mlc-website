<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * সংবাদ তৈরি/সম্পাদনার নতুন সিস্টেম — ফর্ম, ভ্যালিডেশন, ট্যাগ, ছবি,
 * স্ট্যাটাস/শিডিউল, প্রাকদর্শন ও তাৎক্ষণিক পাবলিক আপডেট।
 */
class NewsEditorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('uploads');
    }

    private function editor(): User
    {
        return User::factory()->editor()->create();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title'          => 'মহালছড়িতে নতুন সড়ক নির্মাণ শুরু',
            'category_id'    => Category::factory()->create()->id,
            'excerpt'        => 'আজ থেকে কাজ শুরু হয়েছে বলে জানিয়েছেন প্রকৌশলী।',
            'content'        => '<p>বিস্তারিত প্রতিবেদনে বলা হয়েছে, দুই কিলোমিটার সড়ক নির্মাণ করা হবে।</p>',
            'action'         => 'save_draft',
            'published_date' => now()->format('Y-m-d'),
            'published_time' => now()->format('H:i'),
        ], $overrides);
    }

    /* ---------------- তৈরি ও স্ট্যাটাস ---------------- */

    public function test_save_draft_button_creates_a_draft(): void
    {
        $this->actingAs($this->editor())->post('/admin/news', $this->payload());

        $post = Post::firstOrFail();
        $this->assertSame('draft', $post->status);
        $this->assertNull($post->published_at);
    }

    public function test_publish_action_publishes_with_given_date_and_time(): void
    {
        $this->actingAs($this->editor())->post('/admin/news', $this->payload([
            'action'         => 'publish',
            'published_date' => now()->subHour()->format('Y-m-d'),
            'published_time' => now()->subHour()->format('H:i'),
        ]));

        $post = Post::firstOrFail();
        $this->assertSame('published', $post->status);
        $this->assertNotNull($post->published_at);
        $this->assertTrue($post->is_live);
    }

    public function test_schedule_action_requires_a_future_datetime(): void
    {
        $editor = $this->editor();

        $this->actingAs($editor)->post('/admin/news', $this->payload(['action' => 'schedule']))
            ->assertSessionHasErrors('scheduled_date');

        $this->assertSame(0, Post::count());

        $this->actingAs($editor)->post('/admin/news', $this->payload([
            'action'         => 'schedule',
            'scheduled_date' => now()->subDay()->format('Y-m-d'),
            'scheduled_time' => '10:00',
        ]))->assertSessionHasErrors('scheduled_time');

        $this->actingAs($editor)->post('/admin/news', $this->payload([
            'action'         => 'schedule',
            'scheduled_date' => now()->addDay()->format('Y-m-d'),
            'scheduled_time' => '09:00',
        ]));

        $this->assertSame('scheduled', Post::firstOrFail()->status);
    }

    public function test_reporter_publish_attempt_is_moved_to_pending(): void
    {
        $reporter = User::factory()->reporter()->create();

        $this->actingAs($reporter)->post('/admin/news', $this->payload(['action' => 'publish']));

        $this->assertSame('pending', Post::firstOrFail()->status);
    }

    public function test_reporter_cannot_edit_another_authors_news(): void
    {
        $post = Post::factory()->draft()->create();

        $this->actingAs(User::factory()->reporter()->create())
            ->get('/admin/news/'.$post->slug.'/edit')
            ->assertForbidden();
    }

    public function test_moderator_cannot_edit_news(): void
    {
        $post = Post::factory()->create();

        $this->actingAs(User::factory()->moderator()->create())
            ->get('/admin/news/'.$post->slug.'/edit')
            ->assertForbidden();
    }

    /* ---------------- ভ্যালিডেশন ---------------- */

    public function test_subcategory_must_belong_to_selected_category(): void
    {
        $category = Category::factory()->create();
        $other = Category::factory()->create();
        $foreignSub = Category::factory()->create(['parent_id' => $other->id]);

        $this->actingAs($this->editor())->post('/admin/news', $this->payload([
            'category_id'    => $category->id,
            'subcategory_id' => $foreignSub->id,
        ]))->assertSessionHasErrors('subcategory_id');

        $this->assertSame(0, Post::count());
    }

    public function test_images_with_disallowed_format_are_rejected(): void
    {
        $this->actingAs($this->editor())->post('/admin/news', $this->payload([
            'images' => [UploadedFile::fake()->create('document.pdf', 40, 'application/pdf')],
        ]))->assertSessionHasErrors('images.0');

        $this->assertSame(0, Post::count());
    }

    public function test_oversized_featured_image_is_rejected(): void
    {
        $this->actingAs($this->editor())->post('/admin/news', $this->payload([
            'featured_image' => UploadedFile::fake()->image('big.jpg')->size(9000),
        ]))->assertSessionHasErrors('featured_image');
    }

    public function test_script_inside_content_is_removed_before_saving(): void
    {
        $this->actingAs($this->editor())->post('/admin/news', $this->payload([
            'content' => '<p>নিরাপদ লেখা এখানে রয়েছে যা যথেষ্ট লম্বা।</p><script>alert(1)</script><p onclick="evil()">আরও লেখা</p>',
        ]));

        $content = Post::firstOrFail()->content;

        $this->assertStringNotContainsString('<script', $content);
        $this->assertStringNotContainsString('onclick', $content);
        $this->assertStringContainsString('নিরাপদ লেখা', $content);
    }

    /* ---------------- ট্যাগ ---------------- */

    public function test_tags_are_created_and_synced_from_the_form(): void
    {
        $this->actingAs($this->editor())->post('/admin/news', $this->payload([
            'tags' => 'মহালছড়ি, সড়ক নির্মাণ, মহালছড়ি',
        ]));

        $post = Post::firstOrFail();

        $this->assertSame(2, Tag::count());
        $this->assertEqualsCanonicalizing(['মহালছড়ি', 'সড়ক নির্মাণ'], $post->tags()->pluck('name')->all());
    }

    public function test_tags_can_be_removed_on_update(): void
    {
        $editor = $this->editor();
        $post = Post::factory()->create(['author_id' => $editor->id]);

        Tag::syncFromString($post, 'পুরোনো ট্যাগ, আরেকটি');

        $this->actingAs($editor)->put('/admin/news/'.$post->slug, $this->payload([
            'category_id' => $post->category_id,
            'title'       => $post->title,
            'tags'        => 'শুধু এই ট্যাগ',
        ]));

        $this->assertSame(['শুধু এই ট্যাগ'], $post->fresh()->tags()->pluck('name')->all());
    }

    /* ---------------- ছবি (ImgBB → DB রেফারেন্স) ---------------- */

    public function test_featured_and_gallery_images_are_stored_as_media_references(): void
    {
        $this->actingAs($this->editor())->post('/admin/news', $this->payload([
            'featured_image' => UploadedFile::fake()->image('featured.jpg', 1200, 675),
            'images'         => [
                UploadedFile::fake()->image('one.jpg', 800, 450),
                UploadedFile::fake()->image('two.png', 800, 450),
            ],
        ]));

        $post = Post::firstOrFail();

        // ছবি DB-তে BLOB নয় — শুধু রেফারেন্স
        $this->assertNotNull($post->featured_image);
        $this->assertNotNull($post->featured_media_id);
        $this->assertInstanceOf(Media::class, $post->featuredMedia);
        $this->assertSame(2, $post->images()->count());
        $this->assertSame(2, PostImage::whereNotNull('media_id')->count());
        $this->assertSame(3, Media::count());

        // পাবলিক URL কখনো raw provider URL নয়
        $this->assertStringNotContainsString('i.ibb.co', $post->featured_image_url);
    }

    public function test_gallery_image_can_be_deleted_along_with_its_media_reference(): void
    {
        $editor = $this->editor();

        $this->actingAs($editor)->post('/admin/news', $this->payload([
            'images' => [UploadedFile::fake()->image('gallery.jpg', 800, 450)],
        ]));

        $post = Post::firstOrFail();
        $image = $post->images()->firstOrFail();

        $this->actingAs($editor)
            ->delete('/admin/news/'.$post->slug.'/images/'.$image->id)
            ->assertSessionHas('success');

        $this->assertSame(0, $post->images()->count());
        $this->assertNull(Media::find($image->media_id));
    }

    public function test_gallery_image_order_can_be_changed(): void
    {
        $editor = $this->editor();
        $post = Post::factory()->create(['author_id' => $editor->id]);

        $first = $post->images()->create(['path' => 'a.jpg', 'sort_order' => 0]);
        $second = $post->images()->create(['path' => 'b.jpg', 'sort_order' => 1]);

        $this->actingAs($editor)
            ->post('/admin/news/'.$post->slug.'/images/reorder', ['order' => [$second->id, $first->id]])
            ->assertSessionHas('success');

        $this->assertSame($second->id, $post->images()->first()->id);
    }

    public function test_ajax_gallery_upload_returns_signed_url_not_raw_url(): void
    {
        $editor = $this->editor();
        $post = Post::factory()->create(['author_id' => $editor->id]);

        $response = $this->actingAs($editor)->post('/admin/news/'.$post->slug.'/images', [
            'images' => [UploadedFile::fake()->image('ajax.jpg', 800, 450)],
        ], ['Accept' => 'application/json']);

        $response->assertOk()->assertJsonPath('success', true);

        $json = $response->json();

        $this->assertArrayHasKey('thumb', $json['images'][0]);
        $this->assertStringNotContainsString('i.ibb.co', json_encode($json, JSON_UNESCAPED_UNICODE));
        $this->assertSame(1, $post->images()->count());
    }

    public function test_content_editor_upload_returns_media_reference(): void
    {
        $editor = $this->editor();

        $response = $this->actingAs($editor)->post('/admin/news/media/upload', [
            'images' => [UploadedFile::fake()->image('inline.jpg', 800, 450)],
        ], ['Accept' => 'application/json']);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertSame('{{media:'.Media::firstOrFail()->id.'}}', $response->json('images.0.key'));
    }

    /* ---------------- প্রাকদর্শন ---------------- */

    public function test_preview_page_renders_draft_news_for_its_author(): void
    {
        $reporter = User::factory()->reporter()->create();
        $post = Post::factory()->draft()->create(['author_id' => $reporter->id]);

        $this->actingAs($reporter)
            ->get('/admin/news/'.$post->slug.'/preview')
            ->assertOk()
            ->assertSee('প্রাকদর্শন', false)
            ->assertSee($post->title, false);
    }

    public function test_preview_page_is_forbidden_for_other_reporters(): void
    {
        $post = Post::factory()->draft()->create();

        $this->actingAs(User::factory()->reporter()->create())
            ->get('/admin/news/'.$post->slug.'/preview')
            ->assertForbidden();
    }

    /* ---------------- সাথে সাথে পাবলিক আপডেট ---------------- */

    public function test_editing_news_flushes_public_caches(): void
    {
        $editor = $this->editor();
        $post = Post::factory()->published()->create(['author_id' => $editor->id]);

        Cache::put('site.home.data', 'ক্যাশ করা হোমপেজ', now()->addMinutes(5));
        Cache::put('site.breaking.active', 'ক্যাশ করা ব্রেকিং', now()->addMinutes(5));

        $this->actingAs($editor)->put('/admin/news/'.$post->slug, $this->payload([
            'category_id' => $post->category_id,
            'title'       => 'সঙ্গে সঙ্গে আপডেট হওয়া শিরোনাম',
            'action'      => 'update',
        ]));

        $this->assertNull(Cache::get('site.home.data'));
        $this->assertNull(Cache::get('site.breaking.active'));

        // পাবলিক সাইটে পরিবর্তন সঙ্গে সঙ্গে দেখা যায়
        $this->get('/news/'.$post->fresh()->slug)->assertSee('সঙ্গে সঙ্গে আপডেট হওয়া শিরোনাম', false);
    }

    public function test_breaking_flag_creates_breaking_entry_and_flushes_cache(): void
    {
        Cache::put('site.breaking.active', 'পুরোনো ক্যাশ', now()->addMinutes(5));

        $this->actingAs($this->editor())->post('/admin/news', $this->payload([
            'action'      => 'publish',
            'is_breaking' => '1',
        ]));

        $post = Post::firstOrFail();

        $this->assertTrue($post->is_breaking);
        $this->assertDatabaseHas('breaking_news', ['post_id' => $post->id]);
        $this->assertNull(Cache::get('site.breaking.active'));
    }

    public function test_news_create_form_shows_no_provider_secrets(): void
    {
        $response = $this->actingAs($this->editor())->get('/admin/news/create');

        $response->assertOk();

        $html = $response->getContent();

        // API Key (.env থেকে) বা raw hosting URL — কোনোটিই পেজে যেতে পারবে না
        $apiKey = (string) config('images.providers.imgbb.key');
        if ($apiKey !== '') {
            $this->assertStringNotContainsString($apiKey, $html);
            $this->assertStringNotContainsString(substr($apiKey, 0, 8), $html);
        }

        $this->assertStringNotContainsString('i.ibb.co', $html);
        $this->assertStringContainsString('tagsField', $html);
        $this->assertStringContainsString('newsPreviewModal', $html);
        $this->assertStringContainsString('name="featured_image"', $html);
        $this->assertStringContainsString('news-editor.js', $html);
    }
}
