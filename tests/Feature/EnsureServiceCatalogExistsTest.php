<?php

namespace Tests\Feature;

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureServiceCatalogExistsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_every_default_catalog_service_on_an_empty_database(): void
    {
        $this->artisan('app:ensure-service-catalog-exists')->assertExitCode(0);

        $this->assertDatabaseCount('services', 3);
        $this->assertDatabaseHas('services', ['name' => "Driver's License Processing", 'price' => 50000, 'is_active' => true]);
        $this->assertDatabaseHas('services', ['name' => "Learner's Permit", 'price' => 6000, 'is_active' => true]);
        $this->assertDatabaseHas('services', ['name' => 'Online Certificate', 'price' => 20000, 'is_active' => true]);
    }

    public function test_it_never_touches_a_price_a_director_already_customized(): void
    {
        Service::factory()->create(['name' => "Driver's License Processing", 'price' => 75000]);

        $this->artisan('app:ensure-service-catalog-exists')->assertExitCode(0);

        $this->assertDatabaseHas('services', ['name' => "Driver's License Processing", 'price' => 75000]);
        $this->assertDatabaseCount('services', 3);
    }

    public function test_running_it_twice_does_not_duplicate_anything(): void
    {
        $this->artisan('app:ensure-service-catalog-exists');
        $this->artisan('app:ensure-service-catalog-exists');

        $this->assertDatabaseCount('services', 3);
    }
}
