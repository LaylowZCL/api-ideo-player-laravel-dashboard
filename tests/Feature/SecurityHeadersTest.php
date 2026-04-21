<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth'])->get('/security-test', function () {
            return response('ok');
        });
    }

    public function test_login_page_sends_security_headers(): void
    {
        $response = $this
            ->withHeader('X-Forwarded-Proto', 'https')
            ->get('/login');

        $response->assertOk();
        $response->assertHeader('Content-Security-Policy');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->assertHeader('Cache-Control', 'must-revalidate, no-cache, no-store, private');
        $response->assertHeader('Pragma', 'no-cache');
        $response->assertHeader('Expires', '0');
    }

    public function test_authenticated_web_routes_are_not_cacheable(): void
    {
        $user = new User([
            'id' => 1,
            'name' => 'Security Tester',
            'email' => 'security@example.test',
            'user_type' => 'manager',
            'role' => 'manager',
        ]);

        $response = $this
            ->actingAs($user)
            ->withHeader('X-Forwarded-Proto', 'https')
            ->get('/security-test');

        $response->assertOk();
        $response->assertHeader('Cache-Control', 'must-revalidate, no-cache, no-store, private');
        $response->assertHeader('Content-Security-Policy');
    }
}
