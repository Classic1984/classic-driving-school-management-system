<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Covers the model-event bridge that keeps the training PaymentAllocation
 * ledger in sync with the legacy single-course Payment flow, and the
 * one-time migration that backfills allocations for payments that already
 * existed before allocations did.
 */
class PaymentAllocationSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_a_payment_for_an_enrolled_student_creates_a_matching_training_allocation(): void
    {
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;

        $payment = Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 30000,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('payment_allocations', [
            'payment_id' => $payment->id,
            'allocation_type' => 'training',
            'enrollment_id' => $enrollment->id,
            'amount' => 30000,
        ]);

        $this->assertSame(30000.0, $enrollment->fresh()->amountPaid());
        $this->assertSame(65000.0, $enrollment->fresh()->balance());
    }

    public function test_a_payment_for_a_student_not_enrolled_in_the_course_creates_no_allocation(): void
    {
        $payment = Payment::factory()->create();

        $this->assertDatabaseMissing('payment_allocations', ['payment_id' => $payment->id]);
    }

    public function test_updating_a_payments_amount_updates_its_allocation_and_the_enrollments_balance(): void
    {
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;

        $payment = Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 30000,
            'status' => 'paid',
        ]);

        $payment->update(['amount' => 50000]);

        $this->assertDatabaseHas('payment_allocations', [
            'payment_id' => $payment->id,
            'allocation_type' => 'training',
            'amount' => 50000,
        ]);
        $this->assertSame(50000.0, $enrollment->fresh()->amountPaid());
    }

    public function test_moving_a_payment_to_a_different_course_re_points_its_allocation(): void
    {
        $student = Student::factory()->create();
        $originalCourse = Course::factory()->create();
        $newCourse = Course::factory()->create();
        $student->courses()->attach($originalCourse->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $student->courses()->attach($newCourse->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 50000]);

        $originalEnrollment = $student->courses()->find($originalCourse->id)->pivot;
        $newEnrollment = $student->courses()->find($newCourse->id)->pivot;

        $payment = Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $originalCourse->id,
            'amount' => 20000,
            'status' => 'paid',
        ]);

        $this->assertSame(20000.0, $originalEnrollment->fresh()->amountPaid());

        $payment->update(['course_id' => $newCourse->id]);

        $this->assertSame(0.0, $originalEnrollment->fresh()->amountPaid());
        $this->assertSame(20000.0, $newEnrollment->fresh()->amountPaid());
        $this->assertDatabaseCount('payment_allocations', 1);
    }

    public function test_deleting_a_payment_removes_its_allocation_and_frees_up_the_balance(): void
    {
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;

        $payment = Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 30000,
            'status' => 'paid',
        ]);

        $payment->delete();

        $this->assertDatabaseMissing('payment_allocations', ['payment_id' => $payment->id]);
        $this->assertSame(0.0, $enrollment->fresh()->amountPaid());
        $this->assertSame(95000.0, $enrollment->fresh()->balance());
    }

    public function test_a_pending_payments_allocation_does_not_count_toward_the_balance_until_marked_paid(): void
    {
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;

        $payment = Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 30000,
            'status' => 'pending',
        ]);

        // The allocation row exists (so it's ready the moment the payment
        // clears) but doesn't count yet, since it's tied to a pending
        // payment.
        $this->assertDatabaseHas('payment_allocations', ['payment_id' => $payment->id]);
        $this->assertSame(0.0, $enrollment->fresh()->amountPaid());

        $payment->update(['status' => 'paid']);

        $this->assertSame(30000.0, $enrollment->fresh()->amountPaid());
    }

    public function test_the_backfill_migration_converts_a_pre_existing_payment_into_a_training_allocation(): void
    {
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;

        // Insert the payment directly at the DB level, bypassing Eloquent,
        // to simulate a payment that was recorded before allocations
        // existed - i.e. before the Payment model's save hook was there to
        // create one automatically.
        $paymentId = DB::table('payments')->insertGetId([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 40000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'status' => 'paid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        (require database_path('migrations/2026_08_11_150000_backfill_payment_allocations_for_existing_payments.php'))->up();

        $this->assertDatabaseHas('payment_allocations', [
            'payment_id' => $paymentId,
            'allocation_type' => 'training',
            'enrollment_id' => $enrollment->id,
            'amount' => 40000,
        ]);
    }

    public function test_the_backfill_migration_skips_payments_with_no_matching_enrollment(): void
    {
        $student = Student::factory()->create();
        $course = Course::factory()->create();

        $paymentId = DB::table('payments')->insertGetId([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 40000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'status' => 'paid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        (require database_path('migrations/2026_08_11_150000_backfill_payment_allocations_for_existing_payments.php'))->up();

        $this->assertDatabaseMissing('payment_allocations', ['payment_id' => $paymentId]);
    }
}
