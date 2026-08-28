<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Assessment;
use App\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class AssessmentController extends Controller
{
    /**
     * Record (or re-record) an enrollment's final practical assessment.
     * Upserted on (student_id, course_id) so a retake after an earlier
     * fail simply replaces the result rather than piling up history rows -
     * the same pattern used for theory-class attendance.
     *
     * This is the gate Enrollment::maybeIssueCertificate() checks: a
     * passing result recorded here, once training itself is already
     * "Completed", issues the certificate immediately.
     */
    public function store(Request $request, Enrollment $enrollment): RedirectResponse
    {
        $data = $request->validate([
            'result' => ['required', 'in:pass,fail'],
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        Assessment::updateOrCreate(
            ['student_id' => $enrollment->student_id, 'course_id' => $enrollment->course_id],
            [
                'result' => $data['result'],
                'score' => $data['score'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'assessed_by' => $request->user()->id,
                'assessed_at' => now(),
            ]
        );

        ActivityLog::record("Recorded a {$data['result']} final assessment for {$enrollment->student->name} ({$enrollment->course->name})");

        $enrollment->maybeIssueCertificate();

        return Redirect::route('students.show', $enrollment->student_id)->with('status', 'assessment-saved');
    }
}
