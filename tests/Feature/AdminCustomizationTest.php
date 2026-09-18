<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * অ্যাডমিন প্যানেল থেকে সাইট কাস্টমাইজেশন —
 * সেটিংস (নাম/লোগো/হেডার/ফুটার/সোশ্যাল), মেইনটেন্যান্স মোড,
 * প্রতি পেজে সংবাদ ও মেনু ম্যানেজার।
 */
class AdminCustomizationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->superAdmin()->create();
    }

    public function test_settings_page_lists_every_editable_group(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee('লোগোর পাশের নাম — প্রথম অংশ')   // site_name_a
            ->assertSee('মেইনটেন্যান্স মোড')
            ->assertSee('সোশ্যাল লিংক তালিকা');
    }

    public function test_header_brand_name_fields_are_saved(): void
    {
        $this->actingAs($this->admin())->put('/admin/settings', [
            'site_name'   => 'মহালছড়ি নিউজ',
            'site_name_a' => 'মহালছড়ি',
            'site_name_b' => 'নিউজ',
        ])->assertSessionHas('success');

        $this->assertSame('মহালছড়ি', Setting::get('site_name_a'));
        $this->assertSame('নিউজ', Setting::get('site_name_b'));

        $this->get('/')->assertOk()->assertSee('মহালছড়ি')->assertSee('নিউজ');
    }

    public function test_logo_can_be_set_by_url_without_uploading_a_file(): void
    {
        $this->actingAs($this->admin())->put('/admin/settings', [
            'site_logo_url' => 'https://i.ibb.co/example/logo.png',
        ]);

        $this->assertSame('https://i.ibb.co/example/logo.png', Setting::get('site_logo'));
        $this->get('/')->assertOk()->assertSee('https://i.ibb.co/example/logo.png', false);
    }

    public function test_social_repeater_links_are_saved_and_shown_in_footer(): void
    {
        $this->actingAs($this->admin())->put('/admin/settings', [
            'social' => [
                'label' => ['ফেসবুক', ''],
                'icon'  => ['ph-facebook-logo', ''],
                'url'   => ['https://facebook.com/mahalcharinews', ''],
                'color' => ['#1877F2', ''],
            ],
        ]);

        $links = mc_social_links();

        $this->assertCount(1, $links, 'খালি URL এর সারি বাদ পড়বে');
        $this->assertSame('https://facebook.com/mahalcharinews', $links[0]['url']);
        $this->assertSame('#1877F2', $links[0]['color']);

        $this->get('/')->assertOk()->assertSee('https://facebook.com/mahalcharinews', false);
    }

    public function test_header_and_footer_elements_can_be_hidden(): void
    {
        // ডিফল্ট: সব চালু
        $this->get('/')->assertOk()->assertSee('data-mc-theme-toggle', false);

        $this->actingAs($this->admin())->put('/admin/settings', [
            'header_show_theme_toggle' => '0',
            'header_show_search'       => '0',
            'date_strip_enabled'       => '0',
            'footer_show_social'       => '0',
            'footer_show_categories'   => '0',
            'footer_heading_links'     => 'দরকারি লিংক',
        ]);

        $response = $this->get('/')->assertOk();
        $response->assertDontSee('data-mc-theme-toggle', false);
        $response->assertDontSee('data-mc-search-open', false);
        $response->assertDontSee('data-mc-clock-live', false);
        $response->assertDontSee('বিভাগসমূহ');
        $response->assertSee('দরকারি লিংক');
    }

    public function test_maintenance_mode_blocks_guests_but_not_admins(): void
    {
        $this->actingAs($this->admin())->put('/admin/settings', [
            'site_maintenance'    => '1',
            'maintenance_message' => 'আপডেট চলছে, একটু পরে আসুন।',
        ]);

        $guest = $this->get('/');
        $guest->assertStatus(503)->assertSee('আপডেট চলছে, একটু পরে আসুন।');

        // অ্যাডমিন প্যানেল খোলা থাকবে — না হলে মোড বন্ধ করা যাবে না
        $this->actingAs($this->admin())->get('/admin')->assertOk();
        $this->actingAs($this->admin())->get('/admin/settings')->assertOk();
    }

    public function test_posts_per_page_setting_controls_pagination(): void
    {
        Post::factory()->count(7)->create();

        // সেটিংস ছাড়া ডিফল্ট ১৫
        $this->assertSame(15, mc_per_page());

        $this->actingAs($this->admin())->put('/admin/settings', ['posts_per_page' => '5']);

        $this->assertSame(5, mc_per_page());

        $firstPage = $this->get('/latest-news')->assertOk();

        // ৭টির মধ্যে ৫টি প্রথম পেজে → দ্বিতীয় পেজের লিংক তৈরি হবে
        $firstPage->assertSee('page=2', false);

        $secondPage = $this->get('/latest-news?page=2')->assertOk();
        $secondPage->assertSee(Post::published()->latestFirst()->skip(5)->first()->title);
    }

    public function test_menu_manager_accepts_custom_url_and_prefixed_reference(): void
    {
        $category = Category::factory()->create();
        $page     = Page::create(['title' => 'গোপনীয়তা নীতি', 'content' => 'বিবরণ']);

        // ফর্ম যেভাবে পাঠায়: link_type = 'url', reference_id = 'c{id}'
        $this->actingAs($this->admin())->post('/admin/menus', [
            'location'     => 'footer',
            'label'        => 'নীতিমালা',
            'link_type'    => 'url',
            'url'          => '/privacy-policy',
            'reference_id' => 'c'.$category->id,
            'is_enabled'   => '1',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $menu = Menu::where('label', 'নীতিমালা')->firstOrFail();
        $this->assertSame('internal', $menu->link_type, "'url' → 'internal' নর্মালাইজ হওয়া দরকার");
        $this->assertSame($category->id, (int) $menu->reference_id, "'c12' থেকে শুধু আইডি রাখতে হবে");
        $this->assertSame('/privacy-policy', $menu->href);

        // পেজ রেফারেন্সও একইভাবে কাজ করবে
        $this->actingAs($this->admin())->post('/admin/menus', [
            'location'     => 'footer',
            'label'        => 'পেজ লিংক',
            'link_type'    => 'page',
            'reference_id' => 'p'.$page->id,
            'is_enabled'   => '1',
        ])->assertSessionHasNoErrors();

        $pageMenu = Menu::where('label', 'পেজ লিংক')->firstOrFail();
        $this->assertSame(route('page.show', $page->slug), $pageMenu->href);
    }

    public function test_footer_menu_items_are_seeded_into_footer_location(): void
    {
        $this->seed(\Database\Seeders\MenuSeeder::class);

        $footerLabels = Menu::where('location', 'footer')->pluck('label')->all();

        $this->assertContains('প্রাইভেসি পলিসি', $footerLabels);
        $this->assertContains('শর্তাবলি', $footerLabels);
    }
}
