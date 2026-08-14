<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_vehicle_routes(): void
    {
        $vehicle = Vehicle::factory()->create();

        $this->get('/vehicles')->assertRedirect('/login');
        $this->get('/vehicles/create')->assertRedirect('/login');
        $this->get("/vehicles/{$vehicle->id}")->assertRedirect('/login');
        $this->get("/vehicles/{$vehicle->id}/edit")->assertRedirect('/login');
        $this->post('/vehicles', [])->assertRedirect('/login');
        $this->put("/vehicles/{$vehicle->id}", [])->assertRedirect('/login');
        $this->delete("/vehicles/{$vehicle->id}")->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_vehicle_index(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['name' => 'Toyota Corolla', 'plate_number' => 'ABC-123XY']);

        $response = $this->actingAs($user)->get('/vehicles');

        $response->assertOk();
        $response->assertSee('Toyota Corolla');
    }

    public function test_authenticated_user_can_view_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/vehicles/create');

        $response->assertOk();
    }

    public function test_authenticated_user_can_store_a_vehicle(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Honda Civic',
            'plate_number' => 'XYZ-987AB',
            'status' => 'active',
        ];

        $response = $this->actingAs($user)->post('/vehicles', $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/vehicles');

        $this->assertDatabaseHas('vehicles', [
            'name' => 'Honda Civic',
            'plate_number' => 'XYZ-987AB',
        ]);
    }

    public function test_storing_a_vehicle_requires_valid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/vehicles', [
            'name' => '',
            'plate_number' => '',
            'status' => 'invalid-status',
        ]);

        $response->assertSessionHasErrors(['name', 'plate_number', 'status']);
        $this->assertDatabaseCount('vehicles', 0);
    }

    public function test_storing_a_vehicle_requires_unique_plate_number(): void
    {
        $user = User::factory()->create();
        Vehicle::factory()->create(['plate_number' => 'DUP-100XY']);

        $response = $this->actingAs($user)->post('/vehicles', [
            'name' => 'New Vehicle',
            'plate_number' => 'DUP-100XY',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('plate_number');
        $this->assertDatabaseCount('vehicles', 1);
    }

    public function test_authenticated_user_can_view_a_vehicle(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $response = $this->actingAs($user)->get("/vehicles/{$vehicle->id}");

        $response->assertOk();
        $response->assertSee($vehicle->name);
    }

    public function test_authenticated_user_can_update_a_vehicle(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)->put("/vehicles/{$vehicle->id}", [
            'name' => 'New Name',
            'plate_number' => $vehicle->plate_number,
            'status' => 'inactive',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/vehicles');

        $this->assertSame('New Name', $vehicle->fresh()->name);
        $this->assertSame('inactive', $vehicle->fresh()->status);
    }

    public function test_authenticated_user_can_delete_a_vehicle(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $response = $this->actingAs($user)->delete("/vehicles/{$vehicle->id}");

        $response->assertRedirect('/vehicles');
        $this->assertDatabaseMissing('vehicles', ['id' => $vehicle->id]);
    }
}
