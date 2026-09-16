<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** মিডিয়া আপলোড ও নিরাপত্তা যাচাই (spec ১৩, ২৪, ২৮) */
class MediaUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_admin_can_upload_an_image(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create())
            ->post('/admin/media', ['files' => [UploadedFile::fake()->image('photo.jpg')], 'folder' => 'general'])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Media::count());
    }

    public function test_multiple_files_can_be_uploaded_at_once(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create())
            ->post('/admin/media', ['files' => [
                UploadedFile::fake()->image('a.jpg'),
                UploadedFile::fake()->image('b.png'),
                UploadedFile::fake()->image('c.webp'),
            ], 'folder' => 'news']);

        $this->assertSame(3, Media::count());
    }

    public function test_disallowed_extension_is_rejected_without_500(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create())
            ->post('/admin/media', ['files' => [UploadedFile::fake()->create('virus.exe', 100)], 'folder' => 'general'])
            ->assertSessionHasErrors('files');

        $this->assertSame(0, Media::count());
    }

    public function test_oversized_file_is_rejected(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create())
            ->post('/admin/media', ['files' => [UploadedFile::fake()->image('big.jpg')->size(5000)], 'folder' => 'general'])
            ->assertSessionHasErrors('files');
    }

    public function test_php_file_disguised_as_image_is_rejected(): void
    {
        $file = UploadedFile::fake()->create('shell.php', 10, 'image/jpeg');

        $this->actingAs(User::factory()->superAdmin()->create())
            ->post('/admin/media', ['files' => [$file], 'folder' => 'general'])
            ->assertSessionHasErrors('files');
    }

    public function test_guest_cannot_upload(): void
    {
        $this->post('/admin/media', ['files' => [UploadedFile::fake()->image('x.jpg')]])
            ->assertRedirect('/admin/login');
    }

    public function test_media_can_be_deleted(): void
    {
        $media = Media::factory()->create();

        $this->actingAs(User::factory()->superAdmin()->create())->delete('/admin/media/'.$media->id);

        $this->assertSoftDeleted('media', ['id' => $media->id]);
    }
}
