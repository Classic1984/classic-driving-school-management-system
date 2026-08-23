<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
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

    public function test_marking_processing_in_progress_stamps_the_start_date(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();
        $studentService = Student::factory()->create()->studentServices()->create([
            'service_id' => $service->id,
            'price' => $service->price,
        ]);

        $this->assertNull($studentService->processing_started_at);

        $this->actingAs($user)->patch("/student-services/{$studentService->id}/processing-status", [
            'processing_status' => 'processing',
        ])->assertSessionHasNoErrors();

        $this->assertNotNull($studentService->fresh()->processing_started_at);
        $this->assertTrue($studentService->fresh()->processing_started_at->isToday());
    }

    public function test_resetting_to_not_started_clears_the_start_date(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();
        $studentService = Student::factory()->create()->studentServices()->create([
            'service_id' => $service->id,
            'price' => $service->price,
            'processing_status' => 'processing',
            'processing_started_at' => now()->subDays(5),
        ]);

        $this->actingAs($user)->patch("/student-services/{$studentService->id}/processing-status", [
            'processing_status' => 'not_started',
        ])->assertSessionHasNoErrors();

        $this->assertNull($studentService->fresh()->processing_started_at);
    }

    public function test_re_entering_processing_does_not_reset_an_already_running_start_date(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();
        $startedAt = now()->subDays(5);
        $studentService = Student::factory()->create()->studentServices()->create([
            'service_id' => $service->id,
            'price' => $service->price,
            'processing_status' => 'processing',
            'processing_started_at' => $startedAt,
        ]);

        $this->actingAs($user)->patch("/student-services/{$studentService->id}/processing-status", [
            'processing_status' => 'processing',
        ])->assertSessionHasNoErrors();

        $this->assertSame($startedAt->timestamp, $studentService->fresh()->processing_started_at->timestamp);
    }

    public function test_expected_ready_at_is_null_without_a_tracked_turnaround(): void
    {
        $service = Service::factory()->create(['processing_days' => null]);
        $studentService = Student::factory()->create()->studentServices()->create([
            'service_id' => $service->id,
            'price' => $service->price,
            'processing_status' => 'processing',
            'processing_started_at' => now(),
        ]);

        $this->assertNull($studentService->expectedReadyAt());
        $this->assertNull($studentService->processingProgressPercent());
    }

    public function test_expected_ready_at_is_the_start_date_plus_the_services_processing_days(): void
    {
        $service = Service::factory()->create(['name' => "Driver's License Processing", 'processing_days' => 30]);
        $startedAt = now()->startOfDay();
        $studentService = Student::factory()->create()->studentServices()->create([
            'service_id' => $service->id,
            'price' => $service->price,
            'processing_status' => 'processing',
            'processing_started_at' => $startedAt,
        ]);

        $this->assertTrue($studentService->expectedReadyAt()->equalTo($startedAt->copy()->addDays(30)));
    }

    public function test_processing_progress_percent_reflects_elapsed_time(): void
    {
        $service = Service::factory()->create(['processing_days' => 30]);
        $studentService = Student::factory()->create()->studentServices()->create([
            'service_id' => $service->id,
            'price' => $service->price,
            'processing_status' => 'processing',
            'processing_started_at' => now()->subDays(15),
        ]);

        $this->assertSame(50, $studentService->processingProgressPercent());
    }

    public function test_processing_progress_percent_never_exceeds_100(): void
    {
        $service = Service::factory()->create(['processing_days' => 30]);
        $studentService = Student::factory()->create()->studentServices()->create([
            'service_id' => $service->id,
            'price' => $service->price,
            'processing_status' => 'processing',
            'processing_started_at' => now()->subDays(45),
        ]);

        $this->assertSame(100, $studentService->processingProgressPercent());
    }

    public function test_completed_processing_is_always_full_progress_regardless_of_elapsed_time(): void
    {
        $service = Service::factory()->create(['processing_days' => 30]);
        $studentService = Student::factory()->create()->studentServices()->create([
            'service_id' => $service->id,
            'price' => $service->price,
            'processing_status' => 'completed',
            'processing_started_at' => now()->subDays(2),
        ]);

        $this->assertSame(100, $studentService->processingProgressPercent());
    }

    public function test_processing_past_its_expected_ready_date_is_flagged_overdue(): void
    {
        $service = Service::factory()->create(['processing_days' => 30]);
        $studentService = Student::factory()->create()->studentServices()->create([
            'service_id' => $service->id,
            'price' => $service->price,
            'processing_status' => 'processing',
            'processing_started_at' => now()->subDays(45),
        ]);

        $this->assertTrue($studentService->isOverdueProcessing());
    }

    public function test_completed_processing_is_never_flagged_overdue(): void
    {
        $service = Service::factory()->create(['processing_days' => 30]);
        $studentService = Student::factory()->create()->studentServices()->create([
            'service_id' => $service->id,
            'price' => $service->price,
            'processing_status' => 'completed',
            'processing_started_at' => now()->subDays(45),
        ]);

        $this->assertFalse($studentService->isOverdueProcessing());
    }

    public function test_marking_a_service_completed_confirms_it_was_generated(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => "Learner's Permit"]);
        $student = Student::factory()->create(['name' => 'Amaka Obi']);
        $studentService = $student->studentServices()->create([
            'service_id' => $service->id,
            'price' => $service->price,
            'processing_status' => 'not_started',
        ]);

        $response = $this->actingAs($user)->patch("/student-services/{$studentService->id}/processing-status", [
            'processing_status' => 'completed',
        ]);

        $response->assertRedirect(route('students.show', $student->id));
        $response->assertSessionHas('status', 'service-status-updated');

        $followUp = $this->get(route('students.show', $student->id));
        $followUp->assertSee("Learner's Permit has been generated for Amaka Obi.");

        $this->assertSame('completed', $studentService->fresh()->processing_status);
    }

    public function test_marking_an_already_completed_service_again_warns_instead_of_re_processing(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => "Learner's Permit"]);
        $student = Student::factory()->create(['name' => 'Bola Ade']);
        $studentService = $student->studentServices()->create([
            'service_id' => $service->id,
            'price' => $service->price,
            'processing_status' => 'completed',
        ]);

        $logCountBefore = ActivityLog::count();

        $response = $this->actingAs($user)->patch("/student-services/{$studentService->id}/processing-status", [
            'processing_status' => 'completed',
        ]);

        $response->assertRedirect(route('students.show', $student->id));
        $response->assertSessionHas('status', 'service-status-unchanged');

        $followUp = $this->get(route('students.show', $student->id));
        $followUp->assertSee('already been marked');
        $followUp->assertSee('Learner');

        // No redundant activity entry for a click that changed nothing.
        $this->assertSame($logCountBefore, ActivityLog::count());
    }
}
