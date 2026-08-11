<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Service;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceProcessingStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_new_service_charge_defaults_to_not_started(): void
    {
        $service = Service::factory()->create();
        $studentService = Student::factory()->create()->studentServices()->create([
            'service_id' => $service->id,
            'price' => $service->price,
        ]);

        $this->assertSame('not_started', $studentService->processing_status);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $service = Service::factory()->create();
        $studentService = Student::factory()->create()->studentServices()->create([
            'service_id' => $service->id,
            'price' => $service->price,
        ]);

        $this->patch("/student-services/{$studentService->id}/processing-status", ['processing_status' => 'processing'])
            ->assertRedirect('/login');
    }

    public function test_an_authenticated_user_can_update_the_processing_status(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();
        $studentService = Student::factory()->create()->studentServices()->create([
            'service_id' => $service->id,
            'price' => $service->price,
        ]);

        $response = $this->actingAs($user)->patch("/student-services/{$studentService->id}/processing-status", [
            'processing_status' => 'processing',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('processing', $studentService->fresh()->processing_status);
    }

    public function test_an_invalid_processing_status_is_rejected(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();
        $studentService = Student::factory()->create()->studentServices()->create([
            'service_id' => $service->id,
            'price' => $service->price,
        ]);

        $response = $this->actingAs($user)->patch("/student-services/{$studentService->id}/processing-status", [
            'processing_status' => 'not-a-real-status',
        ]);

        $response->assertSessionHasErrors('processing_status');
        $this->assertSame('not_started', $studentService->fresh()->processing_status);
    }

    public function test_processing_status_is_independent_of_payment_status(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['price' => 6000]);
        $student = Student::factory()->create();
        $studentService = $student->studentServices()->create(['service_id' => $service->id, 'price' => 6000]);

        // Fully paid, but processing hasn't started yet.
        $payment = Payment::factory()->create(['status' => 'paid']);
        PaymentAllocation::factory()->create([
            'payment_id' => $payment->id,
            'allocation_type' => 'service',
            'student_service_id' => $studentService->id,
            'amount' => 6000,
        ]);

        $this->assertSame('paid', $studentService->status());
        $this->assertSame('not_started', $studentService->fresh()->processing_status);

        $this->actingAs($user)->patch("/student-services/{$studentService->id}/processing-status", [
            'processing_status' => 'completed',
        ])->assertSessionHasNoErrors();

        // Marking processing complete never touches payment status.
        $this->assertSame('paid', $studentService->fresh()->status());
        $this->assertSame('completed', $studentService->fresh()->processing_status);
    }

    public function test_the_student_page_shows_the_processing_status_selector(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => "Driver's License Processing"]);
        $studentService = Student::factory()->create()->studentServices()->create([
            'service_id' => $service->id,
            'price' => $service->price,
            'processing_status' => 'processing',
        ]);

        $response = $this->actingAs($user)->get("/students/{$studentService->student_id}");

        $response->assertOk();
        $response->assertSee('Processing Status');
        $response->assertSee('selected', false);
    }
}
