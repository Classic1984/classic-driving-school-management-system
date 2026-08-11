<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentServiceChargeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $student = Student::factory()->create();
        $service = Service::factory()->create();

        $this->post("/students/{$student->id}/services", ['service_id' => $service->id])
            ->assertRedirect('/login');
    }

    public function test_an_authenticated_user_can_charge_a_student_for_a_service(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $service = Service::factory()->create(['name' => "Learner's Permit", 'price' => 6000]);

        $response = $this->actingAs($user)->post("/students/{$student->id}/services", [
            'service_id' => $service->id,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect("/students/{$student->id}");

        $this->assertDatabaseHas('student_services', [
            'student_id' => $student->id,
            'service_id' => $service->id,
            'price' => 6000,
        ]);
    }

    public function test_a_student_cannot_be_charged_twice_for_the_same_service(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $service = Service::factory()->create();
        $student->studentServices()->create(['service_id' => $service->id, 'price' => $service->price]);

        $response = $this->actingAs($user)->post("/students/{$student->id}/services", [
            'service_id' => $service->id,
        ]);

        $response->assertSessionHasErrors('service_id');
        $this->assertDatabaseCount('student_services', 1);
    }

    public function test_an_inactive_service_cannot_be_charged(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $service = Service::factory()->create(['is_active' => false]);

        $response = $this->actingAs($user)->post("/students/{$student->id}/services", [
            'service_id' => $service->id,
        ]);

        $response->assertSessionHasErrors('service_id');
        $this->assertDatabaseCount('student_services', 0);
    }

    public function test_the_student_page_lists_a_students_service_charges(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $service = Service::factory()->create(['name' => "Driver's License Processing", 'price' => 50000]);
        $student->studentServices()->create(['service_id' => $service->id, 'price' => 50000]);

        $response = $this->actingAs($user)->get("/students/{$student->id}");

        $response->assertOk();
        $response->assertSee("Driver's License Processing");
        $response->assertSee('50,000.00');
    }

    public function test_the_student_page_only_offers_services_not_already_charged(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $charged = Service::factory()->create(['name' => 'Already Charged Service']);
        $available = Service::factory()->create(['name' => 'Available Service']);
        $student->studentServices()->create(['service_id' => $charged->id, 'price' => $charged->price]);

        $response = $this->actingAs($user)->get("/students/{$student->id}");

        $response->assertOk();
        $response->assertSee('Available Service');
        // "Already Charged Service" still appears in the financial
        // overview and services tables, but must not appear in the
        // "charge for a new service" dropdown's options.
        $response->assertSee('Already Charged Service');
        preg_match('/<select id="service_id".*?<\/select>/s', $response->getContent(), $matches);
        $this->assertStringContainsString('Available Service', $matches[0]);
        $this->assertStringNotContainsString('Already Charged Service', $matches[0]);
    }
}
