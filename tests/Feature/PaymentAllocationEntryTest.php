<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Service;
use App\Models\Student;
use App\Models\StudentService;
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

    public function test_the_payments_index_records_payment_button_links_to_the_multi_service_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/payments');

        $response->assertOk();
        $response->assertSee(route('payments.record.create'), false);
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

    public function test_an_uncharged_active_service_appears_as_a_billable_row(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $service = Service::factory()->create(['name' => "Driver's License Processing", 'price' => 50000, 'is_active' => true]);

        $response = $this->actingAs($user)->get("/payments/record?student_id={$student->id}");

        $response->assertOk();
        $response->assertSee("Driver's License Processing");
        $response->assertSee('Not Yet Billed');
        $response->assertSee('50,000.00');
    }

    public function test_an_inactive_service_does_not_appear_as_a_billable_row(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        Service::factory()->create(['name' => 'Retired Service', 'is_active' => false]);

        $response = $this->actingAs($user)->get("/payments/record?student_id={$student->id}");

        $response->assertOk();
        $response->assertDontSee('Retired Service');
    }

    public function test_an_already_charged_service_does_not_duplicate_as_a_new_billable_row(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $service = Service::factory()->create(['name' => "Learner's Permit", 'price' => 6000]);
        $student->studentServices()->create(['service_id' => $service->id, 'price' => 6000]);

        $response = $this->actingAs($user)->get("/payments/record?student_id={$student->id}");

        $response->assertOk();
        $response->assertSee("Learner's Permit");
        $response->assertDontSee('Not Yet Billed');
    }

    public function test_paying_for_an_uncharged_service_bills_and_pays_for_it_in_one_step(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $service = Service::factory()->create(['name' => "Driver's License Processing", 'price' => 50000]);

        $response = $this->actingAs($user)->post('/payments/record', [
            'student_id' => $student->id,
            'amount' => 50000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'allocations' => [
                ['type' => 'new_service', 'id' => $service->id, 'amount' => 50000],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect("/students/{$student->id}");

        $this->assertDatabaseHas('student_services', [
            'student_id' => $student->id,
            'service_id' => $service->id,
            'price' => 50000,
        ]);

        $studentService = $student->studentServices()->firstOrFail();
        $this->assertSame(50000.0, $studentService->amountPaid());
        $this->assertSame(0.0, $studentService->balance());
    }

    public function test_paying_less_than_the_full_price_for_an_uncharged_service_leaves_a_balance(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $service = Service::factory()->create(['name' => "Driver's License Processing", 'price' => 50000]);

        $this->actingAs($user)->post('/payments/record', [
            'student_id' => $student->id,
            'amount' => 20000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'allocations' => [
                ['type' => 'new_service', 'id' => $service->id, 'amount' => 20000],
            ],
        ])->assertSessionHasNoErrors();

        $studentService = $student->studentServices()->firstOrFail();
        $this->assertSame(20000.0, $studentService->amountPaid());
        $this->assertSame(30000.0, $studentService->balance());
    }

    public function test_a_new_service_allocation_cannot_exceed_its_full_price(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $service = Service::factory()->create(['price' => 6000]);

        $response = $this->actingAs($user)->post('/payments/record', [
            'student_id' => $student->id,
            'amount' => 10000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'allocations' => [
                ['type' => 'new_service', 'id' => $service->id, 'amount' => 10000],
            ],
        ]);

        $response->assertSessionHasErrors('allocations.0.amount');
        $this->assertDatabaseCount('student_services', 0);
    }

    public function test_one_payment_can_bill_a_new_service_while_paying_an_existing_charge(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;
        $service = Service::factory()->create(['name' => "Learner's Permit", 'price' => 6000]);

        $this->actingAs($user)->post('/payments/record', [
            'student_id' => $student->id,
            'amount' => 26000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'allocations' => [
                ['type' => 'training', 'id' => $enrollment->id, 'amount' => 20000],
                ['type' => 'new_service', 'id' => $service->id, 'amount' => 6000],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame(20000.0, $enrollment->fresh()->amountPaid());
        $this->assertDatabaseHas('student_services', ['student_id' => $student->id, 'service_id' => $service->id]);
    }

    public function test_each_charge_row_renders_a_tick_box_that_can_auto_fill_its_full_balance(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        Service::factory()->create(['name' => "Driver's License Processing", 'price' => 50000, 'is_active' => true]);

        $response = $this->actingAs($user)->get("/payments/record?student_id={$student->id}");

        $response->assertOk();
        $response->assertSee('type="checkbox"', false);
        // The row's Alpine state seeds the checkbox's auto-fill target with the
        // charge's own balance, so ticking it proposes a full payment while the
        // amount field stays editable for a part payment.
        $response->assertSee("checked ? '50000' : ''", false);
    }

    public function test_paying_for_a_tracked_service_automatically_starts_processing(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $service = Service::factory()->create(['name' => "Driver's License Processing", 'price' => 50000, 'processing_days' => 30]);
        $studentService = $student->studentServices()->create(['service_id' => $service->id, 'price' => 50000]);

        $this->actingAs($user)->post('/payments/record', [
            'student_id' => $student->id,
            'amount' => 20000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'allocations' => [
                ['type' => 'service', 'id' => $studentService->id, 'amount' => 20000],
            ],
        ])->assertSessionHasNoErrors();

        $studentService->refresh();
        $this->assertSame('processing', $studentService->processing_status);
        $this->assertTrue($studentService->processing_started_at->isToday());
    }

    public function test_paying_for_an_untracked_service_does_not_start_processing(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $service = Service::factory()->create(['name' => "Learner's Permit", 'price' => 6000, 'processing_days' => null]);
        $studentService = $student->studentServices()->create(['service_id' => $service->id, 'price' => 6000]);

        $this->actingAs($user)->post('/payments/record', [
            'student_id' => $student->id,
            'amount' => 6000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'allocations' => [
                ['type' => 'service', 'id' => $studentService->id, 'amount' => 6000],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame('not_started', $studentService->fresh()->processing_status);
    }

    public function test_a_second_payment_does_not_reset_an_already_processing_services_start_date(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $service = Service::factory()->create(['price' => 50000, 'processing_days' => 30]);
        $startedAt = now()->subDays(10);
        $studentService = $student->studentServices()->create([
            'service_id' => $service->id,
            'price' => 50000,
            'processing_status' => 'processing',
            'processing_started_at' => $startedAt,
        ]);

        $this->actingAs($user)->post('/payments/record', [
            'student_id' => $student->id,
            'amount' => 20000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'allocations' => [
                ['type' => 'service', 'id' => $studentService->id, 'amount' => 20000],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame($startedAt->timestamp, $studentService->fresh()->processing_started_at->timestamp);
    }

    public function test_paying_for_an_already_completed_service_does_not_revert_it_to_processing(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $service = Service::factory()->create(['price' => 50000, 'processing_days' => 30]);
        $studentService = $student->studentServices()->create([
            'service_id' => $service->id,
            'price' => 50000,
            'processing_status' => 'completed',
            'processing_started_at' => now()->subDays(35),
        ]);

        $this->actingAs($user)->post('/payments/record', [
            'student_id' => $student->id,
            'amount' => 10000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'allocations' => [
                ['type' => 'service', 'id' => $studentService->id, 'amount' => 10000],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame('completed', $studentService->fresh()->processing_status);
    }

    public function test_billing_and_paying_a_new_tracked_service_in_one_step_also_starts_processing(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $service = Service::factory()->create(['name' => "Driver's License Processing", 'price' => 50000, 'processing_days' => 30]);

        $this->actingAs($user)->post('/payments/record', [
            'student_id' => $student->id,
            'amount' => 50000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'allocations' => [
                ['type' => 'new_service', 'id' => $service->id, 'amount' => 50000],
            ],
        ])->assertSessionHasNoErrors();

        $studentService = StudentService::where('student_id', $student->id)->where('service_id', $service->id)->firstOrFail();
        $this->assertSame('processing', $studentService->processing_status);
        $this->assertNotNull($studentService->processing_started_at);
    }
}
