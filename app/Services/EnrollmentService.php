<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\DiscountAuditLog;
use App\Models\DiscountRequest;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\ProgrammeUpgradeLog;
use App\Models\Student;
use App\Models\User;
use App\Notifications\DiscountRequestedNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

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
     * Director-applied discount) and certificate fees, logging a discount
     * audit entry if a discount was applied, and recording an initial
     * payment if one was given.
     *
     * A discount requested by anyone other than a Director doesn't take
     * effect here - the enrollment is created at the full course fee, and
     * a pending DiscountRequest is raised for a Director to approve (which
     * applies it) or reject (which leaves the fee as-is) separately.
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

        $discountNeedsApproval = $discountAmount > 0 && ! $actor->isDirector();
        $appliedDiscountAmount = $discountNeedsApproval ? 0.0 : $discountAmount;
        $finalFee = max(0, $originalFee - $appliedDiscountAmount);

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
            'discount_percentage' => $appliedDiscountAmount > 0 ? $discountPercentage : null,
            'discount_amount' => $appliedDiscountAmount > 0 ? $appliedDiscountAmount : null,
            'discount_reason' => $appliedDiscountAmount > 0 ? ($data['discount_reason'] ?? null) : null,
            'discount_reason_note' => $appliedDiscountAmount > 0 ? ($data['discount_reason_note'] ?? null) : null,
            'discount_approved_by' => $appliedDiscountAmount > 0 ? $actor->id : null,
            // Certificate fees are part of the course outline, not an
            // opt-in add-on - every student enrolling in a course that
            // offers a certificate is charged for it from day one, the
            // same way the training fee itself is locked in here.
            'online_certificate_fee' => $course->online_certificate_fee,
            'student_certificate_fee' => $course->student_certificate_fee,
        ]);

        $enrollment = $student->courses()->where('course_id', $course->id)->first()->pivot;

        if ($appliedDiscountAmount > 0) {
            DiscountAuditLog::create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'applied_by' => $actor->id,
                'original_fee' => $originalFee,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $appliedDiscountAmount,
                'final_fee' => $finalFee,
                'reason' => $data['discount_reason'] ?? null,
                'reason_note' => $data['discount_reason_note'] ?? null,
            ]);
        }

        if ($discountNeedsApproval) {
            $discountRequest = DiscountRequest::create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'enrollment_id' => $enrollment->id,
                'requested_by' => $actor->id,
                'original_fee' => $originalFee,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
                'final_fee' => max(0, $originalFee - $discountAmount),
                'reason' => $data['discount_reason'] ?? null,
                'reason_note' => $data['discount_reason_note'] ?? null,
            ]);

            Notification::send(User::where('role', 'director')->get(), new DiscountRequestedNotification($discountRequest));
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

        $enrollment->notifyUpgradeWindowIfEligible();

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

    /**
     * Upgrade an enrollment to a longer programme, per the Programme
     * Upgrade Policy: the student pays only the difference between the
     * new programme's fee and what this enrollment's fee already was (an
     * existing naira discount carries over onto the new fee), and their
     * training progress is preserved by moving their existing attendance
     * records onto the new course rather than resetting them. Eligibility
     * (the five-day window, the course being a valid longer programme) is
     * expected to already be validated by the caller - this method
     * assumes it and just executes the change.
     */
    public function upgrade(Enrollment $enrollment, Course $newCourse, User $actor, float $amountToPayNow, ?string $paymentMethod, Carbon $upgradedAt): void
    {
        DB::transaction(function () use ($enrollment, $newCourse, $actor, $amountToPayNow, $paymentMethod, $upgradedAt) {
            $previousFee = $enrollment->fee();
            $newFee = $this->upgradedFee($enrollment, $newCourse);
            $newOriginalFee = (float) $newCourse->fee;
            $discountAmount = (float) ($enrollment->discount_amount ?? 0);
            $upgradeCost = $this->upgradeCost($enrollment, $newCourse);
            $attendedDaysAtUpgrade = $enrollment->attendedDays();
            $fromCourseId = $enrollment->course_id;

            // Preserve training progress: the days already attended toward
            // the old course count toward the new (longer) one instead of
            // resetting to zero.
            Attendance::where('student_id', $enrollment->student_id)
                ->where('course_id', $fromCourseId)
                ->update(['course_id' => $newCourse->id]);

            $enrollment->forceFill([
                'course_id' => $newCourse->id,
                'fee' => $newFee,
                'original_fee' => $newOriginalFee,
                'discount_percentage' => $discountAmount > 0 && $newOriginalFee > 0
                    ? round($discountAmount / $newOriginalFee * 100, 2)
                    : $enrollment->discount_percentage,
                'due_date' => ($enrollment->enrolled_at ?? $upgradedAt)->copy()->addDays($newCourse->gracePeriodDays())->toDateString(),
            ])->save();

            $amountToPayNow = min(max(0, $amountToPayNow), $upgradeCost);

            if ($amountToPayNow > 0) {
                $payment = Payment::create([
                    'student_id' => $enrollment->student_id,
                    'course_id' => null,
                    'amount' => $amountToPayNow,
                    'payment_date' => $upgradedAt->toDateString(),
                    'payment_method' => $paymentMethod,
                    'status' => 'paid',
                    'recorded_by' => $actor->id,
                ]);

                PaymentAllocation::create([
                    'payment_id' => $payment->id,
                    'allocation_type' => 'training',
                    'enrollment_id' => $enrollment->id,
                    'amount' => $amountToPayNow,
                ]);
            }

            ProgrammeUpgradeLog::create([
                'student_id' => $enrollment->student_id,
                'enrollment_id' => $enrollment->id,
                'from_course_id' => $fromCourseId,
                'to_course_id' => $newCourse->id,
                'upgraded_by' => $actor->id,
                'attended_days_at_upgrade' => $attendedDaysAtUpgrade,
                'previous_fee' => $previousFee,
                'new_fee' => $newFee,
                'amount_charged' => $upgradeCost,
            ]);
        });

        $enrollment->load('course');
        $enrollment->reconcile();
    }

    /**
     * What this enrollment's fee would become after upgrading to the
     * given course: the new course's fee, minus this enrollment's
     * existing naira discount (if any) carried over as-is.
     */
    public function upgradedFee(Enrollment $enrollment, Course $newCourse): float
    {
        return max(0, (float) $newCourse->fee - (float) ($enrollment->discount_amount ?? 0));
    }

    /**
     * How much more the student owes to upgrade this enrollment to the
     * given course: the difference between the upgraded fee and what
     * this enrollment's fee already is. Never negative - a course that
     * isn't actually more expensive after the carried-over discount costs
     * nothing extra.
     */
    public function upgradeCost(Enrollment $enrollment, Course $newCourse): float
    {
        return max(0, $this->upgradedFee($enrollment, $newCourse) - $enrollment->fee());
    }
}
