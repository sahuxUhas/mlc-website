<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Post;
use App\Support\ImageUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * ছবির নিরাপত্তা — ImgBB-এর raw URL কোথাও প্রকাশ না হওয়া এবং
 * signed proxy route দিয়ে নিরাপদভাবে ছবি সার্ভ হওয়া।
 */
class ImageProxyTest extends TestCase
{
    use RefreshDatabase;

    private const REMOTE = 'https://i.ibb.co/abcd1234/photo.jpg';

    public function test_mc_image_hides_the_raw_hosting_url(): void
    {
        $url = mc_image(self::REMOTE);

        $this->assertStringNotContainsString('i.ibb.co', $url);
        $this->assertStringContainsString('/img/', $url);
        $this->assertStringContainsString('s=', $url);
    }

    public function test_local_paths_are_served_from_our_own_storage(): void
    {
        $this->assertStringContainsString('uploads/news/photo.jpg', mc_image('news/photo.jpg'));
        $this->assertStringNotContainsString('/img/', mc_image('news/photo.jpg'));
    }

    public function test_signed_proxy_url_serves_the_image(): void
    {
        Http::fake([
            'i.ibb.co/*' => Http::response('fake-image-bytes', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $url = mc_image(self::REMOTE);

        $this->get($url)
            ->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg')
            ->assertSee('fake-image-bytes', false);
    }

    public function test_proxy_url_without_valid_signature_is_rejected(): void
    {
        $token = ImageUrl::token(self::REMOTE);

        $this->get('/img/'.$token.'?s=invalid-signature')->assertNotFound();
        $this->get('/img/'.$token)->assertNotFound();
    }

    public function test_proxy_refuses_hosts_outside_the_allowlist(): void
    {
        $url = ImageUrl::signedUrl('https://evil.test/photo.jpg');

        Http::fake();

        $this->get($url)->assertNotFound();

        Http::assertNothingSent();
    }

    public function test_proxy_does_not_cache_a_failed_fetch_as_an_image(): void
    {
        Http::fake([
            'i.ibb.co/*' => Http::response('not an image', 200, ['Content-Type' => 'text/html']),
        ]);

        $this->get(mc_image(self::REMOTE))->assertNotFound();
    }

    public function test_public_article_never_prints_the_raw_hosting_url(): void
    {
        $post = Post::factory()->published()->create([
            'featured_image' => self::REMOTE,
            'content'        => '<p>ছবি সহ সংবাদ, যেখানে হোস্টিং লিংক কখনো দেখা যাবে না।</p>',
        ]);

        $response = $this->get('/news/'.$post->slug);

        $response->assertOk();
        $this->assertStringNotContainsString('i.ibb.co', $response->getContent());
        $this->assertStringContainsString('/img/', $response->getContent());
        $this->assertStringNotContainsString(self::REMOTE, $post->featured_image_url);
    }

    public function test_media_library_shows_thumbnail_only_without_raw_url(): void
    {
        Media::factory()->create([
            'disk'          => 'imgbb',
            'provider'      => 'imgbb',
            'path'          => self::REMOTE,
            'provider_url'  => self::REMOTE,
            'provider_id'   => 'abcd1234',
            'thumb_url'     => 'https://i.ibb.co/abcd1234/photo-thumb.jpg',
            'extension'     => 'jpg',
            'file_name'     => 'সংবাদের ছবি.jpg',
        ]);

        $response = $this->actingAs(\App\Models\User::factory()->superAdmin()->create())
            ->get('/admin/media');

        $response->assertOk();

        $html = $response->getContent();

        $this->assertStringNotContainsString('i.ibb.co', $html);
        $this->assertStringNotContainsString('abcd1234', $html);
        $this->assertStringContainsString('সংবাদের ছবি.jpg', $html);
    }

    public function test_content_media_reference_renders_through_the_proxy(): void
    {
        $media = Media::factory()->create(['path' => self::REMOTE, 'provider' => 'imgbb', 'disk' => 'imgbb']);

        $rendered = mc_content('<p>লেখা</p>' . '{{media:' . $media->id . '|ছবির ক্যাপশন}}');

        $this->assertStringContainsString('/img/', $rendered);
        $this->assertStringContainsString('ছবির ক্যাপশন', $rendered);
        $this->assertStringNotContainsString('i.ibb.co', $rendered);
    }

    public function test_missing_media_reference_is_silently_removed(): void
    {
        $this->assertSame('<p>লেখা</p>', mc_content('<p>লেখা</p>{{media:999999}}'));
    }
}
