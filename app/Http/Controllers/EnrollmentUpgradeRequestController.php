<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentUpgradeRequest;
use App\Services\EnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class EnrollmentUpgradeRequestController extends Controller
{
    /**
     * Approve an upgrade request: execute it exactly as a Director's own
     * direct upgrade would (see EnrollmentService::upgrade()) - the
     * student is charged only the requested fee difference, and their
     * training progress carries over rather than resetting.
     */
    public function approve(EnrollmentUpgradeRequest $enrollmentUpgradeRequest, EnrollmentService $enrollmentService): RedirectResponse
    {
        $enrollmentUpgradeRequest->load(['student', 'fromCourse', 'toCourse']);
        $enrollment = Enrollment::findOrFail($enrollmentUpgradeRequest->enrollment_id);
        $newCourse = Course::findOrFail($enrollmentUpgradeRequest->to_course_id);

        // The student may have been separately enrolled into the target
        // course (or the enrollment otherwise changed) since this was
        // requested - course_student's unique (course_id, student_id)
        // constraint would otherwise fail deep inside upgrade().
        if ($enrollment->course_id !== $newCourse->id
            && $enrollment->student->courses()->where('courses.id', $newCourse->id)->exists()) {
            return Redirect::back()->withErrors([
                'enrollmentUpgradeRequest' => 'This student already has a separate enrollment in that programme - this request can no longer be approved as-is.',
            ]);
        }

        $enrollmentService->upgrade(
            $enrollment,
            $newCourse,
            request()->user(),
            (float) ($enrollmentUpgradeRequest->amount_paid ?? 0),
            $enrollmentUpgradeRequest->payment_method,
            now(),
        );

        $enrollmentUpgradeRequest->update([
            'status' => 'approved',
            'resolved_by' => request()->user()->id,
            'resolved_at' => now(),
        ]);

        ActivityLog::record("Approved an upgrade request for {$enrollmentUpgradeRequest->student->name} from {$enrollmentUpgradeRequest->fromCourse->name} to {$newCourse->name}");

        return Redirect::route('approvals.index')->with('status', 'enrollment-upgrade-request-approved');
    }

    /**
     * Reject an upgrade request. The enrollment stays exactly as it was -
     * nothing changes on it, and no payment is recorded.
     */
    public function reject(EnrollmentUpgradeRequest $enrollmentUpgradeRequest): RedirectResponse
    {
        $enrollmentUpgradeRequest->load(['student', 'fromCourse', 'toCourse']);

        $enrollmentUpgradeRequest->update([
            'status' => 'rejected',
            'resolved_by' => request()->user()->id,
            'resolved_at' => now(),
        ]);

        ActivityLog::record("Rejected an upgrade request for {$enrollmentUpgradeRequest->student->name} from {$enrollmentUpgradeRequest->fromCourse->name} to {$enrollmentUpgradeRequest->toCourse->name}");

        return Redirect::route('approvals.index')->with('status', 'enrollment-upgrade-request-rejected');
    }
}
