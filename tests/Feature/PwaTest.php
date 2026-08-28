<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_dashboard_links_the_manifest_and_registers_the_service_worker(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('rel="manifest"', false);
        $response->assertSee('manifest.webmanifest', false);
        $response->assertSee("navigator.serviceWorker.register('/sw.js')", false);
        $response->assertSee('apple-touch-icon', false);
    }

    public function test_the_login_page_links_the_manifest_too(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('rel="manifest"', false);
        $response->assertSee('manifest.webmanifest', false);
    }
}
