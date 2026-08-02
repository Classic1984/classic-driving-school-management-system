<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentLockingTest extends TestCase
{
    use RefreshDatabase;

    public function test_grace_period_is_four_days_for_three_and_four_week_courses(): void
    {
        $this->assertSame(4, Course::factory()->make(['duration_weeks' => 4])->gracePeriodDays());
        $this->assertSame(4, Course::factory()->make(['duration_weeks' => 3])->gracePeriodDays());
    }

    public function test_grace_period_is_two_days_for_one_and_two_week_courses(): void
    {
        $this->assertSame(2, Course::factory()->make(['duration_weeks' => 2])->gracePeriodDays());
        $this->assertSame(2, Course::factory()->make(['duration_weeks' => 1])->gracePeriodDays());
    }

    public function test_enrollment_locks_when_balance_is_overdue(): void
    {
        $course = Course::factory()->create(['fee' => 100, 'duration_weeks' => 4]);
        $student = Student::factory()->create();
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->subDays(10)->toDateString(),
            'due_date' => now()->subDays(6)->toDateString(),
            'status' => 'active',
        ]);

        $enrollment = Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();
        $enrollment->refreshStatus();

        $this->assertSame('locked', $enrollment->fresh()->status);
        $this->assertSame('overdue_balance', $enrollment->fresh()->locked_reason);
    }

    public function test_enrollment_stays_active_when_balance_is_paid_before_due_date(): void
    {
        $course = Course::factory()->create(['fee' => 100, 'duration_weeks' => 4]);
        $student = Student::factory()->create();
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->toDateString(),
            'due_date' => now()->addDays(4)->toDateString(),
            'status' => 'active',
        ]);
        Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 100,
            'status' => 'paid',
        ]);

        $enrollment = Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();
        $enrollment->refreshStatus();

        $this->assertSame('active', $enrollment->fresh()->status);
    }

    public function test_recording_a_payment_immediately_unlocks_the_enrollment(): void
    {
        $admin = \App\Models\User::factory()->create();
        $course = Course::factory()->create(['fee' => 100, 'duration_weeks' => 4]);
        $student = Student::factory()->create();
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->subDays(10)->toDateString(),
            'due_date' => now()->subDays(6)->toDateString(),
            'status' => 'locked',
            'locked_reason' => 'overdue_balance',
        ]);

        $response = $this->actingAs($admin)->post('/payments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 100,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);

        $response->assertSessionHasNoErrors();

        $enrollment = Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();
        $this->assertSame('active', $enrollment->status);
        $this->assertNull($enrollment->locked_reason);
    }

    public function test_enrollment_locks_after_two_months_even_if_fully_paid(): void
    {
        $course = Course::factory()->create(['fee' => 100, 'duration_weeks' => 4]);
        $student = Student::factory()->create();
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->subMonths(3)->toDateString(),
            'due_date' => now()->subMonths(3)->addDays(4)->toDateString(),
            'status' => 'active',
        ]);
        Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 100,
            'status' => 'paid',
        ]);

        $enrollment = Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();
        $enrollment->refreshStatus();

        $this->assertSame('locked', $enrollment->fresh()->status);
        $this->assertSame('training_period_expired', $enrollment->fresh()->locked_reason);
    }

    public function test_completed_enrollments_are_never_relocked(): void
    {
        $course = Course::factory()->create(['fee' => 100, 'duration_weeks' => 4]);
        $student = Student::factory()->create();
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->subMonths(6)->toDateString(),
            'due_date' => now()->subMonths(6)->toDateString(),
            'status' => 'completed',
        ]);

        $enrollment = Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();
        $enrollment->refreshStatus();

        $this->assertSame('completed', $enrollment->fresh()->status);
    }

    public function test_refresh_enrollment_locks_command_locks_overdue_enrollments(): void
    {
        $course = Course::factory()->create(['fee' => 100, 'duration_weeks' => 4]);
        $student = Student::factory()->create();
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->subDays(10)->toDateString(),
            'due_date' => now()->subDays(6)->toDateString(),
            'status' => 'active',
        ]);

        $this->artisan('app:refresh-enrollment-locks')->assertExitCode(0);

        $enrollment = Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();
        $this->assertSame('locked', $enrollment->status);
    }
}
