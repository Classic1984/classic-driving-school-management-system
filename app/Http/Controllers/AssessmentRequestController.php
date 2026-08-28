<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Assessment;
use App\Models\AssessmentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class AssessmentRequestController extends Controller
{
    /**
     * Approve an instructor's assessment recommendation: record it as the
     * enrollment's real Assessment (the same upsert-by-(student, course)
     * AssessmentController::store() does), which lets
     * Enrollment::maybeIssueCertificate() issue the certificate if
     * training is already complete.
     */
    public function approve(AssessmentRequest $assessmentRequest): RedirectResponse
    {
        $assessmentRequest->load(['student', 'course', 'enrollment']);

        Assessment::updateOrCreate(
            ['student_id' => $assessmentRequest->student_id, 'course_id' => $assessmentRequest->course_id],
            [
                'result' => $assessmentRequest->result,
                'score' => $assessmentRequest->score,
                'remarks' => $assessmentRequest->remarks,
                'assessed_by' => request()->user()->id,
                'assessed_at' => now(),
            ]
        );

        $assessmentRequest->update([
            'status' => 'approved',
            'resolved_by' => request()->user()->id,
            'resolved_at' => now(),
        ]);

        ActivityLog::record("Confirmed a {$assessmentRequest->result} final assessment for {$assessmentRequest->student->name} ({$assessmentRequest->course->name})");

        $assessmentRequest->enrollment?->maybeIssueCertificate();

        return Redirect::route('approvals.index')->with('status', 'assessment-request-approved');
    }

    /**
     * Reject an instructor's assessment recommendation. No Assessment is
     * recorded and no certificate is issued.
     */
    public function reject(AssessmentRequest $assessmentRequest): RedirectResponse
    {
        $assessmentRequest->load(['student', 'course']);

        $assessmentRequest->update([
            'status' => 'rejected',
            'resolved_by' => request()->user()->id,
            'resolved_at' => now(),
        ]);

        ActivityLog::record("Rejected an assessment recommendation for {$assessmentRequest->student->name} ({$assessmentRequest->course->name})");

        return Redirect::route('approvals.index')->with('status', 'assessment-request-rejected');
    }
}
