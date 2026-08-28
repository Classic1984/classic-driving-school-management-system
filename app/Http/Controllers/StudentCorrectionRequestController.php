<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Student;
use App\Models\StudentCorrectionRequest;
use App\Models\User;
use App\Notifications\StudentCorrectionRequestedNotification;
use App\Services\WebPushService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentCorrectionRequestController extends Controller
{
    /**
     * Show the form for requesting a correction to one of a student's
     * Director-locked fields.
     */
    public function create(Request $request, Student $student): View
    {
        $field = $request->query('field');
        $field = in_array($field, StudentCorrectionRequest::FIELDS, true) ? $field : null;

        return view('student-correction-requests.create', compact('student', 'field'));
    }

    /**
     * Store a new correction request. This never changes the student
     * record itself - only a Director can do that, separately.
     */
    public function store(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'field' => ['required', Rule::in(StudentCorrectionRequest::FIELDS)],
            'requested_value' => ['required', 'string', 'max:1000'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $correctionRequest = StudentCorrectionRequest::create([
            'student_id' => $student->id,
            'requested_by' => $request->user()->id,
            'field' => $validated['field'],
            'current_value' => $this->currentValueFor($student, $validated['field']),
            'requested_value' => $validated['requested_value'],
            'reason' => $validated['reason'] ?? null,
        ]);

        Notification::send(User::where('role', 'director')->get(), new StudentCorrectionRequestedNotification($correctionRequest));
        app(WebPushService::class)->sendToDirectors(
            'Correction Request',
            "{$request->user()->name} requested a change to {$student->name}'s {$correctionRequest->fieldLabel()}.",
            route('approvals.index')
        );

        ActivityLog::record("Requested a correction to {$student->name}'s {$correctionRequest->fieldLabel()}");

        return Redirect::route('students.show', $student)->with('status', 'correction-requested');
    }

    /**
     * Mark a correction request resolved, once the Director has made the
     * actual change (via the student edit form, or the course roster for a
     * program change).
     */
    public function resolve(StudentCorrectionRequest $correctionRequest): RedirectResponse
    {
        $correctionRequest->update([
            'status' => 'resolved',
            'resolved_by' => request()->user()->id,
            'resolved_at' => now(),
        ]);

        ActivityLog::record("Resolved a correction request for {$correctionRequest->student->name}'s {$correctionRequest->fieldLabel()}");

        return Redirect::route('approvals.index')->with('status', 'correction-request-resolved');
    }

    /**
     * Reject a correction request without changing anything.
     */
    public function reject(StudentCorrectionRequest $correctionRequest): RedirectResponse
    {
        $correctionRequest->update([
            'status' => 'rejected',
            'resolved_by' => request()->user()->id,
            'resolved_at' => now(),
        ]);

        ActivityLog::record("Rejected a correction request for {$correctionRequest->student->name}'s {$correctionRequest->fieldLabel()}");

        return Redirect::route('approvals.index')->with('status', 'correction-request-rejected');
    }

    /**
     * A snapshot of the field's current value, for context on the request.
     */
    protected function currentValueFor(Student $student, string $field): string
    {
        return match ($field) {
            'name' => $student->name,
            'date_of_birth' => optional($student->date_of_birth)->format('Y-m-d') ?? '—',
            'phone' => $student->phone,
            'program' => $student->courses->pluck('name')->implode(', ') ?: '—',
            default => '—',
        };
    }
}
