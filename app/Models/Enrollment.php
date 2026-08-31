<?php

namespace App\Models;

use App\Notifications\EnrollmentLockedNotification;
use App\Notifications\TrainingCompletedNotification;
use App\Notifications\TrainingDaysRemainingNotification;
use App\Services\TermiiSmsService;
use App\Services\WebPushService;
use App\Services\WhatsAppService;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
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
     * Remaining training days at or below this number trigger a one-time
     * "nearing completion" reminder to staff.
     */
    public const TRAINING_DAYS_REMAINING_THRESHOLD = 3;

    /**
     * A student may upgrade to a longer programme only through their
     * fifth completed training day - see the Programme Upgrade Policy.
     */
    public const UPGRADE_WINDOW_DAYS = 5;

    /**
     * Days without a fresh training login before an active enrollment is
     * flagged for dropout risk - deliberately higher than the 4-day
     * threshold for the automatic absence check-in text (see
     * SendAbsenceCheckInReminder), since this is meant to catch students
     * that automatic nudge didn't bring back, not every normal gap
     * between sessions.
     */
    public const ABSENCE_RISK_THRESHOLD_DAYS = 7;

    /**
     * How many days out from an unpaid balance's due date staff should be
     * proactively warned, before the balance actually goes overdue and
     * the enrollment locks on its own.
     */
    public const PAYMENT_RISK_WINDOW_DAYS = 5;

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
            'training_reminder_sent_at' => 'datetime',
            'online_certificate_fee' => 'decimal:2',
            'student_certificate_fee' => 'decimal:2',
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
     * The total amount the student has paid toward this course so far,
     * summed from the training payment allocations recorded against this
     * enrollment (each backed by a payment currently marked "paid").
     */
    public function amountPaid(): float
    {
        return (float) PaymentAllocation::where('enrollment_id', $this->id)
            ->where('allocation_type', 'training')
            ->whereHas('payment', fn ($query) => $query->where('status', 'paid'))
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

    /**
     * The remaining balance owed for this enrollment's online certificate
     * charge, or null if the student hasn't been charged for one.
     */
    public function onlineCertificateBalance(): ?float
    {
        return $this->certificateBalance('online_certificate');
    }

    /**
     * The remaining balance owed for this enrollment's student certificate
     * charge, or null if the student hasn't been charged for one.
     */
    public function studentCertificateBalance(): ?float
    {
        return $this->certificateBalance('student_certificate');
    }

    /**
     * The remaining balance for a course-outline certificate charge
     * ('online_certificate' or 'student_certificate'), or null if this
     * enrollment was never charged for that certificate type.
     */
    protected function certificateBalance(string $type): ?float
    {
        $fee = $this->{"{$type}_fee"};

        if ($fee === null) {
            return null;
        }

        return max(0, (float) $fee - $this->certificateAmountPaid($type));
    }

    /**
     * The total amount paid toward a course-outline certificate charge so
     * far, across every payment allocation recorded against it.
     */
    protected function certificateAmountPaid(string $type): float
    {
        return (float) PaymentAllocation::where('enrollment_id', $this->id)
            ->where('allocation_type', $type)
            ->whereHas('payment', fn ($query) => $query->where('status', 'paid'))
            ->sum('amount');
    }

    public function isOverdue(): bool
    {
        return $this->balance() > 0
            && $this->due_date !== null
            && now()->toDateString() > $this->due_date->toDateString();
    }

    /**
     * This enrollment's most recent present training login for its
     * course, or null if the student has never trained in it yet.
     */
    public function lastTrainingDate(): ?Carbon
    {
        return Attendance::where('student_id', $this->student_id)
            ->where('course_id', $this->course_id)
            ->where('status', 'present')
            ->latest('date')
            ->first()
            ?->date;
    }

    /**
     * Days since this enrollment's student last trained in this course,
     * or since they enrolled if they've never trained in it at all.
     */
    public function daysSinceLastTraining(): int
    {
        $referenceDate = $this->lastTrainingDate() ?? $this->enrolled_at;

        return $referenceDate === null ? 0 : $referenceDate->diffInDays(now()->startOfDay());
    }

    /**
     * Whether this active enrollment has gone quiet long enough to flag
     * for dropout risk - see ABSENCE_RISK_THRESHOLD_DAYS. Only applies
     * while a balance is still owed: a fully paid student who simply
     * hasn't come in for a while isn't a risk to anything, since there's
     * no money or unfinished obligation at stake either way.
     */
    public function isAttendanceRisk(): bool
    {
        return $this->status === 'active'
            && $this->balance() > 0
            && $this->daysSinceLastTraining() >= self::ABSENCE_RISK_THRESHOLD_DAYS;
    }

    /**
     * Whether this active enrollment's balance is due soon but not yet
     * overdue - a proactive warning before it locks on its own (an
     * already-overdue balance is surfaced separately, once it locks).
     */
    public function isPaymentRisk(): bool
    {
        if ($this->status !== 'active' || $this->balance() <= 0 || $this->due_date === null) {
            return false;
        }

        $today = now()->toDateString();
        $dueDate = $this->due_date->toDateString();
        $warningStart = $this->due_date->copy()->subDays(self::PAYMENT_RISK_WINDOW_DAYS)->toDateString();

        return $dueDate >= $today && $today >= $warningStart;
    }

    /**
     * Human-readable reasons this enrollment is currently flagged at
     * risk, for display next to it - empty when it isn't at risk.
     */
    public function riskReasons(): array
    {
        $reasons = [];

        if ($this->isAttendanceRisk()) {
            $reasons[] = "Absent {$this->daysSinceLastTraining()} day(s)";
        }

        if ($this->isPaymentRisk()) {
            $daysUntilDue = now()->startOfDay()->diffInDays($this->due_date);
            $reasons[] = $daysUntilDue > 0
                ? "Payment due in {$daysUntilDue} day(s)"
                : 'Payment due today';
        }

        return $reasons;
    }

    /**
     * "high" when both the attendance and payment risk signals are
     * present at once - these students are the most likely to be lost
     * entirely - "medium" when only one signal is present, null when
     * neither is (not at risk).
     */
    public function riskLevel(): ?string
    {
        $signals = (int) $this->isAttendanceRisk() + (int) $this->isPaymentRisk();

        return match ($signals) {
            2 => 'high',
            1 => 'medium',
            default => null,
        };
    }

    public function isAtRisk(): bool
    {
        return $this->riskLevel() !== null;
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
    public function expectedCompletionDate(): ?Carbon
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
     * Whether this enrollment is still within its five-completed-day
     * programme upgrade window. Attended days 0 through 5 (inclusive) are
     * eligible; day 6 onward, or a completed enrollment, are not.
     */
    public function isWithinUpgradeWindow(): bool
    {
        return $this->status !== 'completed' && $this->attendedDays() <= self::UPGRADE_WINDOW_DAYS;
    }

    /**
     * How many completed training days remain in the upgrade window, 0
     * once it has closed.
     */
    public function upgradeDaysRemaining(): int
    {
        return max(0, self::UPGRADE_WINDOW_DAYS - $this->attendedDays());
    }

    /**
     * The longer programmes this enrollment could upgrade into: active
     * courses of the same type and schedule (so an upgrade never changes
     * the student's manual/automatic track or weekday/weekend slot) with
     * more training weeks than the current course - e.g. a 2-week weekday
     * manual course upgrades only into a longer weekday manual course.
     */
    public function eligibleUpgradeCourses()
    {
        // course_student has a unique (course_id, student_id) constraint -
        // a course the student already holds any enrollment in (regardless
        // of that enrollment's status) can't be upgraded into without
        // violating it, so it's excluded here rather than surfacing as an
        // uncaught duplicate-key error at save time.
        $alreadyEnrolledCourseIds = $this->student->courses()->pluck('courses.id');

        return Course::where('status', 'active')
            ->where('course_type', $this->course->course_type)
            ->where('schedule', $this->course->schedule)
            ->where('duration_weeks', '>', $this->course->duration_weeks)
            ->whereNotIn('id', $alreadyEnrolledCourseIds)
            ->orderBy('duration_weeks')
            ->get();
    }

    /**
     * Whether this enrollment can be upgraded right now: still within the
     * five-day window, and at least one longer programme exists to
     * upgrade into.
     */
    public function canUpgrade(): bool
    {
        return $this->isWithinUpgradeWindow() && $this->eligibleUpgradeCourses()->isNotEmpty();
    }

    /**
     * The Director/staff-facing label for this enrollment's upgrade
     * eligibility, per the Programme Upgrade Policy's staff view.
     */
    public function upgradeStatusLabel(): string
    {
        if ($this->status === 'completed' || $this->eligibleUpgradeCourses()->isEmpty()) {
            return 'Not Available';
        }

        return $this->isWithinUpgradeWindow() ? 'Eligible' : 'Closed';
    }

    /**
     * A short explanation for why the upgrade status above isn't
     * "Eligible", for display next to it. Null while eligible.
     */
    public function upgradeStatusReason(): ?string
    {
        if ($this->status === 'completed') {
            return 'Programme completed - new registration required.';
        }

        if ($this->eligibleUpgradeCourses()->isEmpty()) {
            return 'No longer programme is available to upgrade into.';
        }

        if (! $this->isWithinUpgradeWindow()) {
            return 'Five-day upgrade period exceeded.';
        }

        return null;
    }

    /**
     * Real aggregate counts across every enrollment, for the "Student
     * Training Progress" widget's header stats - shared by the dashboard
     * widget and the Enrolled Trainees page, both of which only display a
     * handful of cards but need the true totals behind them. "In progress"
     * and "not started" both read status=active, split by whether any
     * training day has actually been logged yet (see statusLabel()).
     *
     * @return array{total_students: int, in_progress: int, completed: int, not_started: int, overall_progress: int, non_experience: int, auto_programs: int, manual_programs: int, highest_progress: int, average_progress: int, lowest_progress: int}
     */
    public static function trainingProgressStats(): array
    {
        $activeEnrollments = static::where('status', 'active')->with(['course', 'student'])->get();
        $percentages = $activeEnrollments->map(fn (self $enrollment) => $enrollment->trainingCompletionPercentage());

        return [
            'total_students' => static::distinct('student_id')->count('student_id'),
            'in_progress' => $activeEnrollments->filter(fn (self $enrollment) => $enrollment->attendedDays() > 0)->count(),
            'completed' => static::where('status', 'completed')->count(),
            'not_started' => $activeEnrollments->filter(fn (self $enrollment) => $enrollment->attendedDays() === 0)->count(),
            'overall_progress' => $percentages->isEmpty() ? 0 : (int) round($percentages->avg()),
            'non_experience' => $activeEnrollments->filter(fn (self $enrollment) => $enrollment->student->has_driving_experience === false)->count(),
            'auto_programs' => $activeEnrollments->filter(fn (self $enrollment) => $enrollment->course->course_type === 'automatic')->count(),
            'manual_programs' => $activeEnrollments->filter(fn (self $enrollment) => $enrollment->course->course_type === 'manual')->count(),
            'highest_progress' => $percentages->isEmpty() ? 0 : (int) $percentages->max(),
            'average_progress' => $percentages->isEmpty() ? 0 : (int) round($percentages->avg()),
            'lowest_progress' => $percentages->isEmpty() ? 0 : (int) $percentages->min(),
        ];
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
     * A five-stage status label - Registered, Active, Locked, Completed, or
     * Certified - layered on top of the real stored `status` column
     * (active/locked/completed) purely for display. Nothing is persisted
     * here and the underlying column is untouched, so every existing
     * feature that reads `status` directly keeps working; this just gives
     * staff the richer REGISTERED -> ACTIVE -> LOCKED -> COMPLETED ->
     * CERTIFIED picture wherever an enrollment's stage is shown.
     */
    public function statusLabel(): string
    {
        if ($this->status === 'completed') {
            return $this->hasCertificate() ? 'Certified' : 'Completed';
        }

        if ($this->status === 'locked') {
            return 'Locked';
        }

        return $this->attendedDays() === 0 ? 'Registered' : 'Active';
    }

    /**
     * Whether a certificate has been issued for this enrollment.
     */
    public function hasCertificate(): bool
    {
        return Certificate::where('student_id', $this->student_id)
            ->where('course_id', $this->course_id)
            ->exists();
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
     * Mark this enrollment completed. Training days attended and balance
     * cleared are no longer enough on their own to issue a certificate -
     * see maybeIssueCertificate() - so this only advances the enrollment
     * to "Completed" and lets the student know their final practical
     * assessment is what's standing between them and their certificate.
     * Used both when training auto-completes and when staff complete it
     * manually.
     */
    public function markCompleted(): void
    {
        $this->forceFill(['status' => 'completed', 'locked_reason' => null])->save();

        // Runs before the admin notification below (not after) so that
        // TrainingCompletedNotification's hasCertificate() check reflects
        // the real outcome of this same call - covers the order-of-events
        // where a passing assessment was already on file before the
        // training-days/balance side of completion caught up to it.
        $this->maybeIssueCertificate();

        Notification::send(User::admins()->get(), new TrainingCompletedNotification($this));
        $this->textStudent(
            'training_completed',
            "Classic Driving School: Congratulations! You have completed your training program in {$this->course->name}. Your certificate will be issued once your final practical assessment is confirmed.",
            ['1' => $this->course->name]
        );

        $this->student->refreshStatus();
    }

    /**
     * The current (most recently recorded) assessment for this
     * enrollment, or null if none has been recorded yet.
     */
    public function assessment(): ?Assessment
    {
        return Assessment::where('student_id', $this->student_id)
            ->where('course_id', $this->course_id)
            ->first();
    }

    /**
     * Whether this enrollment has a passing final assessment on file.
     */
    public function hasPassedAssessment(): bool
    {
        return $this->assessment()?->result === 'pass';
    }

    /**
     * Issue this enrollment's certificate if - and only if - training is
     * both fully completed (attendance + balance, see refreshStatus()/
     * reconcile()) and a passing final assessment is on file. Either of
     * those two things can happen first (a student might be assessed
     * before their last training day, or the reverse), so this is called
     * from both markCompleted() and wherever an assessment is recorded -
     * it's a no-op once a certificate already exists.
     */
    public function maybeIssueCertificate(): void
    {
        if ($this->status !== 'completed' || ! $this->hasPassedAssessment() || $this->hasCertificate()) {
            return;
        }

        $certificate = Certificate::firstOrCreate(
            ['student_id' => $this->student_id, 'course_id' => $this->course_id],
            ['issue_date' => now()->toDateString()]
        );

        if (! $certificate->wasRecentlyCreated) {
            return;
        }

        ActivityLog::record("Issued certificate {$certificate->certificate_number} for {$this->student->name} ({$this->course->name})");
        $this->textStudent(
            'certificate_ready',
            "Classic Driving School: Your certificate for {$this->course->name} is ready for collection at the school office.",
            ['1' => $this->course->name]
        );
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

        // Completion requires both the required training days attended and
        // the balance cleared - a student never completes while still
        // owing money, no matter how many days they've attended.
        if ($this->balance() <= 0 && $this->hasCompletedTraining()) {
            $this->markCompleted();

            return;
        }

        $this->applyLockingRules();
        $this->maybeNotifyDaysRemaining();
    }

    /**
     * Recompute this enrollment's status from scratch against its current
     * attendance and balance - including reverting a "completed" status
     * back to active/locked if the numbers no longer support it. Used
     * whenever something that feeds hasCompletedTraining()/balance() is
     * deliberately corrected after the fact: an attendance record being
     * added, corrected, or removed, or a course's required training days
     * being edited (which changes totalTrainingDays() for every enrollment
     * in that course). Ordinary refreshStatus() deliberately never
     * un-completes an enrollment, so routine due-date or training-period
     * drift can't relock a finished student - only a deliberate correction
     * like this should be able to. Any certificate already issued is left
     * on file rather than deleted automatically.
     */
    public function reconcile(): void
    {
        if ($this->balance() <= 0 && $this->hasCompletedTraining()) {
            if ($this->status !== 'completed') {
                $this->markCompleted();
            }

            return;
        }

        if ($this->status === 'completed') {
            $this->forceFill(['status' => 'active', 'locked_reason' => null])->save();
        }

        $this->applyLockingRules();
        $this->maybeNotifyDaysRemaining();

        $this->student->refreshStatus();
    }

    /**
     * Apply the overdue-balance / expired-training-period / active locking
     * rules and persist the result, notifying admins the first time an
     * enrollment locks for an overdue balance.
     */
    protected function applyLockingRules(): void
    {
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

    /**
     * Notify staff once, the first time this enrollment's remaining
     * training days drop to (or below) the reminder threshold. Re-arms if
     * an attendance correction pushes remaining days back above the
     * threshold, so a later re-approach notifies again.
     */
    protected function maybeNotifyDaysRemaining(): void
    {
        $remaining = $this->remainingTrainingDays();
        $withinThreshold = $remaining > 0 && $remaining <= self::TRAINING_DAYS_REMAINING_THRESHOLD;

        if ($withinThreshold && $this->training_reminder_sent_at === null) {
            Notification::send(User::admins()->get(), new TrainingDaysRemainingNotification($this));
            $this->textStudent(
                'training_days_remaining',
                "Classic Driving School: You have {$remaining} training day(s) remaining in {$this->course->name}. Keep up the great work!",
                ['1' => (string) $remaining, '2' => $this->course->name]
            );
            $this->forceFill(['training_reminder_sent_at' => now()])->save();
        } elseif (! $withinThreshold && $this->training_reminder_sent_at !== null) {
            $this->forceFill(['training_reminder_sent_at' => null])->save();
        }
    }

    /**
     * Text the newly-enrolled student about the programme upgrade window,
     * if this course actually has a longer variant to upgrade into.
     * Called once, right after enrollment - see EnrollmentService::enroll().
     */
    public function notifyUpgradeWindowIfEligible(): void
    {
        if ($this->eligibleUpgradeCourses()->isEmpty()) {
            return;
        }

        $this->textStudent(
            'programme_upgrade_window',
            'Welcome to Classic Driving School! If you wish to upgrade your training programme, you may do so within your first five completed training days. '
                .'You can upgrade from 2 weeks to 3 or 4 weeks, or from 3 weeks to 4 weeks. '
                .'After the fifth completed training day, programme upgrades will no longer be available. Please speak with the school office if you wish to upgrade.'
        );
    }

    /**
     * Text this enrollment's student directly (SMS, falling back to
     * WhatsApp), logging the attempt the same way the scheduled reminder
     * commands do - giving staff the same Message Log visibility into
     * these student-facing texts. Failures are swallowed (and logged)
     * rather than thrown, so a messaging provider outage never blocks the
     * status change that triggered it.
     */
    protected function textStudent(string $purpose, string $message, array $whatsappVariables = []): void
    {
        $student = $this->student;

        try {
            $channel = match (true) {
                app(TermiiSmsService::class)->send($student->phone, $message) => 'sms',
                app(WhatsAppService::class)->send($student->phone, config("services.twilio.whatsapp_templates.{$purpose}"), $whatsappVariables) => 'whatsapp',
                default => null,
            };

            MessageLog::create([
                'recipient_type' => 'student',
                'recipient_id' => $student->id,
                'recipient_name' => $student->name,
                'recipient_phone' => $student->phone,
                'purpose' => $purpose,
                'channel' => $channel,
                'status' => $channel ? 'sent' : 'failed',
                'message' => $message,
            ]);

            $this->pushStudent($purpose, $message);
        } catch (\Throwable $e) {
            Log::error("Failed to text student #{$student->id} for enrollment #{$this->id} ({$purpose}): {$e->getMessage()}", ['exception' => $e]);
        }
    }

    /**
     * Every event that texts a student (see textStudent() above) also
     * pushes them the same message, if they've got the app installed and
     * notifications enabled - a no-op otherwise (WebPushService itself
     * checks app access and VAPID configuration). This is on top of, not
     * instead of, the SMS/WhatsApp text - push is instant when it lands,
     * but nothing here assumes it will.
     */
    protected function pushStudent(string $purpose, string $message): void
    {
        if (! $this->student->hasAppAccess()) {
            return;
        }

        $title = match ($purpose) {
            'certificate_ready' => 'Certificate Ready',
            'training_completed' => 'Training Completed',
            'training_days_remaining' => 'Training Update',
            'programme_upgrade_window' => 'Programme Upgrade Available',
            default => 'Classic Driving School',
        };

        app(WebPushService::class)->sendToUser($this->student->user, $title, $message, route('student.dashboard'));
    }
}
