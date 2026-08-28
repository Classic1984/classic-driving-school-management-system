<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\AssessmentRequest;
use App\Models\Enrollment;
use App\Models\User;
use App\Notifications\AssessmentRequestedNotification;
use App\Services\WebPushService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;

class InstructorAssessmentRequestController extends Controller
{
    /**
     * An instructor's recommended final practical assessment result for
     * one of their own students, pending Director confirmation. Unlike
     * the staff-facing AssessmentController, this never touches the real
     * Assessment record (and so never issues a certificate) by itself -
     * see AssessmentRequestController::approve().
     */
    public function store(Request $request, Enrollment $enrollment): RedirectResponse
    {
        $instructor = $request->user()->instructor;

        abort_unless($instructor->courses->contains('id', $enrollment->course_id), 403);
        abort_unless($enrollment->status === 'completed', 403);

        $data = $request->validate([
            'result' => ['required', 'in:pass,fail'],
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $assessmentRequest = AssessmentRequest::updateOrCreate(
            ['enrollment_id' => $enrollment->id, 'status' => 'pending'],
            [
                'student_id' => $enrollment->student_id,
                'course_id' => $enrollment->course_id,
                'requested_by' => $request->user()->id,
                'result' => $data['result'],
                'score' => $data['score'] ?? null,
                'remarks' => $data['remarks'] ?? null,
            ]
        );
        $assessmentRequest->load(['student', 'course', 'requestedBy']);

        Notification::send(User::where('role', 'director')->get(), new AssessmentRequestedNotification($assessmentRequest));
        app(WebPushService::class)->sendToDirectors(
            'Assessment Recommendation',
            "{$assessmentRequest->requestedBy->name} recommended a {$data['result']} for {$assessmentRequest->student->name} ({$assessmentRequest->course->name}).",
            route('approvals.index')
        );

        ActivityLog::record("Submitted a {$data['result']} final assessment recommendation for {$assessmentRequest->student->name} ({$assessmentRequest->course->name}) via the instructor app");

        return Redirect::route('instructor.dashboard')->with('status', 'assessment-request-submitted');
    }
}
