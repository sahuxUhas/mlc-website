<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** সংবাদ তৈরি/আপডেট/প্রকাশ/শিডিউল ও ট্র্যাশ-রিস্টোর (spec ৪, ২১, ২৮) */
class NewsLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function editor(): User
    {
        return User::factory()->editor()->create();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title'       => 'মহালছড়িতে নতুন স্বাস্থ্য কমপ্লেক্স উদ্বোধন',
            'category_id' => Category::factory()->create()->id,
            'excerpt'     => 'আজ সকালে নতুন কমপ্লেক্স উদ্বোধন করা হয়েছে।',
            'content'     => '<p>বিস্তারিত প্রতিবেদন এখানে রয়েছে, যা যথেষ্ট লম্বা হতে হবে।</p>',
            'status'      => 'published',
        ], $overrides);
    }

    public function test_editor_can_create_a_published_news_item(): void
    {
        $this->actingAs($this->editor())->post('/admin/news', $this->payload());

        $this->assertDatabaseHas('posts', ['title' => 'মহালছড়িতে নতুন স্বাস্থ্য কমপ্লেক্স উদ্বোধন', 'status' => 'published']);
    }

    public function test_slug_is_generated_from_bangla_title_and_is_unique(): void
    {
        $cat = Category::factory()->create();
        $editor = $this->editor();

        $this->actingAs($editor)->post('/admin/news', $this->payload(['category_id' => $cat->id]));
        $this->actingAs($editor)->post('/admin/news', $this->payload(['category_id' => $cat->id, 'title' => 'মহালছড়িতে নতুন স্বাস্থ্য কমপ্লেক্স উদ্বোধন']));

        $slugs = Post::pluck('slug');
        $this->assertCount(2, $slugs->unique());
    }

    public function test_title_is_required(): void
    {
        $this->actingAs($this->editor())
            ->post('/admin/news', $this->payload(['title' => '']))
            ->assertSessionHasErrors('title');
    }

    public function test_category_must_exist(): void
    {
        $this->actingAs($this->editor())
            ->post('/admin/news', $this->payload(['category_id' => 99999]))
            ->assertSessionHasErrors('category_id');
    }

    public function test_editor_can_update_a_news_item(): void
    {
        $post = Post::factory()->create();

        $this->actingAs($this->editor())->put('/admin/news/'.$post->id, $this->payload([
            'category_id' => $post->category_id,
            'title'       => 'হালনাগাদ করা শিরোনাম',
        ]));

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'হালনাগাদ করা শিরোনাম']);
    }

    public function test_draft_status_can_be_changed_to_published(): void
    {
        $post = Post::factory()->draft()->create();

        $this->actingAs($this->editor())->post('/admin/news/'.$post->id.'/status/published');

        $this->assertSame('published', $post->fresh()->status);
    }

    public function test_reporter_cannot_publish_others_news(): void
    {
        $post = Post::factory()->draft()->create();
        $reporter = User::factory()->reporter()->create();

        $this->actingAs($reporter)->post('/admin/news/'.$post->id.'/status/published')->assertForbidden();
    }

    public function test_scheduled_post_is_not_visible_publicly_until_due(): void
    {
        $post = Post::factory()->create([
            'status'       => 'scheduled',
            'scheduled_at' => now()->addDays(2),
            'published_at' => null,
        ]);

        $this->get('/news/'.$post->slug)->assertNotFound();
        $this->assertNull(Post::visible()->whereKey($post->id)->first());
    }

    public function test_trashing_then_restoring_a_news_item(): void
    {
        $post = Post::factory()->create();

        $this->actingAs($this->editor())->delete('/admin/news/'.$post->id);
        $this->assertSoftDeleted('posts', ['id' => $post->id]);

        $this->actingAs($this->editor())->post('/admin/trash/news/'.$post->id.'/restore');
        $this->assertNotSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_bulk_trash_removes_several_posts(): void
    {
        $posts = Post::factory()->count(3)->create();

        $this->actingAs($this->editor())
            ->post('/admin/news/bulk', ['action' => 'trash', 'ids' => $posts->pluck('id')->all()])
            ->assertSessionHas('success');

        $this->assertSame(3, Post::onlyTrashed()->count());
    }
}
