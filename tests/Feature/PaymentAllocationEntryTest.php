<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Service;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentAllocationEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/payments/record')->assertRedirect('/login');
        $this->post('/payments/record', [])->assertRedirect('/login');
    }

    public function test_the_entry_screen_shows_a_student_picker_with_no_student_selected(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Pick Me']);

        $response = $this->actingAs($user)->get('/payments/record');

        $response->assertOk();
        $response->assertSee('Pick Me');
        $response->assertDontSee('Payment Amount');
    }

    public function test_selecting_a_student_shows_their_open_charges(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['name' => 'Beginner Course']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $service = Service::factory()->create(['name' => "Learner's Permit", 'price' => 6000]);
        $student->studentServices()->create(['service_id' => $service->id, 'price' => 6000]);

        $response = $this->actingAs($user)->get("/payments/record?student_id={$student->id}");

        $response->assertOk();
        $response->assertSee('Training — Beginner Course');
        $response->assertSee("Learner's Permit");
        $response->assertSee('Payment Amount');
    }

    public function test_a_fully_paid_charge_does_not_appear_in_the_open_charges_grid(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $service = Service::factory()->create(['name' => 'Fully Paid Service', 'price' => 6000]);
        $studentService = $student->studentServices()->create(['service_id' => $service->id, 'price' => 6000]);
        $payment = Payment::factory()->create(['status' => 'paid']);
        PaymentAllocation::factory()->create([
            'payment_id' => $payment->id,
            'allocation_type' => 'service',
            'student_service_id' => $studentService->id,
            'amount' => 6000,
        ]);

        $response = $this->actingAs($user)->get("/payments/record?student_id={$student->id}");

        $response->assertOk();
        $response->assertSee('has no outstanding charges');

        // Sanity-check the fixture: the charge really does exist, it's
        // just fully paid (balance 0) so it's correctly excluded.
        $this->assertSame(0.0, $studentService->balance());
    }

    public function test_one_payment_can_fund_training_and_a_flat_service_at_once(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;
        $service = Service::factory()->create(['price' => 6000]);
        $studentService = $student->studentServices()->create(['service_id' => $service->id, 'price' => 6000]);

        $response = $this->actingAs($user)->post('/payments/record', [
            'student_id' => $student->id,
            'amount' => 26000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'allocations' => [
                ['type' => 'training', 'id' => $enrollment->id, 'amount' => 20000],
                ['type' => 'service', 'id' => $studentService->id, 'amount' => 6000],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect("/students/{$student->id}");

        $this->assertDatabaseHas('payments', ['student_id' => $student->id, 'course_id' => null, 'amount' => 26000]);
        $this->assertSame(20000.0, $enrollment->fresh()->amountPaid());
        $this->assertSame(0.0, $studentService->fresh()->balance());
    }

    public function test_the_allocated_amounts_must_add_up_to_the_payment_amount(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;

        $response = $this->actingAs($user)->post('/payments/record', [
            'student_id' => $student->id,
            'amount' => 30000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'allocations' => [
                ['type' => 'training', 'id' => $enrollment->id, 'amount' => 20000],
            ],
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_an_allocation_cannot_exceed_the_charges_balance(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;

        $response = $this->actingAs($user)->post('/payments/record', [
            'student_id' => $student->id,
            'amount' => 100000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'allocations' => [
                ['type' => 'training', 'id' => $enrollment->id, 'amount' => 100000],
            ],
        ]);

        $response->assertSessionHasErrors('allocations.0.amount');
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_a_charge_that_does_not_belong_to_the_student_is_rejected(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $otherStudent = Student::factory()->create();
        $course = Course::factory()->create();
        $otherStudent->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $otherEnrollment = $otherStudent->courses()->first()->pivot;

        $response = $this->actingAs($user)->post('/payments/record', [
            'student_id' => $student->id,
            'amount' => 20000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'allocations' => [
                ['type' => 'training', 'id' => $otherEnrollment->id, 'amount' => 20000],
            ],
        ]);

        $response->assertSessionHasErrors('allocations.0.id');
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_duplicate_allocation_rows_for_the_same_charge_cannot_exceed_its_balance(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 30000]);
        $enrollment = $student->courses()->first()->pivot;

        $response = $this->actingAs($user)->post('/payments/record', [
            'student_id' => $student->id,
            'amount' => 40000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'allocations' => [
                ['type' => 'training', 'id' => $enrollment->id, 'amount' => 20000],
                ['type' => 'training', 'id' => $enrollment->id, 'amount' => 20000],
            ],
        ]);

        $response->assertSessionHasErrors('allocations.1.amount');
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_a_payment_with_no_allocations_is_rejected(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();

        $response = $this->actingAs($user)->post('/payments/record', [
            'student_id' => $student->id,
            'amount' => 20000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'allocations' => [],
        ]);

        $response->assertSessionHasErrors('allocations');
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_blank_allocation_rows_are_ignored_rather_than_rejected(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;
        $service = Service::factory()->create(['price' => 6000]);
        $studentService = $student->studentServices()->create(['service_id' => $service->id, 'price' => 6000]);

        $response = $this->actingAs($user)->post('/payments/record', [
            'student_id' => $student->id,
            'amount' => 20000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'allocations' => [
                ['type' => 'training', 'id' => $enrollment->id, 'amount' => 20000],
                ['type' => 'service', 'id' => $studentService->id, 'amount' => ''],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(20000.0, $enrollment->fresh()->amountPaid());
        $this->assertSame(0.0, $studentService->fresh()->amountPaid());
    }

    public function test_a_multi_service_payment_unlocks_training_immediately_when_the_balance_clears(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, [
            'enrolled_at' => now(),
            'status' => 'locked',
            'locked_reason' => 'overdue_balance',
            'fee' => 20000,
            'due_date' => now()->subDay()->toDateString(),
        ]);
        $enrollment = $student->courses()->first()->pivot;

        $this->actingAs($user)->post('/payments/record', [
            'student_id' => $student->id,
            'amount' => 20000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'allocations' => [
                ['type' => 'training', 'id' => $enrollment->id, 'amount' => 20000],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame('active', $enrollment->fresh()->status);
        $this->assertNull($enrollment->fresh()->locked_reason);
    }

    public function test_the_student_page_and_payments_index_render_a_multi_service_payment_without_a_course(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;
        $service = Service::factory()->create();
        $studentService = $student->studentServices()->create(['service_id' => $service->id, 'price' => 6000]);

        $this->actingAs($user)->post('/payments/record', [
            'student_id' => $student->id,
            'amount' => 26000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'allocations' => [
                ['type' => 'training', 'id' => $enrollment->id, 'amount' => 20000],
                ['type' => 'service', 'id' => $studentService->id, 'amount' => 6000],
            ],
        ])->assertSessionHasNoErrors();

        // A multi-service payment has no single course, so every page that
        // lists payments must render its allocation-based description
        // instead of crashing on a null $payment->course.
        $description = 'Training — '.$course->name;
        $this->actingAs($user)->get("/students/{$student->id}")->assertOk()->assertSee($description);
        $this->actingAs($user)->get('/payments')->assertOk()->assertSee($description);

        $payment = Payment::whereNull('course_id')->firstOrFail();
        $this->actingAs($user)->get("/payments/{$payment->id}")->assertOk()->assertSee('Multiple Services')->assertSee($description);
    }
}
