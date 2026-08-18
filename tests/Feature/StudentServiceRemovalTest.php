<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Service;
use App\Models\Student;
use App\Models\StudentService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentServiceRemovalTest extends TestCase
{
    use RefreshDatabase;

    protected function chargeStudent(Student $student, Service $service): StudentService
    {
        return $student->studentServices()->create(['service_id' => $service->id, 'price' => $service->price]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $student = Student::factory()->create();
        $service = Service::factory()->create();
        $studentService = $this->chargeStudent($student, $service);

        $this->delete("/student-services/{$studentService->id}")->assertRedirect('/login');
    }

    public function test_a_secretary_cannot_remove_a_service_charge(): void
    {
        $secretary = User::factory()->secretary()->create();
        $student = Student::factory()->create();
        $service = Service::factory()->create();
        $studentService = $this->chargeStudent($student, $service);

        $this->actingAs($secretary)->delete("/student-services/{$studentService->id}")->assertForbidden();
        $this->assertDatabaseHas('student_services', ['id' => $studentService->id]);
    }

    public function test_an_admin_cannot_remove_a_service_charge(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create();
        $service = Service::factory()->create();
        $studentService = $this->chargeStudent($student, $service);

        $this->actingAs($admin)->delete("/student-services/{$studentService->id}")->assertForbidden();
    }

    public function test_a_director_can_remove_an_unpaid_unprocessed_service_charge(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create(['name' => 'Jane Doe']);
        $service = Service::factory()->create(['name' => "Driver's License"]);
        $studentService = $this->chargeStudent($student, $service);

        $response = $this->actingAs($director)->delete("/student-services/{$studentService->id}");

        $response->assertRedirect(route('students.show', $student));
        $this->assertDatabaseMissing('student_services', ['id' => $studentService->id]);
        $this->assertDatabaseHas('activity_logs', [
            'description' => "Removed Jane Doe's charge for Driver's License (no payments or processing recorded)",
        ]);
    }

    public function test_it_refuses_to_remove_a_service_charge_with_a_payment_recorded(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        $service = Service::factory()->create(['price' => 50000]);
        $studentService = $this->chargeStudent($student, $service);
        $payment = Payment::factory()->create(['student_id' => $student->id, 'course_id' => null, 'status' => 'paid']);
        PaymentAllocation::factory()->create([
            'payment_id' => $payment->id,
            'allocation_type' => 'service',
            'student_service_id' => $studentService->id,
            'amount' => 20000,
        ]);

        $response = $this->actingAs($director)->delete("/student-services/{$studentService->id}");

        $response->assertSessionHasErrors('studentService');
        $this->assertDatabaseHas('student_services', ['id' => $studentService->id]);
    }

    public function test_it_refuses_to_remove_a_service_charge_that_is_already_processing(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        $service = Service::factory()->create();
        $studentService = $this->chargeStudent($student, $service);
        $studentService->update(['processing_status' => 'processing', 'processing_started_at' => now()]);

        $response = $this->actingAs($director)->delete("/student-services/{$studentService->id}");

        $response->assertSessionHasErrors('studentService');
        $this->assertDatabaseHas('student_services', ['id' => $studentService->id]);
    }

    public function test_the_remove_link_only_shows_to_directors_for_an_eligible_charge(): void
    {
        $director = User::factory()->director()->create();
        $secretary = User::factory()->secretary()->create();
        $student = Student::factory()->create();
        $service = Service::factory()->create();
        $this->chargeStudent($student, $service);

        $this->actingAs($director)->get("/students/{$student->id}")->assertSee('Remove');
        $this->actingAs($secretary)->get("/students/{$student->id}")->assertDontSee('Remove');
    }

    public function test_the_remove_link_is_hidden_once_processing_has_started(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        $service = Service::factory()->create();
        $studentService = $this->chargeStudent($student, $service);
        $studentService->update(['processing_status' => 'processing', 'processing_started_at' => now()]);

        $this->actingAs($director)->get("/students/{$student->id}")->assertDontSee('Remove');
    }
}
