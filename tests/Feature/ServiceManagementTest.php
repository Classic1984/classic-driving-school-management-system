<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/services')->assertRedirect('/login');
    }

    public function test_a_secretary_cannot_manage_services(): void
    {
        $secretary = User::factory()->secretary()->create();

        $this->actingAs($secretary)->get('/services')->assertForbidden();
        $this->actingAs($secretary)->get('/services/create')->assertForbidden();
        $this->actingAs($secretary)->post('/services', ['name' => 'Test', 'price' => 1000])->assertForbidden();
    }

    public function test_an_admin_cannot_manage_services(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/services')->assertForbidden();
    }

    public function test_a_director_can_view_the_service_list(): void
    {
        $director = User::factory()->director()->create();
        Service::factory()->create(['name' => "Driver's License Processing"]);

        $this->actingAs($director)
            ->get('/services')
            ->assertOk()
            ->assertSee("Driver's License Processing");
    }

    public function test_a_director_can_add_a_new_service(): void
    {
        $director = User::factory()->director()->create();

        $this->actingAs($director)
            ->post('/services', [
                'name' => 'Online Certificate',
                'price' => 15000,
                'is_active' => '1',
            ])
            ->assertRedirect(route('services.index'));

        $this->assertDatabaseHas('services', [
            'name' => 'Online Certificate',
            'price' => 15000,
            'is_active' => true,
        ]);
    }

    public function test_omitting_the_active_checkbox_creates_an_inactive_service(): void
    {
        $director = User::factory()->director()->create();

        $this->actingAs($director)->post('/services', [
            'name' => 'Online Certificate',
            'price' => 15000,
        ]);

        $this->assertDatabaseHas('services', [
            'name' => 'Online Certificate',
            'is_active' => false,
        ]);
    }

    public function test_a_service_name_must_be_unique(): void
    {
        $director = User::factory()->director()->create();
        Service::factory()->create(['name' => 'Online Certificate']);

        $this->actingAs($director)
            ->post('/services', ['name' => 'Online Certificate', 'price' => 15000])
            ->assertSessionHasErrors('name');
    }

    public function test_a_director_can_update_a_service(): void
    {
        $director = User::factory()->director()->create();
        $service = Service::factory()->create(['name' => 'Online Certificate', 'price' => 15000, 'is_active' => true]);

        $this->actingAs($director)
            ->put("/services/{$service->id}", [
                'name' => 'Online Certificate',
                'price' => 18000,
            ])
            ->assertRedirect(route('services.index'));

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'price' => 18000,
            'is_active' => false,
        ]);
    }

    public function test_updating_a_service_can_keep_its_own_name(): void
    {
        $director = User::factory()->director()->create();
        $service = Service::factory()->create(['name' => 'Online Certificate']);

        $this->actingAs($director)
            ->put("/services/{$service->id}", [
                'name' => 'Online Certificate',
                'price' => 18000,
                'is_active' => '1',
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_a_director_can_set_a_services_processing_days(): void
    {
        $director = User::factory()->director()->create();

        $this->actingAs($director)->post('/services', [
            'name' => "Driver's License Processing",
            'price' => 50000,
            'processing_days' => 30,
        ]);

        $this->assertDatabaseHas('services', [
            'name' => "Driver's License Processing",
            'processing_days' => 30,
        ]);
    }

    public function test_processing_days_is_optional(): void
    {
        $director = User::factory()->director()->create();

        $this->actingAs($director)
            ->post('/services', ['name' => "Learner's Permit", 'price' => 6000])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('services', [
            'name' => "Learner's Permit",
            'processing_days' => null,
        ]);
    }

    public function test_processing_days_must_be_a_positive_integer(): void
    {
        $director = User::factory()->director()->create();

        $this->actingAs($director)
            ->post('/services', ['name' => 'Online Certificate', 'price' => 15000, 'processing_days' => 0])
            ->assertSessionHasErrors('processing_days');
    }
}
