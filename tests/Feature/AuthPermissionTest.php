<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** অ্যাডমিন লগইন ও ভূমিকা-ভিত্তিক অনুমতি (spec ২০, ২৮) */
class AuthPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_is_reachable(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('লগইন');
    }

    public function test_guest_is_redirected_from_admin_dashboard(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_valid_credentials_log_an_admin_in(): void
    {
        $user = User::factory()->superAdmin()->create(['password' => bcrypt('ChangeMe@123')]);

        $this->post('/admin/login', ['email' => $user->email, 'password' => 'ChangeMe@123'])
            ->assertRedirect('/admin');

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->post('/admin/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()->inactive()->create(['password' => bcrypt('ChangeMe@123')]);

        $this->post('/admin/login', ['email' => $user->email, 'password' => 'ChangeMe@123'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_reporter_cannot_manage_users(): void
    {
        $reporter = User::factory()->reporter()->create();

        $this->actingAs($reporter)->get('/admin/users')->assertForbidden();
    }

    public function test_moderator_cannot_create_news(): void
    {
        $moderator = User::factory()->moderator()->create();

        $this->actingAs($moderator)->get('/admin/news/create')->assertForbidden();
    }

    public function test_super_admin_can_reach_every_module(): void
    {
        $admin = User::factory()->superAdmin()->create();

        foreach (['/admin', '/admin/news', '/admin/categories', '/admin/users', '/admin/media',
                  '/admin/comments', '/admin/ads', '/admin/videos', '/admin/announcements',
                  '/admin/breaking', '/admin/menus', '/admin/pages', '/admin/settings',
                  '/admin/trash', '/admin/activity', '/admin/albums', '/admin/reporters'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_logout_ends_the_session(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest();
    }
}
