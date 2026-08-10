<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A single, deliberately organized suite for the school's core business
 * rules, one section per rule area. Each area already has incidental
 * coverage elsewhere (EnrollmentCompletionTest, EnrollmentLockingTest,
 * CertificateTest, RolePermissionTest, CourseTest) - this file exists so
 * every rule named on the audit checklist has its own explicit,
 * easy-to-find regression test rather than only being covered as a side
 * effect of some other feature's test.
 */
class BusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    protected function enroll(Student $student, Course $course, array $overrides = []): Enrollment
    {
        $course->students()->attach($student->id, array_merge([
            'enrolled_at' => now()->toDateString(),
            'due_date' => now()->addDays($course->gracePeriodDays())->toDateString(),
            'status' => 'active',
            'fee' => $course->fee,
        ], $overrides));

        return Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();
    }

    protected function attend(Student $student, Course $course, int $days, int $startingFrom = 1): void
    {
        for ($day = $startingFrom; $day < $startingFrom + $days; $day++) {
            Attendance::factory()->create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'date' => now()->addDays($day)->toDateString(),
                'status' => 'present',
                'duration' => 1,
            ]);
        }
    }

    protected function pay(Student $student, Course $course, float $amount): void
    {
        Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => $amount,
            'status' => 'paid',
        ]);
    }

    // ------------------------------------------------------------------
    // 5 / 10 / 15 / 20-day courses
    //
    // totalTrainingDays() is duration_weeks * 5, so these are the school's
    // actual 1/2/3/4-week programme lengths. Each must require exactly its
    // own day count - one day short must not complete, and the final day
    // must complete it immediately.
    // ------------------------------------------------------------------

    public function test_a_5_day_course_completes_only_once_all_5_days_are_attended(): void
    {
        $course = Course::factory()->create(['duration_weeks' => 1, 'fee' => 100]);
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course);
        $this->pay($student, $course, 100);

        $this->assertSame(5, $course->totalTrainingDays());

        $this->attend($student, $course, 4);
        $enrollment->refreshStatus();
        $this->assertNotSame('completed', $enrollment->fresh()->status);

        $this->attend($student, $course, 1, startingFrom: 5);
        $enrollment->refreshStatus();
        $this->assertSame('completed', $enrollment->fresh()->status);
    }

    public function test_a_10_day_course_completes_only_once_all_10_days_are_attended(): void
    {
        $course = Course::factory()->create(['duration_weeks' => 2, 'fee' => 100]);
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course);
        $this->pay($student, $course, 100);

        $this->assertSame(10, $course->totalTrainingDays());

        $this->attend($student, $course, 9);
        $enrollment->refreshStatus();
        $this->assertNotSame('completed', $enrollment->fresh()->status);

        $this->attend($student, $course, 1, startingFrom: 10);
        $enrollment->refreshStatus();
        $this->assertSame('completed', $enrollment->fresh()->status);
    }

    public function test_a_15_day_course_completes_only_once_all_15_days_are_attended(): void
    {
        $course = Course::factory()->create(['duration_weeks' => 3, 'fee' => 100]);
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course);
        $this->pay($student, $course, 100);

        $this->assertSame(15, $course->totalTrainingDays());

        $this->attend($student, $course, 14);
        $enrollment->refreshStatus();
        $this->assertNotSame('completed', $enrollment->fresh()->status);

        $this->attend($student, $course, 1, startingFrom: 15);
        $enrollment->refreshStatus();
        $this->assertSame('completed', $enrollment->fresh()->status);
    }

    public function test_a_20_day_course_completes_only_once_all_20_days_are_attended(): void
    {
        $course = Course::factory()->create(['duration_weeks' => 4, 'fee' => 100]);
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course);
        $this->pay($student, $course, 100);

        $this->assertSame(20, $course->totalTrainingDays());

        $this->attend($student, $course, 19);
        $enrollment->refreshStatus();
        $this->assertNotSame('completed', $enrollment->fresh()->status);

        $this->attend($student, $course, 1, startingFrom: 20);
        $enrollment->refreshStatus();
        $this->assertSame('completed', $enrollment->fresh()->status);
    }

    // ------------------------------------------------------------------
    // Remaining days
    // ------------------------------------------------------------------

    public function test_remaining_training_days_and_completion_percentage_track_attendance_accurately(): void
    {
        $course = Course::factory()->create(['duration_weeks' => 4, 'fee' => 100]);
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course);

        $this->assertSame(20, $enrollment->remainingTrainingDays());
        $this->assertSame(0, $enrollment->trainingCompletionPercentage());

        $this->attend($student, $course, 12);
        $this->assertSame(8, $enrollment->fresh()->remainingTrainingDays());
        $this->assertSame(60, $enrollment->fresh()->trainingCompletionPercentage());

        $this->attend($student, $course, 8, startingFrom: 13);
        $this->assertSame(0, $enrollment->fresh()->remainingTrainingDays());
        $this->assertSame(100, $enrollment->fresh()->trainingCompletionPercentage());
    }

    // ------------------------------------------------------------------
    // Completion
    //
    // Completion requires BOTH full attendance AND a cleared balance -
    // neither one alone is enough.
    // ------------------------------------------------------------------

    public function test_completion_requires_both_full_attendance_and_a_cleared_balance(): void
    {
        $course = Course::factory()->create(['duration_weeks' => 1, 'fee' => 100]);

        // Neither condition met.
        $studentNeither = Student::factory()->create();
        $enrollmentNeither = $this->enroll($studentNeither, $course);
        $enrollmentNeither->refreshStatus();
        $this->assertNotSame('completed', $enrollmentNeither->fresh()->status);

        // Balance cleared, attendance incomplete.
        $studentPaidOnly = Student::factory()->create();
        $enrollmentPaidOnly = $this->enroll($studentPaidOnly, $course);
        $this->pay($studentPaidOnly, $course, 100);
        $this->attend($studentPaidOnly, $course, 3);
        $enrollmentPaidOnly->refreshStatus();
        $this->assertNotSame('completed', $enrollmentPaidOnly->fresh()->status);

        // Attendance complete, balance outstanding.
        $studentAttendedOnly = Student::factory()->create();
        $enrollmentAttendedOnly = $this->enroll($studentAttendedOnly, $course);
        $this->attend($studentAttendedOnly, $course, 5);
        $enrollmentAttendedOnly->refreshStatus();
        $this->assertNotSame('completed', $enrollmentAttendedOnly->fresh()->status);

        // Both conditions met.
        $studentBoth = Student::factory()->create();
        $enrollmentBoth = $this->enroll($studentBoth, $course);
        $this->pay($studentBoth, $course, 100);
        $this->attend($studentBoth, $course, 5);
        $enrollmentBoth->refreshStatus();
        $this->assertSame('completed', $enrollmentBoth->fresh()->status);
    }

    // ------------------------------------------------------------------
    // Completion with unpaid balance
    //
    // The specific danger case: full attendance on a 4-week/20-day
    // programme, but a balance is still owed. Training must stay Active,
    // not Completed, and no certificate is issued.
    // ------------------------------------------------------------------

    public function test_full_attendance_with_an_outstanding_balance_stays_active_and_issues_no_certificate(): void
    {
        $course = Course::factory()->create(['duration_weeks' => 4, 'fee' => 30000]);
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course);
        $this->pay($student, $course, 10000);
        $this->attend($student, $course, 20);

        $enrollment->refreshStatus();

        $this->assertSame('active', $enrollment->fresh()->status);
        $this->assertSame(20000.0, $enrollment->fresh()->balance());
        $this->assertSame(20, $enrollment->fresh()->attendedDays());
        $this->assertDatabaseMissing('certificates', [
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);

        // The manual "Mark Complete" action must refuse it too.
        $user = User::factory()->create();
        $this->actingAs($user)->patch("/enrollments/{$enrollment->id}/complete")
            ->assertSessionHasErrors('enrollment');
        $this->assertNotSame('completed', $enrollment->fresh()->status);
    }

    // ------------------------------------------------------------------
    // Payment locking
    // ------------------------------------------------------------------

    public function test_an_enrollment_locks_once_the_balance_is_overdue_and_unlocks_immediately_once_paid(): void
    {
        $admin = User::factory()->create();
        $course = Course::factory()->create(['duration_weeks' => 4, 'fee' => 100]);
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course, [
            'enrolled_at' => now()->subDays(10)->toDateString(),
            'due_date' => now()->subDays(6)->toDateString(),
        ]);

        $enrollment->refreshStatus();
        $this->assertSame('locked', $enrollment->fresh()->status);
        $this->assertSame('overdue_balance', $enrollment->fresh()->locked_reason);

        $this->actingAs($admin)->post('/payments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 100,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'status' => 'paid',
        ])->assertSessionHasNoErrors();

        $this->assertSame('active', $enrollment->fresh()->status);
        $this->assertNull($enrollment->fresh()->locked_reason);
    }

    public function test_a_balance_is_not_overdue_on_its_due_date_but_locks_the_day_after(): void
    {
        $course = Course::factory()->create(['duration_weeks' => 4, 'fee' => 100]);

        $onDueDate = Student::factory()->create();
        $enrollmentOnDueDate = $this->enroll($onDueDate, $course, [
            'enrolled_at' => now()->subDays(4)->toDateString(),
            'due_date' => now()->toDateString(),
        ]);
        $enrollmentOnDueDate->refreshStatus();
        $this->assertSame('active', $enrollmentOnDueDate->fresh()->status);

        $pastDueDate = Student::factory()->create();
        $enrollmentPastDueDate = $this->enroll($pastDueDate, $course, [
            'enrolled_at' => now()->subDays(5)->toDateString(),
            'due_date' => now()->subDay()->toDateString(),
        ]);
        $enrollmentPastDueDate->refreshStatus();
        $this->assertSame('locked', $enrollmentPastDueDate->fresh()->status);
        $this->assertSame('overdue_balance', $enrollmentPastDueDate->fresh()->locked_reason);
    }

    // ------------------------------------------------------------------
    // Attendance correction
    // ------------------------------------------------------------------

    public function test_correcting_attendance_after_completion_reverts_the_enrollment_and_recalculates_progress(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['duration_weeks' => 4, 'fee' => 100]);
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course);
        $this->pay($student, $course, 100);

        $attendances = [];
        for ($day = 1; $day <= 20; $day++) {
            $attendances[] = Attendance::factory()->create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'date' => now()->addDays($day)->toDateString(),
                'status' => 'present',
                'duration' => 1,
            ]);
        }
        $enrollment->refreshStatus();
        $this->assertSame('completed', $enrollment->fresh()->status);

        // A wrongly logged day is found and deleted after completion.
        $this->actingAs($user)->delete('/attendances/'.$attendances[0]->id)->assertSessionHasNoErrors();

        $this->assertSame(19, $enrollment->fresh()->attendedDays());
        $this->assertSame(1, $enrollment->fresh()->remainingTrainingDays());
        $this->assertSame(95, $enrollment->fresh()->trainingCompletionPercentage());
        $this->assertNotSame('completed', $enrollment->fresh()->status);

        // Re-logging the corrected day completes it again.
        $this->actingAs($user)->post('/attendances', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->addDays(21)->toDateString(),
            'status' => 'present',
        ])->assertSessionHasNoErrors();

        $this->assertSame('completed', $enrollment->fresh()->status);
    }

    // ------------------------------------------------------------------
    // Certificate duplication
    // ------------------------------------------------------------------

    public function test_a_certificate_cannot_be_issued_twice_for_the_same_completed_enrollment(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['duration_weeks' => 1, 'fee' => 100]);
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course);
        $this->pay($student, $course, 100);
        $this->attend($student, $course, 5);

        // Auto-completion issues the first certificate...
        $enrollment->refreshStatus();
        $this->assertDatabaseCount('certificates', 1);

        // ...running the same recompute again must not duplicate it.
        $enrollment->fresh()->refreshStatus();
        $this->assertDatabaseCount('certificates', 1);

        // Nor can one be manually issued a second time via the form.
        $response = $this->actingAs($user)->post('/certificates', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'issue_date' => now()->toDateString(),
        ]);
        $response->assertSessionHasErrors('student_id');
        $this->assertDatabaseCount('certificates', 1);
    }

    // ------------------------------------------------------------------
    // Director access
    // ------------------------------------------------------------------

    public function test_the_director_has_full_delete_and_finance_access(): void
    {
        $director = User::factory()->director()->create();
        $course = Course::factory()->create();
        $student = Student::factory()->create();
        $attendance = Attendance::factory()->create();
        $payment = Payment::factory()->create();
        $certificate = Certificate::factory()->create();

        $this->actingAs($director)->delete("/courses/{$course->id}")->assertRedirect('/courses');
        $this->actingAs($director)->delete("/students/{$student->id}")->assertRedirect('/students');
        $this->actingAs($director)->delete("/attendances/{$attendance->id}")->assertRedirect('/attendances');
        $this->actingAs($director)->delete("/payments/{$payment->id}")->assertRedirect('/payments');
        $this->actingAs($director)->delete("/certificates/{$certificate->id}")->assertRedirect('/certificates');

        $this->actingAs($director)->get('/finance')->assertOk();
        $this->actingAs($director)->get('/expenses')->assertOk();
        $this->actingAs($director)->get('/activity-log')->assertOk();
        $this->actingAs($director)->get('/users')->assertOk();
    }

    // ------------------------------------------------------------------
    // Secretary restriction
    // ------------------------------------------------------------------

    public function test_the_secretary_can_manage_operational_data_but_cannot_delete_or_reach_finance(): void
    {
        $secretary = User::factory()->secretary()->create();
        $course = Course::factory()->create();
        $student = Student::factory()->create();
        $attendance = Attendance::factory()->create();
        $payment = Payment::factory()->create();
        $certificate = Certificate::factory()->create();

        // Can operate day to day...
        $this->actingAs($secretary)->get('/courses/create')->assertOk();
        $this->actingAs($secretary)->get('/students/create')->assertOk();
        $this->actingAs($secretary)->get('/attendances/create')->assertOk();
        $this->actingAs($secretary)->get('/payments/create')->assertOk();

        // ...but cannot delete anything...
        $this->actingAs($secretary)->delete("/courses/{$course->id}")->assertForbidden();
        $this->actingAs($secretary)->delete("/students/{$student->id}")->assertForbidden();
        $this->actingAs($secretary)->delete("/attendances/{$attendance->id}")->assertForbidden();
        $this->actingAs($secretary)->delete("/payments/{$payment->id}")->assertForbidden();
        $this->actingAs($secretary)->delete("/certificates/{$certificate->id}")->assertForbidden();

        // ...and cannot reach the Director-only finance or staff sections.
        $this->actingAs($secretary)->get('/finance')->assertForbidden();
        $this->actingAs($secretary)->get('/expenses')->assertForbidden();
        $this->actingAs($secretary)->get('/activity-log')->assertForbidden();
        $this->actingAs($secretary)->get('/users')->assertForbidden();
    }
}
