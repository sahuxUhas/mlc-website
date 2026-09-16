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
            'guest_name'       => 'রহিম উদ্দিন',
            'body'             => 'চমৎকার প্রতিবেদন, ধন্যবাদ।',
        ]);

        // নতুন মন্তব্য ডিফল্টভাবে pending থাকে (মডারেশনের অপেক্ষায়)
        $this->assertDatabaseHas('comments', ['guest_name' => 'রহিম উদ্দিন', 'status' => 'pending']);
    }

    public function test_comment_submission_shows_moderation_confirmation(): void
    {
        $post = Post::factory()->create();

        $this->post('/comments', [
            'commentable_type' => 'post',
            'commentable_id'   => $post->id,
            'guest_name'       => 'রহিম উদ্দিন',
            'body'             => 'মন্তব্য পাঠানোর কনফার্মেশন যাচাই।',
        ])->assertRedirect();

        $this->get('/news/'.$post->slug)
            ->assertOk()
            ->assertSee('আপনার মন্তব্য পর্যালোচনার জন্য পাঠানো হয়েছে।');
    }

    public function test_comment_requires_name_and_body(): void
    {
        $post = Post::factory()->create();

        $this->post('/comments', ['commentable_type' => 'post', 'commentable_id' => $post->id])
            ->assertSessionHasErrors(['guest_name', 'body']);

        // খুব ছোট নাম/মন্তব্য এবং সীমার বেশি দৈর্ঘ্যও প্রত্যাখ্যাত হয়
        $this->post('/comments', [
            'commentable_type' => 'post',
            'commentable_id'   => $post->id,
            'guest_name'       => 'অ',
            'body'             => 'ঠিক',
        ])->assertSessionHasErrors(['guest_name', 'body']);

        $this->post('/comments', [
            'commentable_type' => 'post',
            'commentable_id'   => $post->id,
            'guest_name'       => str_repeat('ক', 61),
            'body'             => str_repeat('খ', 2001),
        ])->assertSessionHasErrors(['guest_name', 'body']);
    }

    public function test_comment_body_is_stored_without_html_injection(): void
    {
        $post = Post::factory()->create();

        $this->post('/comments', [
            'commentable_type' => 'post',
            'commentable_id'   => $post->id,
            'guest_name'       => 'রহিম উদ্দিন',
            'body'             => '<script>alert(1)</script> মন্তব্য',
        ]);

        $comment = Comment::where('commentable_id', $post->id)->firstOrFail();
        $comment->update(['status' => 'approved']);

        // মার্কআপ হিসেবে রেন্ডার হবে না — এস্কেপ হয়ে প্লেইন টেক্সট হিসেবে দেখাবে
        $this->get('/news/'.$post->slug)
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_only_approved_comments_are_shown_publicly(): void
    {
        $post = Post::factory()->create();
        Comment::factory()->create(['commentable_id' => $post->id, 'body' => 'অননুমোদিত মন্তব্য পাঠ', 'status' => 'pending']);
        Comment::factory()->create(['commentable_id' => $post->id, 'body' => 'প্রত্যাখ্যাত মন্তব্য পাঠ', 'status' => 'rejected']);
        Comment::factory()->create(['commentable_id' => $post->id, 'body' => 'স্প্যাম মন্তব্য পাঠ', 'status' => 'spam']);

        $this->get('/news/'.$post->slug)
            ->assertOk()
            ->assertDontSee('অননুমোদিত মন্তব্য পাঠ')
            ->assertDontSee('প্রত্যাখ্যাত মন্তব্য পাঠ')
            ->assertDontSee('স্প্যাম মন্তব্য পাঠ')
            ->assertSee('এখনও কোনো মন্তব্য নেই। প্রথম মন্তব্যটি করুন।');
    }

    public function test_approved_comment_is_shown_publicly(): void
    {
        $post = Post::factory()->create();
        Comment::factory()->approved()->create(['commentable_id' => $post->id, 'body' => 'অভিনন্দন ও শুভেচ্ছা পাঠ।']);

        $this->get('/news/'.$post->slug)
            ->assertOk()
            ->assertSee('অভিনন্দন ও শুভেচ্ছা পাঠ।')
            ->assertDontSee('এখনও কোনো মন্তব্য নেই।');
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
