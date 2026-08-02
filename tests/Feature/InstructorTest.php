<?php

namespace Tests\Feature;

use App\Models\Instructor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_instructor_routes(): void
    {
        $instructor = Instructor::factory()->create();

        $this->get('/instructors')->assertRedirect('/login');
        $this->get('/instructors/create')->assertRedirect('/login');
        $this->get("/instructors/{$instructor->id}")->assertRedirect('/login');
        $this->get("/instructors/{$instructor->id}/edit")->assertRedirect('/login');
        $this->post('/instructors', [])->assertRedirect('/login');
        $this->put("/instructors/{$instructor->id}", [])->assertRedirect('/login');
        $this->delete("/instructors/{$instructor->id}")->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_instructor_index(): void
    {
        $user = User::factory()->create();
        $instructor = Instructor::factory()->create(['name' => 'Jane Trainer']);

        $response = $this->actingAs($user)->get('/instructors');

        $response->assertOk();
        $response->assertSee('Jane Trainer');
    }

    public function test_authenticated_user_can_view_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/instructors/create');

        $response->assertOk();
    }

    public function test_authenticated_user_can_store_an_instructor(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'John Trainer',
            'email' => 'john.trainer@example.com',
            'phone' => '555-0100',
            'license_number' => 'INS-12345',
            'specialization' => 'manual',
            'hire_date' => '2020-01-15',
            'status' => 'active',
        ];

        $response = $this->actingAs($user)->post('/instructors', $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/instructors');

        $this->assertDatabaseHas('instructors', [
            'name' => 'John Trainer',
            'email' => 'john.trainer@example.com',
        ]);
    }

    public function test_storing_an_instructor_requires_valid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/instructors', [
            'name' => '',
            'email' => 'not-an-email',
            'phone' => '',
            'specialization' => 'invalid-specialization',
            'hire_date' => '',
            'status' => 'invalid-status',
        ]);

        $response->assertSessionHasErrors([
            'name', 'email', 'phone', 'specialization', 'hire_date', 'status',
        ]);

        $this->assertDatabaseCount('instructors', 0);
    }

    public function test_storing_an_instructor_requires_unique_email(): void
    {
        $user = User::factory()->create();
        Instructor::factory()->create(['email' => 'duplicate@example.com']);

        $response = $this->actingAs($user)->post('/instructors', [
            'name' => 'New Instructor',
            'email' => 'duplicate@example.com',
            'phone' => '555-0100',
            'specialization' => 'manual',
            'hire_date' => '2020-01-15',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('instructors', 1);
    }

    public function test_authenticated_user_can_view_an_instructor(): void
    {
        $user = User::factory()->create();
        $instructor = Instructor::factory()->create();

        $response = $this->actingAs($user)->get("/instructors/{$instructor->id}");

        $response->assertOk();
        $response->assertSee($instructor->name);
    }

    public function test_authenticated_user_can_update_an_instructor(): void
    {
        $user = User::factory()->create();
        $instructor = Instructor::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)->put("/instructors/{$instructor->id}", [
            'name' => 'New Name',
            'email' => $instructor->email,
            'phone' => $instructor->phone,
            'license_number' => $instructor->license_number,
            'specialization' => $instructor->specialization,
            'hire_date' => $instructor->hire_date->format('Y-m-d'),
            'status' => 'inactive',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/instructors');

        $this->assertSame('New Name', $instructor->fresh()->name);
        $this->assertSame('inactive', $instructor->fresh()->status);
    }

    public function test_authenticated_user_can_delete_an_instructor(): void
    {
        $user = User::factory()->create();
        $instructor = Instructor::factory()->create();

        $response = $this->actingAs($user)->delete("/instructors/{$instructor->id}");

        $response->assertRedirect('/instructors');
        $this->assertDatabaseMissing('instructors', ['id' => $instructor->id]);
    }
}
