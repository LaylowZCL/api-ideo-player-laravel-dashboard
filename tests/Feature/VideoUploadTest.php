<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use App\Http\Middleware\VerifyCsrfToken;
use Tests\TestCase;

class VideoUploadTest extends TestCase
{
    use RefreshDatabase;

    private function ensurePublicVideosDir(): void
    {
        $dir = public_path('videos');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
    }

    private function createUser(): User
    {
        return User::factory()->create();
    }

    public function test_upload_allows_request_without_csrf_in_tests(): void
    {
        $this->ensurePublicVideosDir();
        $user = $this->createUser();

        $file = UploadedFile::fake()->create('sample.mp4', 1, 'video/mp4');

        $response = $this
            ->actingAs($user)
            ->withMiddleware([VerifyCsrfToken::class])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->post('/api/videos/upload', [
                'title' => 'Teste',
                'description' => 'Upload sem CSRF',
                'duration_seconds' => 60,
                'video' => $file,
            ]);

        $response->assertStatus(200);
    }

    public function test_upload_succeeds_with_valid_payload(): void
    {
        $this->ensurePublicVideosDir();
        $user = $this->createUser();

        Session::start();
        $token = csrf_token();

        $file = UploadedFile::fake()->create('sample.mp4', 1, 'video/mp4');

        $response = $this
            ->actingAs($user)
            ->withMiddleware([VerifyCsrfToken::class])
            ->withHeader('X-CSRF-TOKEN', $token)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->post('/api/videos/upload', [
                '_token' => $token,
                'title' => 'Teste',
                'description' => 'Upload válido',
                'duration_seconds' => 60,
                'video' => $file,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $videoName = data_get($response->json(), 'video.name');
        $this->assertNotEmpty($videoName);

        $filePath = storage_path('app/public/videos/' . $videoName);
        $this->assertTrue(File::exists($filePath));

        File::delete($filePath);
    }

    public function test_upload_rejects_invalid_mime(): void
    {
        $this->ensurePublicVideosDir();
        $user = $this->createUser();

        Session::start();
        $token = csrf_token();

        $file = UploadedFile::fake()->create('sample.txt', 1, 'text/plain');

        $response = $this
            ->actingAs($user)
            ->withMiddleware([VerifyCsrfToken::class])
            ->withHeader('X-CSRF-TOKEN', $token)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->post('/api/videos/upload', [
                '_token' => $token,
                'title' => 'Teste inválido',
                'description' => 'Mime inválido',
                'video' => $file,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['video']);
    }
}
