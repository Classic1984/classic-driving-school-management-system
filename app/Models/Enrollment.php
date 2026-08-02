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
     * The remaining balance owed for this course.
     */
    public function balance(): float
    {
        return max(0, (float) $this->course->fee - $this->amountPaid());
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
     * Recompute and persist this enrollment's locked/active status based on
     * the current payment and training-period rules. Runs immediately after
     * a payment is recorded, and daily via a scheduled command to catch
     * due-dates and the training-period deadline passing on their own.
     */
    public function refreshStatus(): void
    {
        if ($this->status === 'completed') {
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
