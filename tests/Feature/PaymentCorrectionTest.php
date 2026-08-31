<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Payment;
use App\Models\PaymentCorrectionLog;
use App\Models\Service;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentCorrectionTest extends TestCase
{
    use RefreshDatabase;

    protected function createSplitPayment(User $user, Student $student, int $enrollmentId, int $studentServiceId): Payment
    {
        $this->actingAs($user)->post('/payments/record', [
            'student_id' => $student->id,
            'amount' => 50000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'allocations' => [
                ['type' => 'training', 'id' => $enrollmentId, 'amount' => 30000],
                ['type' => 'service', 'id' => $studentServiceId, 'amount' => 20000],
            ],
        ])->assertSessionHasNoErrors();

        return Payment::whereNull('course_id')->firstOrFail();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $payment = Payment::factory()->create();

        $this->get("/payments/{$payment->id}/correct")->assertRedirect('/login');
        $this->put("/payments/{$payment->id}/correct", [])->assertRedirect('/login');
    }

    public function test_a_secretary_cannot_view_or_submit_a_correction(): void
    {
        $secretary = User::factory()->secretary()->create();
        $payment = Payment::factory()->create();

        $this->actingAs($secretary)->get("/payments/{$payment->id}/correct")->assertForbidden();
        $this->actingAs($secretary)->put("/payments/{$payment->id}/correct", [])->assertForbidden();
    }

    public function test_an_admin_cannot_view_or_submit_a_correction(): void
    {
        $admin = User::factory()->admin()->create();
        $payment = Payment::factory()->create();

        $this->actingAs($admin)->get("/payments/{$payment->id}/correct")->assertForbidden();
        $this->actingAs($admin)->put("/payments/{$payment->id}/correct", [])->assertForbidden();
    }

    public function test_the_correct_allocation_link_is_hidden_from_a_secretary_on_the_payment_page(): void
    {
        $secretary = User::factory()->secretary()->create();
        $payment = Payment::factory()->create();

        $response = $this->actingAs($secretary)->get("/payments/{$payment->id}");

        $response->assertOk();
        $response->assertDontSee('Correct Allocation');
    }

    public function test_the_correction_form_shows_the_current_allocation(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['name' => 'Beginner Course']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;
        $service = Service::factory()->create(['name' => 'Driver License', 'price' => 50000]);
        $studentService = $student->studentServices()->create(['service_id' => $service->id, 'price' => 50000]);

        $payment = $this->createSplitPayment($user, $student, $enrollment->id, $studentService->id);

        $response = $this->actingAs($user)->get("/payments/{$payment->id}/correct");

        $response->assertOk();
        $response->assertSee('Training — Beginner Course');
        $response->assertSee('Driver License');
        $response->assertSee('30,000.00');
        $response->assertSee('20,000.00');
    }

    public function test_a_director_can_correct_a_payments_allocation_with_an_audit_trail(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;
        $service = Service::factory()->create(['price' => 50000]);
        $studentService = $student->studentServices()->create(['service_id' => $service->id, 'price' => 50000]);

        $payment = $this->createSplitPayment($user, $student, $enrollment->id, $studentService->id);
        $trainingAllocation = $payment->allocations()->where('allocation_type', 'training')->firstOrFail();
        $serviceAllocation = $payment->allocations()->where('allocation_type', 'service')->firstOrFail();

        $response = $this->actingAs($user)->put("/payments/{$payment->id}/correct", [
            'reason' => 'Staff mis-keyed the split at entry.',
            'allocations' => [
                ['id' => $trainingAllocation->id, 'amount' => 20000],
                ['id' => $serviceAllocation->id, 'amount' => 30000],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect("/payments/{$payment->id}");

        $this->assertSame(20000.0, (float) $trainingAllocation->fresh()->amount);
        $this->assertSame(30000.0, (float) $serviceAllocation->fresh()->amount);
        // The payment's own total is never touched by a correction.
        $this->assertSame(50000.0, (float) $payment->fresh()->amount);

        $this->assertDatabaseHas('payment_correction_logs', [
            'payment_id' => $payment->id,
            'corrected_by' => $user->id,
            'reason' => 'Staff mis-keyed the split at entry.',
        ]);

        $log = PaymentCorrectionLog::where('payment_id', $payment->id)->firstOrFail();
        $this->assertEquals(30000.0, $log->original_allocations[0]['amount']);
        $this->assertEquals(20000.0, $log->new_allocations[0]['amount']);
    }

    public function test_a_correction_must_add_up_to_the_original_payment_total(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;
        $service = Service::factory()->create(['price' => 50000]);
        $studentService = $student->studentServices()->create(['service_id' => $service->id, 'price' => 50000]);

        $payment = $this->createSplitPayment($user, $student, $enrollment->id, $studentService->id);
        $trainingAllocation = $payment->allocations()->where('allocation_type', 'training')->firstOrFail();
        $serviceAllocation = $payment->allocations()->where('allocation_type', 'service')->firstOrFail();

        $response = $this->actingAs($user)->put("/payments/{$payment->id}/correct", [
            'reason' => 'Trying to sneak in extra money.',
            'allocations' => [
                ['id' => $trainingAllocation->id, 'amount' => 20000],
                ['id' => $serviceAllocation->id, 'amount' => 20000],
            ],
        ]);

        $response->assertSessionHasErrors('allocations');
        $this->assertSame(30000.0, (float) $trainingAllocation->fresh()->amount);
        $this->assertDatabaseCount('payment_correction_logs', 0);
    }

    public function test_a_correction_cannot_drop_or_add_an_allocation(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;
        $service = Service::factory()->create(['price' => 50000]);
        $studentService = $student->studentServices()->create(['service_id' => $service->id, 'price' => 50000]);

        $payment = $this->createSplitPayment($user, $student, $enrollment->id, $studentService->id);
        $trainingAllocation = $payment->allocations()->where('allocation_type', 'training')->firstOrFail();

        $response = $this->actingAs($user)->put("/payments/{$payment->id}/correct", [
            'reason' => 'Dropping the service allocation entirely.',
            'allocations' => [
                ['id' => $trainingAllocation->id, 'amount' => 50000],
            ],
        ]);

        $response->assertSessionHasErrors('allocations');
        $this->assertDatabaseCount('payment_correction_logs', 0);
    }

    public function test_a_reason_is_required(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;
        $service = Service::factory()->create(['price' => 50000]);
        $studentService = $student->studentServices()->create(['service_id' => $service->id, 'price' => 50000]);

        $payment = $this->createSplitPayment($user, $student, $enrollment->id, $studentService->id);
        $trainingAllocation = $payment->allocations()->where('allocation_type', 'training')->firstOrFail();
        $serviceAllocation = $payment->allocations()->where('allocation_type', 'service')->firstOrFail();

        $response = $this->actingAs($user)->put("/payments/{$payment->id}/correct", [
            'allocations' => [
                ['id' => $trainingAllocation->id, 'amount' => 20000],
                ['id' => $serviceAllocation->id, 'amount' => 30000],
            ],
        ]);

        $response->assertSessionHasErrors('reason');
    }

    public function test_a_correction_updates_the_enrollments_balance_immediately(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;
        $service = Service::factory()->create(['price' => 50000]);
        $studentService = $student->studentServices()->create(['service_id' => $service->id, 'price' => 50000]);

        $payment = $this->createSplitPayment($user, $student, $enrollment->id, $studentService->id);
        $this->assertSame(65000.0, $enrollment->fresh()->balance());

        $trainingAllocation = $payment->allocations()->where('allocation_type', 'training')->firstOrFail();
        $serviceAllocation = $payment->allocations()->where('allocation_type', 'service')->firstOrFail();

        $this->actingAs($user)->put("/payments/{$payment->id}/correct", [
            'reason' => 'Correcting the split so training reflects what was really paid.',
            'allocations' => [
                ['id' => $trainingAllocation->id, 'amount' => 40000],
                ['id' => $serviceAllocation->id, 'amount' => 10000],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame(55000.0, $enrollment->fresh()->balance());
    }

    public function test_a_correction_logs_the_old_and_new_amounts_to_the_activity_log(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;
        $service = Service::factory()->create(['price' => 50000]);
        $studentService = $student->studentServices()->create(['service_id' => $service->id, 'price' => 50000]);

        $payment = $this->createSplitPayment($user, $student, $enrollment->id, $studentService->id);
        $trainingAllocation = $payment->allocations()->where('allocation_type', 'training')->firstOrFail();
        $serviceAllocation = $payment->allocations()->where('allocation_type', 'service')->firstOrFail();

        $this->actingAs($user)->put("/payments/{$payment->id}/correct", [
            'reason' => 'Staff mis-keyed the split at entry.',
            'allocations' => [
                ['id' => $trainingAllocation->id, 'amount' => 20000],
                ['id' => $serviceAllocation->id, 'amount' => 30000],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('activity_logs', [
            'description' => "Corrected the allocation for payment {$payment->receipt_number}: Training — {$course->name} ₦30,000.00 → ₦20,000.00, {$service->name} ₦20,000.00 → ₦30,000.00",
        ]);
    }

    public function test_a_correction_reopens_a_completed_enrollment_if_the_balance_no_longer_covers_it(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        // Zero training-day requirement so the enrollment completes the
        // moment its training allocation covers the fee in full.
        $course = Course::factory()->create(['duration_hours' => 0, 'duration_weeks' => 0]);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 30000]);
        $enrollment = $student->courses()->first()->pivot;
        $service = Service::factory()->create(['price' => 50000]);
        $studentService = $student->studentServices()->create(['service_id' => $service->id, 'price' => 50000]);

        // Training allocation (30000) fully covers the 30000 fee, so this
        // payment completes the enrollment immediately.
        $payment = $this->createSplitPayment($user, $student, $enrollment->id, $studentService->id);
        $this->assertSame('completed', $enrollment->fresh()->status);

        $trainingAllocation = $payment->allocations()->where('allocation_type', 'training')->firstOrFail();
        $serviceAllocation = $payment->allocations()->where('allocation_type', 'service')->firstOrFail();

        // Correct the split so only 10000 of the same payment is actually
        // training money - the enrollment's fee is no longer fully paid.
        $this->actingAs($user)->put("/payments/{$payment->id}/correct", [
            'reason' => 'Training allocation was overstated at entry.',
            'allocations' => [
                ['id' => $trainingAllocation->id, 'amount' => 10000],
                ['id' => $serviceAllocation->id, 'amount' => 40000],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame(20000.0, $enrollment->fresh()->balance());
        $this->assertNotSame('completed', $enrollment->fresh()->status);
    }

    public function test_the_payment_page_shows_correction_history(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;
        $service = Service::factory()->create(['price' => 50000]);
        $studentService = $student->studentServices()->create(['service_id' => $service->id, 'price' => 50000]);

        $payment = $this->createSplitPayment($user, $student, $enrollment->id, $studentService->id);
        $trainingAllocation = $payment->allocations()->where('allocation_type', 'training')->firstOrFail();
        $serviceAllocation = $payment->allocations()->where('allocation_type', 'service')->firstOrFail();

        $this->actingAs($user)->put("/payments/{$payment->id}/correct", [
            'reason' => 'Fixing a data-entry mistake in the original split.',
            'allocations' => [
                ['id' => $trainingAllocation->id, 'amount' => 20000],
                ['id' => $serviceAllocation->id, 'amount' => 30000],
            ],
        ])->assertSessionHasNoErrors();

        $response = $this->actingAs($user)->get("/payments/{$payment->id}");

        $response->assertOk();
        $response->assertSee('Fixing a data-entry mistake in the original split.');
        $response->assertSee($user->name);
    }
}
