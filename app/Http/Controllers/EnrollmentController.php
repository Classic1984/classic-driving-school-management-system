<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReactivationRequest;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\ReactivationAuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    /**
     * Mark an enrollment as completed, automatically issuing the student's
     * certificate for it. Requires the required training days to have been
     * attended; an outstanding balance does not block this, since training
     * completion and payment status are tracked independently. Once
     * completed, the enrollment is exempt from future locking.
     */
    public function complete(Enrollment $enrollment): RedirectResponse
    {
        if ($enrollment->status === 'completed') {
            return Redirect::back()->with('status', 'enrollment-already-completed');
        }

        if (! $enrollment->hasCompletedTraining()) {
            return Redirect::back()->withErrors([
                'enrollment' => 'Cannot mark this course complete: the required training days ('.$enrollment->attendedDays().' of '.$enrollment->course->totalTrainingDays().') have not been attended yet.',
            ]);
        }

        $enrollment->markCompleted();

        return Redirect::back()->with('status', 'enrollment-completed');
    }

    /**
     * Show the Director-only form for reactivating an enrollment that
     * locked because its training period expired.
     */
    public function showReactivateForm(Enrollment $enrollment): View
    {
        abort_unless($enrollment->isLockedForExpiredTrainingPeriod(), 404);

        $enrollment->load(['student', 'course']);

        return view('enrollments.reactivate', compact('enrollment'));
    }

    /**
     * Reactivate an enrollment locked for an expired training period. The
     * Director collects the enrollment's outstanding balance plus a
     * separately agreed reactivation fee as a single payment, then the
     * training period resets from today so training resumes from wherever
     * the student left off (attendance history is untouched) and will lock
     * again if the two-month grace period lapses a second time.
     */
    public function reactivate(StoreReactivationRequest $request, Enrollment $enrollment): RedirectResponse
    {
        if (! $enrollment->isLockedForExpiredTrainingPeriod()) {
            return Redirect::back()->withErrors([
                'enrollment' => 'This enrollment is not locked for an expired training period.',
            ]);
        }

        $balanceCleared = $enrollment->balance();
        $additionalFee = (float) $request->validated('additional_fee');
        $totalAmount = $balanceCleared + $additionalFee;

        if ($totalAmount > 0) {
            Payment::create([
                'student_id' => $enrollment->student_id,
                'course_id' => $enrollment->course_id,
                'amount' => $totalAmount,
                'payment_date' => now()->toDateString(),
                'payment_method' => $request->validated('payment_method'),
                'status' => 'paid',
                'reference_number' => $request->validated('reference_number'),
                'notes' => trim(sprintf(
                    'Reactivation payment (outstanding balance ₦%s + agreed fee ₦%s).%s',
                    number_format($balanceCleared, 2),
                    number_format($additionalFee, 2),
                    $request->filled('notes') ? ' '.$request->validated('notes') : ''
                )),
            ]);
        }

        ReactivationAuditLog::create([
            'student_id' => $enrollment->student_id,
            'course_id' => $enrollment->course_id,
            'reactivated_by' => $request->user()->id,
            'balance_cleared' => $balanceCleared,
            'additional_fee' => $additionalFee,
            'total_amount' => $totalAmount,
        ]);

        $enrollment->forceFill([
            'status' => 'active',
            'locked_reason' => null,
            'enrolled_at' => now()->toDateString(),
            'reactivated_at' => now()->toDateString(),
            'reactivation_fee' => $additionalFee,
            'reactivated_by' => $request->user()->id,
        ])->save();

        return Redirect::route('students.show', $enrollment->student_id)->with('status', 'enrollment-reactivated');
    }
}
