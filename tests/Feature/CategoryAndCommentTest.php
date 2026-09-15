<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** ক্যাটাগরি CRUD ও মন্তব্য মডারেশন (spec ৮, ১২, ২৮) */
class CategoryAndCommentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->superAdmin()->create();
    }

    public function test_category_can_be_created(): void
    {
        $this->actingAs($this->admin())->post('/admin/categories', [
            'name'       => 'খেলাধুলা',
            'is_visible' => '1',
        ]);

        $this->assertDatabaseHas('categories', ['name' => 'খেলাধুলা']);
    }

    public function test_category_name_is_required_and_unique(): void
    {
        Category::factory()->create(['name' => 'খেলাধুলা', 'slug' => 'kheladhula']);

        $this->actingAs($this->admin())->post('/admin/categories', ['name' => ''])->assertSessionHasErrors('name');
        $this->actingAs($this->admin())->post('/admin/categories', ['name' => 'খেলাধুলা'])->assertSessionHasErrors('name');
    }

    public function test_category_can_be_toggled(): void
    {
        $cat = Category::factory()->create(['is_visible' => true]);

        $this->actingAs($this->admin())->post('/admin/categories/'.$cat->id.'/toggle');

        $this->assertFalse((bool) $cat->fresh()->is_visible);
    }

    public function test_visitor_can_comment_without_an_account(): void
    {
        $post = Post::factory()->create();

        $this->post('/comments', [
            'commentable_type' => 'post',
            'commentable_id'   => $post->id,
            'name'             => 'রহিম উদ্দিন',
            'comment'          => 'চমৎকার প্রতিবেদন, ধন্যবাদ।',
        ]);

        // নতুন মন্তব্য ডিফল্টভাবে pending থাকে
        $this->assertDatabaseHas('comments', ['name' => 'রহিম উদ্দিন', 'status' => 'pending']);
    }

    public function test_comment_requires_name_and_body(): void
    {
        $post = Post::factory()->create();

        $this->post('/comments', ['commentable_type' => 'post', 'commentable_id' => $post->id])
            ->assertSessionHasErrors(['name', 'comment']);
    }

    public function test_pending_comment_is_not_shown_publicly(): void
    {
        $post = Post::factory()->create();
        Comment::factory()->create(['commentable_id' => $post->id, 'status' => 'pending']);

        $this->get('/news/'.$post->slug)->assertOk()->assertDontSee('চমৎকার');
    }

    public function test_moderator_can_approve_a_comment(): void
    {
        $comment = Comment::factory()->create(['status' => 'pending']);
        $moderator = User::factory()->moderator()->create();

        $this->actingAs($moderator)->post('/admin/comments/'.$comment->id.'/status/approved');

        $this->assertSame('approved', $comment->fresh()->status);
    }

    public function test_moderator_can_mark_comment_as_spam(): void
    {
        $comment = Comment::factory()->create();
        $moderator = User::factory()->moderator()->create();

        $this->actingAs($moderator)->post('/admin/comments/'.$comment->id.'/status/spam');

        $this->assertSame('spam', $comment->fresh()->status);
    }

    public function test_reporter_cannot_moderate_comments(): void
    {
        $comment = Comment::factory()->create();

        $this->actingAs(User::factory()->reporter()->create())
            ->post('/admin/comments/'.$comment->id.'/status/approved')
            ->assertForbidden();
    }
}
