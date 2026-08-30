<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnrollmentRequest;
use App\Http\Requests\StoreEnrollmentUpgradeRequest;
use App\Http\Requests\StoreReactivationRequest;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\ReactivationAuditLog;
use App\Models\Student;
use App\Services\EnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    /**
     * Show the Director-only form for enrolling an already-registered
     * student into a course - the same course/discount/initial-payment
     * fields offered at registration time, for a student who was
     * registered without one (registering a student and assigning them a
     * program are two separately gated actions; a Secretary can do the
     * former but not the latter).
     */
    public function create(Student $student): View
    {
        $enrolledCourseIds = $student->courses()->pluck('courses.id');
        $courses = Course::where('status', 'active')->whereNotIn('id', $enrolledCourseIds)->orderBy('name')->get();

        return view('enrollments.create', compact('student', 'courses'));
    }

    /**
     * Enroll the student into the course.
     */
    public function store(StoreEnrollmentRequest $request, Student $student, EnrollmentService $enrollmentService): RedirectResponse
    {
        $course = Course::findOrFail($request->validated('course_id'));

        $enrollmentService->enroll($student, $course, $request->user(), now(), $request->validated());

        ActivityLog::record("Enrolled {$student->name} in {$course->name}");

        return Redirect::route('students.show', $student)->with('status', 'student-enrolled');
    }

    /**
     * Permanently remove an enrollment - for a duplicate or mistaken entry
     * only. Refused if any payment has ever been recorded against it, or
     * if training has already been logged for it, since either means this
     * is real history rather than a mistake, and should be corrected some
     * other way (a payment reversal, or simply left as-is) instead of
     * deleted outright.
     */
    public function destroy(Enrollment $enrollment): RedirectResponse
    {
        $enrollment->load(['student', 'course']);

        if ($enrollment->amountPaid() > 0) {
            return Redirect::back()->withErrors([
                'enrollment' => 'Cannot remove this enrollment: a payment of ₦'.number_format($enrollment->amountPaid(), 2).' has already been recorded against it.',
            ]);
        }

        if (Attendance::where('student_id', $enrollment->student_id)->where('course_id', $enrollment->course_id)->exists()) {
            return Redirect::back()->withErrors([
                'enrollment' => 'Cannot remove this enrollment: training has already been logged against it.',
            ]);
        }

        $studentId = $enrollment->student_id;
        $description = "Removed {$enrollment->student->name}'s enrollment in {$enrollment->course->name} (no payments or training recorded)";

        $enrollment->delete();

        ActivityLog::record($description);

        return Redirect::route('students.show', $studentId)->with('status', 'enrollment-removed');
    }

    /**
     * Mark an enrollment as completed, automatically issuing the student's
     * certificate for it. Requires both the required training days to have
     * been attended AND the outstanding balance to be cleared - a student
     * never completes while still owing money. Once completed, the
     * enrollment is exempt from future locking.
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

        if ($enrollment->balance() > 0) {
            return Redirect::back()->withErrors([
                'enrollment' => 'Cannot mark this course complete: outstanding balance of ₦'.number_format($enrollment->balance(), 2).' must be cleared first.',
            ]);
        }

        $enrollment->markCompleted();

        ActivityLog::record("Marked {$enrollment->student->name}'s enrollment in {$enrollment->course->name} as completed");

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
            // course_id is deliberately left null here, the same way the
            // programme-upgrade flow does it (EnrollmentService::upgrade())
            // - otherwise Payment::booted()'s save hook would allocate the
            // entire bundled total (balance + reactivation fee) as
            // "training" revenue. The two allocations below split it
            // correctly instead.
            $payment = Payment::create([
                'student_id' => $enrollment->student_id,
                'course_id' => null,
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

            if ($balanceCleared > 0) {
                PaymentAllocation::create([
                    'payment_id' => $payment->id,
                    'allocation_type' => 'training',
                    'enrollment_id' => $enrollment->id,
                    'amount' => $balanceCleared,
                ]);
            }

            if ($additionalFee > 0) {
                PaymentAllocation::create([
                    'payment_id' => $payment->id,
                    'allocation_type' => 'reactivation_fee',
                    'enrollment_id' => $enrollment->id,
                    'amount' => $additionalFee,
                ]);
            }
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

        ActivityLog::record("Reactivated {$enrollment->student->name}'s enrollment in {$enrollment->course->name}");

        return Redirect::route('students.show', $enrollment->student_id)->with('status', 'enrollment-reactivated');
    }

    /**
     * Show the Director-only form for upgrading an enrollment to a longer
     * programme, per the Programme Upgrade Policy - available only within
     * the student's first five completed training days.
     */
    public function showUpgradeForm(Enrollment $enrollment): View
    {
        abort_unless($enrollment->canUpgrade(), 404);

        $enrollment->load(['student', 'course']);
        $eligibleCourses = $enrollment->eligibleUpgradeCourses();

        return view('enrollments.upgrade', compact('enrollment', 'eligibleCourses'));
    }

    /**
     * Upgrade the enrollment to the selected longer programme. The
     * student is charged only the difference between the new programme's
     * fee and what they've already been charged, and their training
     * progress carries over rather than resetting.
     */
    public function upgrade(StoreEnrollmentUpgradeRequest $request, Enrollment $enrollment, EnrollmentService $enrollmentService): RedirectResponse
    {
        if (! $enrollment->canUpgrade()) {
            return Redirect::back()->withErrors([
                'enrollment' => 'Your programme upgrade period has ended. Programme upgrades are only available within your first five completed training days.',
            ]);
        }

        $newCourse = Course::findOrFail($request->validated('course_id'));
        $fromCourseName = $enrollment->course->name;

        $enrollmentService->upgrade(
            $enrollment,
            $newCourse,
            $request->user(),
            (float) $request->validated('amount_paid', 0),
            $request->validated('payment_method'),
            now(),
        );

        ActivityLog::record("Upgraded {$enrollment->student->name}'s programme from {$fromCourseName} to {$newCourse->name}");

        return Redirect::route('students.show', $enrollment->student_id)->with('status', 'enrollment-upgraded');
    }
}
