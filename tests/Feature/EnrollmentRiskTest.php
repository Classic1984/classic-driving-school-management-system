<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentRiskTest extends TestCase
{
    use RefreshDatabase;

    protected function activeEnrollment(array $overrides = []): Enrollment
    {
        $course = Course::factory()->create(['fee' => 100]);
        $student = Student::factory()->create();
        $course->students()->attach($student->id, array_merge([
            'enrolled_at' => now()->subDays(10)->toDateString(),
            'status' => 'active',
        ], $overrides));

        return Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();
    }

    public function test_enrollment_is_attendance_risk_after_the_threshold_with_no_training_login(): void
    {
        $enrollment = $this->activeEnrollment(['enrolled_at' => now()->subDays(10)->toDateString()]);

        $this->assertTrue($enrollment->isAttendanceRisk());
        $this->assertSame('medium', $enrollment->riskLevel());
    }

    public function test_a_fully_paid_enrollment_is_never_attendance_risk_no_matter_how_long_absent(): void
    {
        $enrollment = $this->activeEnrollment(['enrolled_at' => now()->subDays(60)->toDateString()]);
        Payment::factory()->create([
            'student_id' => $enrollment->student_id,
            'course_id' => $enrollment->course_id,
            'amount' => 100,
            'status' => 'paid',
        ]);

        $this->assertSame(0.0, $enrollment->fresh()->balance());
        $this->assertFalse($enrollment->fresh()->isAttendanceRisk());
        $this->assertNull($enrollment->fresh()->riskLevel());
    }

    public function test_enrollment_is_not_attendance_risk_before_the_threshold(): void
    {
        $enrollment = $this->activeEnrollment(['enrolled_at' => now()->subDays(3)->toDateString()]);

        $this->assertFalse($enrollment->isAttendanceRisk());
        $this->assertNull($enrollment->riskLevel());
    }

    public function test_attendance_risk_uses_the_last_training_login_not_the_enrollment_date(): void
    {
        $enrollment = $this->activeEnrollment(['enrolled_at' => now()->subDays(30)->toDateString()]);

        Attendance::factory()->create([
            'student_id' => $enrollment->student_id,
            'course_id' => $enrollment->course_id,
            'status' => 'present',
            'date' => now()->subDays(2)->toDateString(),
        ]);

        $this->assertSame(2, $enrollment->daysSinceLastTraining());
        $this->assertFalse($enrollment->isAttendanceRisk());
    }

    public function test_enrollment_is_payment_risk_when_balance_is_due_soon_but_not_overdue(): void
    {
        $enrollment = $this->activeEnrollment([
            'enrolled_at' => now()->toDateString(),
            'due_date' => now()->addDays(3)->toDateString(),
        ]);

        Attendance::factory()->create([
            'student_id' => $enrollment->student_id,
            'course_id' => $enrollment->course_id,
            'status' => 'present',
            'date' => now()->toDateString(),
        ]);

        $this->assertTrue($enrollment->isPaymentRisk());
        $this->assertSame('medium', $enrollment->riskLevel());
    }

    public function test_enrollment_is_not_payment_risk_when_due_date_is_too_far_away(): void
    {
        $enrollment = $this->activeEnrollment(['due_date' => now()->addDays(10)->toDateString()]);

        $this->assertFalse($enrollment->isPaymentRisk());
    }

    public function test_enrollment_is_not_payment_risk_once_it_is_already_overdue(): void
    {
        $enrollment = $this->activeEnrollment(['due_date' => now()->subDays(2)->toDateString()]);

        $this->assertTrue($enrollment->isOverdue());
        $this->assertFalse($enrollment->isPaymentRisk());
    }

    public function test_enrollment_is_high_risk_when_both_signals_are_present(): void
    {
        $enrollment = $this->activeEnrollment([
            'enrolled_at' => now()->subDays(10)->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
        ]);

        $this->assertTrue($enrollment->isAttendanceRisk());
        $this->assertTrue($enrollment->isPaymentRisk());
        $this->assertSame('high', $enrollment->riskLevel());
        $this->assertCount(2, $enrollment->riskReasons());
    }

    public function test_a_locked_enrollment_is_never_flagged_at_risk(): void
    {
        $enrollment = $this->activeEnrollment([
            'enrolled_at' => now()->subDays(10)->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
            'status' => 'locked',
            'locked_reason' => 'overdue_balance',
        ]);

        $this->assertFalse($enrollment->isAtRisk());
    }

    public function test_a_completed_enrollment_is_never_flagged_at_risk(): void
    {
        $enrollment = $this->activeEnrollment([
            'enrolled_at' => now()->subDays(10)->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
            'status' => 'completed',
        ]);

        $this->assertFalse($enrollment->isAtRisk());
    }

    public function test_risk_reasons_describe_days_absent_and_days_until_due(): void
    {
        $enrollment = $this->activeEnrollment([
            'enrolled_at' => now()->subDays(9)->toDateString(),
            'due_date' => now()->addDays(4)->toDateString(),
        ]);

        $reasons = $enrollment->riskReasons();

        $this->assertContains('Absent 9 day(s)', $reasons);
        $this->assertContains('Payment due in 4 day(s)', $reasons);
    }
}
