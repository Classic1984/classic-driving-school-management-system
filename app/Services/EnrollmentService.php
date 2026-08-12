<?php

namespace App\Services;

use App\Models\Course;
use App\Models\DiscountAuditLog;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Enrolls a student into a course - the shared logic behind both
 * "register a new student with a course" (StudentController::store) and
 * "enroll an already-registered student into a course"
 * (EnrollmentController::store), so the two flows can never drift apart
 * on fee snapshotting, discounts, or grace periods.
 */
class EnrollmentService
{
    /**
     * Attach the student to the course, snapshotting its fee (minus any
     * discount) and certificate fees, logging a discount audit entry if a
     * discount was applied, and recording an initial payment if one was
     * given.
     *
     * @param  array{starts_double_period?: bool, discount_choice?: ?string, custom_discount_percentage?: ?float, custom_discount_amount?: ?float, discount_reason?: ?string, discount_reason_note?: ?string, amount_paid?: ?float, payment_method?: ?string}  $data
     */
    public function enroll(Student $student, Course $course, User $actor, Carbon $enrolledAt, array $data): Enrollment
    {
        $originalFee = (float) $course->fee;
        [$discountPercentage, $discountAmount] = $this->resolveDiscount(
            $data['discount_choice'] ?? null,
            (float) ($data['custom_discount_percentage'] ?? 0),
            (float) ($data['custom_discount_amount'] ?? 0),
            $originalFee,
        );
        $finalFee = max(0, $originalFee - $discountAmount);

        // A weekday student starting double period immediately burns through
        // 4 training days in just 2 calendar days, so their balance is due
        // that much sooner than the course's normal grace period.
        $gracePeriodDays = (! $course->isWeekend() && ! empty($data['starts_double_period']))
            ? 2
            : $course->gracePeriodDays();

        $student->courses()->attach($course->id, [
            'enrolled_at' => $enrolledAt->toDateString(),
            'due_date' => $enrolledAt->copy()->addDays($gracePeriodDays)->toDateString(),
            'status' => 'active',
            'fee' => $finalFee,
            'original_fee' => $originalFee,
            'discount_percentage' => $discountAmount > 0 ? $discountPercentage : null,
            'discount_amount' => $discountAmount > 0 ? $discountAmount : null,
            'discount_reason' => $discountAmount > 0 ? ($data['discount_reason'] ?? null) : null,
            'discount_reason_note' => $discountAmount > 0 ? ($data['discount_reason_note'] ?? null) : null,
            'discount_approved_by' => $discountAmount > 0 ? $actor->id : null,
            // Certificate fees are part of the course outline, not an
            // opt-in add-on - every student enrolling in a course that
            // offers a certificate is charged for it from day one, the
            // same way the training fee itself is locked in here.
            'online_certificate_fee' => $course->online_certificate_fee,
            'student_certificate_fee' => $course->student_certificate_fee,
        ]);

        $enrollment = $student->courses()->where('course_id', $course->id)->first()->pivot;

        if ($discountAmount > 0) {
            DiscountAuditLog::create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'applied_by' => $actor->id,
                'original_fee' => $originalFee,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
                'final_fee' => $finalFee,
                'reason' => $data['discount_reason'] ?? null,
                'reason_note' => $data['discount_reason_note'] ?? null,
            ]);
        }

        if (! empty($data['amount_paid'])) {
            Payment::create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'amount' => $data['amount_paid'],
                'payment_date' => $enrolledAt->toDateString(),
                'payment_method' => $data['payment_method'] ?? null,
                'status' => 'paid',
                'recorded_by' => $actor->id,
            ]);
        }

        return $enrollment;
    }

    /**
     * Work out the discount percentage and naira amount from the
     * validated discount fields. Presets are fixed naira amounts; a
     * custom discount is either a percentage or a fixed amount (not
     * both).
     *
     * @return array{0: ?float, 1: float}
     */
    public function resolveDiscount(?string $choice, float $customPercentage, float $customAmount, float $originalFee): array
    {
        if (! $choice) {
            return [null, 0.0];
        }

        if ($choice === 'custom') {
            if ($customAmount > 0) {
                $amount = min($customAmount, $originalFee);

                return [
                    $originalFee > 0 ? round($amount / $originalFee * 100, 2) : 0.0,
                    round($amount, 2),
                ];
            }

            return [$customPercentage, round($originalFee * $customPercentage / 100, 2)];
        }

        // Presets are fixed naira amounts, not percentages.
        $amount = min((float) $choice, $originalFee);

        return [
            $originalFee > 0 ? round($amount / $originalFee * 100, 2) : 0.0,
            round($amount, 2),
        ];
    }
}
