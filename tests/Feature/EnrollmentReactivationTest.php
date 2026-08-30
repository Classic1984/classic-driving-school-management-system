<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\ReactivationAuditLog;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentReactivationTest extends TestCase
{
    use RefreshDatabase;

    protected function enroll(Student $student, Course $course, array $overrides = []): Enrollment
    {
        $course->students()->attach($student->id, array_merge([
            'enrolled_at' => now()->subMonths(3)->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => 'locked',
            'locked_reason' => 'training_period_expired',
        ], $overrides));

        return Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $course = Course::factory()->create();
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course);

        $this->get("/enrollments/{$enrollment->id}/reactivate")->assertRedirect('/login');
        $this->post("/enrollments/{$enrollment->id}/reactivate", [])->assertRedirect('/login');
    }

    public function test_admin_cannot_reactivate_an_enrollment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::factory()->create();
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course);

        $this->actingAs($admin)->get("/enrollments/{$enrollment->id}/reactivate")->assertForbidden();
        $this->actingAs($admin)->post("/enrollments/{$enrollment->id}/reactivate", [
            'additional_fee' => 30000,
            'payment_method' => 'cash',
        ])->assertForbidden();
    }

    public function test_secretary_cannot_reactivate_an_enrollment(): void
    {
        $secretary = User::factory()->create(['role' => 'secretary']);
        $course = Course::factory()->create();
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course);

        $this->actingAs($secretary)->get("/enrollments/{$enrollment->id}/reactivate")->assertForbidden();
    }

    public function test_director_can_view_the_reactivation_form(): void
    {
        $director = User::factory()->director()->create();
        $course = Course::factory()->create(['fee' => 50000]);
        $student = Student::factory()->create(['name' => 'Locked Student']);
        $enrollment = $this->enroll($student, $course);

        $response = $this->actingAs($director)->get("/enrollments/{$enrollment->id}/reactivate");

        $response->assertOk();
        $response->assertSee('Locked Student');
        $response->assertSee('50,000.00');
    }

    public function test_the_form_is_not_available_for_an_enrollment_locked_for_overdue_balance_instead(): void
    {
        $director = User::factory()->director()->create();
        $course = Course::factory()->create();
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course, ['locked_reason' => 'overdue_balance']);

        $this->actingAs($director)->get("/enrollments/{$enrollment->id}/reactivate")->assertNotFound();
    }

    public function test_the_form_is_not_available_for_an_active_enrollment(): void
    {
        $director = User::factory()->director()->create();
        $course = Course::factory()->create();
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course, ['status' => 'active', 'locked_reason' => null]);

        $this->actingAs($director)->get("/enrollments/{$enrollment->id}/reactivate")->assertNotFound();
    }

    public function test_director_can_reactivate_an_enrollment_collecting_balance_plus_the_agreed_fee(): void
    {
        $director = User::factory()->director()->create();
        $course = Course::factory()->create(['fee' => 50000]);
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course, ['fee' => 50000]);
        Payment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id, 'amount' => 20000, 'status' => 'paid']);
        // Outstanding balance is therefore 30,000.

        $response = $this->actingAs($director)->post("/enrollments/{$enrollment->id}/reactivate", [
            'additional_fee' => 25000,
            'payment_method' => 'cash',
            'reference_number' => 'RA-001',
            'notes' => 'Agreed with parent.',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('students.show', $student));

        // Total collected: 30,000 outstanding balance + 25,000 agreed fee = 55,000.
        // course_id is null - like the programme-upgrade flow, this avoids
        // Payment::booted()'s save hook lumping the whole bundled total
        // into a single "training" allocation (see the allocation-split
        // assertions below instead).
        $this->assertDatabaseHas('payments', [
            'student_id' => $student->id,
            'course_id' => null,
            'amount' => 55000,
            'payment_method' => 'cash',
            'status' => 'paid',
            'reference_number' => 'RA-001',
        ]);

        $payment = Payment::where('reference_number', 'RA-001')->firstOrFail();

        $this->assertDatabaseHas('payment_allocations', [
            'payment_id' => $payment->id,
            'allocation_type' => 'training',
            'enrollment_id' => $enrollment->id,
            'amount' => 30000,
        ]);

        $this->assertDatabaseHas('payment_allocations', [
            'payment_id' => $payment->id,
            'allocation_type' => 'reactivation_fee',
            'enrollment_id' => $enrollment->id,
            'amount' => 25000,
        ]);

        $this->assertDatabaseHas('reactivation_audit_logs', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'reactivated_by' => $director->id,
            'balance_cleared' => 30000,
            'additional_fee' => 25000,
            'total_amount' => 55000,
        ]);

        $fresh = $enrollment->fresh();
        $this->assertSame('active', $fresh->status);
        $this->assertNull($fresh->locked_reason);
        $this->assertSame(0.0, $fresh->balance());
        $this->assertSame(now()->toDateString(), $fresh->enrolled_at->toDateString());
        $this->assertSame(now()->toDateString(), $fresh->reactivated_at->toDateString());
        $this->assertSame($director->id, $fresh->reactivated_by);
    }

    public function test_reactivating_gives_a_fresh_two_month_training_period(): void
    {
        $director = User::factory()->director()->create();
        $course = Course::factory()->create(['fee' => 10000]);
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course, ['fee' => 10000]);
        Payment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id, 'amount' => 10000, 'status' => 'paid']);

        $this->actingAs($director)->post("/enrollments/{$enrollment->id}/reactivate", [
            'additional_fee' => 0,
            'payment_method' => 'cash',
        ]);

        $fresh = $enrollment->fresh();
        $this->assertFalse($fresh->isTrainingPeriodExpired());
    }

    public function test_reactivation_can_be_free_of_charge_when_the_balance_is_already_clear(): void
    {
        $director = User::factory()->director()->create();
        $course = Course::factory()->create(['fee' => 10000]);
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course, ['fee' => 10000]);
        Payment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id, 'amount' => 10000, 'status' => 'paid']);

        $response = $this->actingAs($director)->post("/enrollments/{$enrollment->id}/reactivate", [
            'additional_fee' => 0,
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('payments', 1);
        $this->assertSame('active', $enrollment->fresh()->status);
    }

    public function test_reactivating_requires_a_valid_payment_method(): void
    {
        $director = User::factory()->director()->create();
        $course = Course::factory()->create();
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course);

        $response = $this->actingAs($director)->post("/enrollments/{$enrollment->id}/reactivate", [
            'additional_fee' => 20000,
            'payment_method' => 'invalid-method',
        ]);

        $response->assertSessionHasErrors('payment_method');
        $this->assertSame(0, ReactivationAuditLog::count());
    }

    public function test_reactivating_an_enrollment_not_locked_for_expired_training_fails(): void
    {
        $director = User::factory()->director()->create();
        $course = Course::factory()->create();
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course, ['status' => 'locked', 'locked_reason' => 'overdue_balance']);

        $response = $this->actingAs($director)->post("/enrollments/{$enrollment->id}/reactivate", [
            'additional_fee' => 20000,
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHasErrors('enrollment');
        $this->assertSame(0, ReactivationAuditLog::count());
    }
}
