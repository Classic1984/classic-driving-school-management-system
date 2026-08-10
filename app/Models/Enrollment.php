<?php

namespace App\Models;

use App\Notifications\EnrollmentLockedNotification;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\Notification;

class Enrollment extends Pivot
{
    protected $table = 'course_student';

    /**
     * This pivot table has its own auto-incrementing id.
     */
    public $incrementing = true;

    /**
     * The number of months a student has to complete training before it
     * is automatically locked, regardless of payment status.
     */
    public const TRAINING_PERIOD_MONTHS = 2;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enrolled_at' => 'date',
            'due_date' => 'date',
            'fee' => 'decimal:2',
            'original_fee' => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'reactivated_at' => 'date',
            'reactivation_fee' => 'decimal:2',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function discountApprovedBy()
    {
        return $this->belongsTo(User::class, 'discount_approved_by');
    }

    public function reactivatedBy()
    {
        return $this->belongsTo(User::class, 'reactivated_by');
    }

    /**
     * Whether this enrollment is locked specifically because its two-month
     * training period lapsed (as opposed to an overdue balance), which is
     * the only lock reason the Director can clear via reactivation.
     */
    public function isLockedForExpiredTrainingPeriod(): bool
    {
        return $this->status === 'locked' && $this->locked_reason === 'training_period_expired';
    }

    /**
     * The course's fee before any discount. Falls back to the (already
     * final) fee for enrollments recorded before discounts existed.
     */
    public function originalFee(): float
    {
        return (float) ($this->original_fee ?? $this->fee());
    }

    public function hasDiscount(): bool
    {
        return (float) ($this->discount_amount ?? 0) > 0;
    }

    /**
     * The total amount the student has paid toward this course so far.
     */
    public function amountPaid(): float
    {
        return (float) Payment::where('student_id', $this->student_id)
            ->where('course_id', $this->course_id)
            ->where('status', 'paid')
            ->sum('amount');
    }

    /**
     * The fee owed for this enrollment, locked in at the time the student
     * enrolled. Falls back to the course's current fee for enrollments
     * recorded before this fee was captured on the pivot.
     */
    public function fee(): float
    {
        return (float) ($this->fee ?? $this->course->fee);
    }

    /**
     * The remaining balance owed for this course.
     */
    public function balance(): float
    {
        return max(0, $this->fee() - $this->amountPaid());
    }

    public function isOverdue(): bool
    {
        return $this->balance() > 0
            && $this->due_date !== null
            && now()->toDateString() > $this->due_date->toDateString();
    }

    public function isTrainingPeriodExpired(): bool
    {
        return $this->enrolled_at !== null
            && $this->enrolled_at->copy()->addMonths(self::TRAINING_PERIOD_MONTHS)->isPast();
    }

    /**
     * The deadline by which this enrollment's training is expected to be
     * finished before it locks for an expired training period.
     */
    public function expectedCompletionDate(): ?\Illuminate\Support\Carbon
    {
        return $this->enrolled_at?->copy()->addMonths(self::TRAINING_PERIOD_MONTHS);
    }

    /**
     * The number of training days the student has used up for this course.
     * Each present training login counts for its own "duration" in days
     * (defaulting to 1), not just one day per login — this is what lets a
     * weekend student's single Saturday session count for 2 days and a
     * single Sunday session count for 3, covering a full training week
     * across just the weekend.
     */
    public function attendedDays(): int
    {
        return (int) Attendance::where('student_id', $this->student_id)
            ->where('course_id', $this->course_id)
            ->where('status', 'present')
            ->get()
            ->sum(fn (Attendance $attendance) => $attendance->duration ?? 1);
    }

    /**
     * Whether the student has attended all training days allocated to this
     * course.
     */
    public function hasCompletedTraining(): bool
    {
        return $this->attendedDays() >= $this->course->totalTrainingDays();
    }

    /**
     * The number of training days left before this course's allocation is
     * exhausted.
     */
    public function remainingTrainingDays(): int
    {
        return max(0, $this->course->totalTrainingDays() - $this->attendedDays());
    }

    /**
     * The percentage of allocated training days the student has attended
     * so far, capped at 100.
     */
    public function trainingCompletionPercentage(): int
    {
        $totalDays = $this->course->totalTrainingDays();

        if ($totalDays <= 0) {
            return 0;
        }

        return (int) min(100, round($this->attendedDays() / $totalDays * 100));
    }

    /**
     * A simplified training status for progress displays: locked
     * enrollments (whichever reason) read as "Expired" here, since from a
     * training-progress point of view they're no longer accruing days.
     */
    public function trainingStatusLabel(): string
    {
        return match ($this->status) {
            'completed' => 'Completed',
            'locked' => 'Expired',
            default => 'Active',
        };
    }

    /**
     * A human-readable explanation of why this enrollment is locked, for
     * display to staff (e.g. on the Dashboard's locked-students list). Null
     * when the enrollment isn't locked at all.
     */
    public function lockedReasonLabel(): ?string
    {
        return match ($this->locked_reason) {
            'training_period_expired' => 'Training Period Expired',
            'overdue_balance' => 'Overdue Balance',
            default => null,
        };
    }

    /**
     * Mark this enrollment completed and issue the student's certificate
     * for it, if one doesn't already exist. Used both when training
     * auto-completes and when staff complete it manually.
     */
    public function markCompleted(): void
    {
        $this->forceFill(['status' => 'completed', 'locked_reason' => null])->save();

        Certificate::firstOrCreate(
            ['student_id' => $this->student_id, 'course_id' => $this->course_id],
            ['certificate_number' => Certificate::numberFor($this->student, $this->course), 'issue_date' => now()->toDateString()]
        );

        $this->student->refreshStatus();
    }

    /**
     * Recompute and persist this enrollment's locked/active status based on
     * the current payment and training-period rules. Runs immediately after
     * a payment is recorded or a training login is saved, and daily via a
     * scheduled command to catch due-dates and the training-period deadline
     * passing on their own.
     */
    public function refreshStatus(): void
    {
        if ($this->status === 'completed') {
            return;
        }

        // Training completion depends only on attendance, never on payment
        // status - a student who has attended every required day completes
        // their training even with a balance outstanding, which then remains
        // a separate financial matter tracked via balance()/isOverdue().
        if ($this->hasCompletedTraining()) {
            $this->markCompleted();

            return;
        }

        $wasLockedForOverdueBalance = $this->status === 'locked' && $this->locked_reason === 'overdue_balance';

        if ($this->isOverdue()) {
            $newStatus = 'locked';
            $newReason = 'overdue_balance';
        } elseif ($this->isTrainingPeriodExpired()) {
            $newStatus = 'locked';
            $newReason = 'training_period_expired';
        } else {
            $newStatus = 'active';
            $newReason = null;
        }

        if ($newStatus !== $this->status || $newReason !== $this->locked_reason) {
            $this->forceFill(['status' => $newStatus, 'locked_reason' => $newReason])->save();

            if ($newStatus === 'locked' && $newReason === 'overdue_balance' && ! $wasLockedForOverdueBalance) {
                Notification::send(User::admins()->get(), new EnrollmentLockedNotification($this));
            }
        }
    }
}
