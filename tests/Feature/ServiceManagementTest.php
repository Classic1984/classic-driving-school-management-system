<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Student;
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

    public function test_the_index_defaults_to_alphabetical_order_and_can_be_reversed(): void
    {
        $director = User::factory()->director()->create();
        Service::factory()->create(['name' => 'Zebra Service']);
        Service::factory()->create(['name' => 'Alpha Service']);

        $this->actingAs($director)
            ->get('/services')
            ->assertOk()
            ->assertSeeInOrder(['Alpha Service', 'Zebra Service']);

        $this->actingAs($director)
            ->get('/services?sort=desc')
            ->assertOk()
            ->assertSeeInOrder(['Zebra Service', 'Alpha Service']);
    }

    public function test_the_summary_counts_reflect_active_and_inactive_services(): void
    {
        $director = User::factory()->director()->create();
        Service::factory()->create(['name' => 'Active One', 'is_active' => true, 'processing_days' => 10]);
        Service::factory()->create(['name' => 'Active Two', 'is_active' => true, 'processing_days' => 30]);
        Service::factory()->create(['name' => 'Inactive One', 'is_active' => false, 'processing_days' => 5]);

        $response = $this->actingAs($director)->get('/services');

        $response->assertOk();
        $response->assertSeeInOrder(['3', 'Total Services']);
        $response->assertSeeInOrder(['2', 'Active Services']);
        $response->assertSeeInOrder(['1', 'Inactive Service']);
        // Average of only the two active services' processing days (10, 30),
        // ignoring the inactive service's 5.
        $response->assertSeeInOrder(['20', 'Avg. Processing Days']);
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

    public function test_a_director_can_delete_a_service_that_has_never_been_charged(): void
    {
        $director = User::factory()->director()->create();
        $service = Service::factory()->create(['name' => 'Duplicate Entry']);

        $this->actingAs($director)
            ->delete("/services/{$service->id}")
            ->assertRedirect(route('services.index'));

        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    public function test_a_service_already_charged_to_a_student_cannot_be_deleted(): void
    {
        $director = User::factory()->director()->create();
        $service = Service::factory()->create(['name' => "Driver's License Processing"]);
        $student = Student::factory()->create();
        $student->studentServices()->create(['service_id' => $service->id, 'price' => $service->price]);

        $response = $this->actingAs($director)->delete("/services/{$service->id}");

        $response->assertRedirect(route('services.index'));
        $this->assertDatabaseHas('services', ['id' => $service->id]);
        $this->assertDatabaseHas('student_services', ['student_id' => $student->id, 'service_id' => $service->id]);
    }

    public function test_the_blocked_delete_message_names_the_charged_student(): void
    {
        $director = User::factory()->director()->create();
        $service = Service::factory()->create(['name' => 'School Certificate']);
        $student = Student::factory()->create(['name' => 'Charged Student']);
        $student->studentServices()->create(['service_id' => $service->id, 'price' => $service->price]);

        $this->actingAs($director)->delete("/services/{$service->id}");

        $response = $this->actingAs($director)->get('/services');

        $response->assertOk();
        $response->assertSee('Charged Student');
        $response->assertSee(route('students.show', $student), false);
    }

    public function test_a_secretary_cannot_delete_a_service(): void
    {
        $secretary = User::factory()->secretary()->create();
        $service = Service::factory()->create();

        $this->actingAs($secretary)->delete("/services/{$service->id}")->assertForbidden();
        $this->assertDatabaseHas('services', ['id' => $service->id]);
    }
}
