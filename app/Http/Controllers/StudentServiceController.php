<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Service;
use App\Models\Student;
use App\Models\StudentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class StudentServiceController extends Controller
{
    /**
     * Charge a student for a flat catalog service (e.g. Driver's License
     * Processing, Learner's Permit) - independent of any course
     * enrollment. The price is snapshotted from the service's current
     * price, the same way a course's fee is snapshotted onto an
     * enrollment.
     */
    public function store(Request $request, Student $student): RedirectResponse
    {
        $data = $request->validate([
            'service_id' => [
                'required',
                'integer',
                Rule::exists('services', 'id')->where('is_active', true),
                Rule::unique('student_services')->where('student_id', $student->id),
            ],
        ], [
            'service_id.unique' => 'This student has already been charged for that service.',
        ]);

        $service = Service::findOrFail($data['service_id']);

        StudentService::create([
            'student_id' => $student->id,
            'service_id' => $service->id,
            'price' => $service->price,
        ]);

        ActivityLog::record("Charged {$student->name} ₦".number_format((float) $service->price, 2)." for {$service->name}");

        return Redirect::route('students.show', $student)->with('status', 'service-charged');
    }

    /**
     * Update a service charge's real-world processing status - entirely
     * independent of its payment status, per its own field on the model.
     */
    public function updateProcessingStatus(Request $request, StudentService $studentService): RedirectResponse
    {
        $data = $request->validate([
            'processing_status' => ['required', Rule::in(StudentService::PROCESSING_STATUSES)],
        ]);

        $studentService->load('service');

        $updates = ['processing_status' => $data['processing_status']];

        // Stamp the moment processing (re)starts so progress toward the
        // service's expected turnaround can be tracked; clear it on reset
        // back to not started rather than leaving a stale start date.
        if ($data['processing_status'] === 'processing' && $studentService->processing_status !== 'processing') {
            $updates['processing_started_at'] = now();
        } elseif ($data['processing_status'] === 'not_started') {
            $updates['processing_started_at'] = null;
        }

        $studentService->update($updates);

        ActivityLog::record("Updated {$studentService->service->name} processing status to \"{$studentService->processingStatusLabel()}\" for {$studentService->student->name}");

        return Redirect::route('students.show', $studentService->student_id)->with('status', 'service-status-updated');
    }

    /**
     * Permanently remove a service charge - for a duplicate or mistaken
     * entry only. Refused if any payment has ever been recorded against
     * it, or if its processing has already started, since either means
     * this is real activity rather than a mistake, and should be
     * corrected some other way (a payment reversal, or simply left
     * as-is) instead of deleted outright.
     */
    public function destroy(StudentService $studentService): RedirectResponse
    {
        $studentService->load(['student', 'service']);

        if ($studentService->amountPaid() > 0) {
            return Redirect::back()->withErrors([
                'studentService' => 'Cannot remove this charge: a payment of ₦'.number_format($studentService->amountPaid(), 2).' has already been recorded against it.',
            ]);
        }

        if ($studentService->processing_status !== 'not_started') {
            return Redirect::back()->withErrors([
                'studentService' => 'Cannot remove this charge: processing has already started for it.',
            ]);
        }

        $studentId = $studentService->student_id;
        $description = "Removed {$studentService->student->name}'s charge for {$studentService->service->name} (no payments or processing recorded)";

        $studentService->delete();

        ActivityLog::record($description);

        return Redirect::route('students.show', $studentId)->with('status', 'service-removed');
    }
}
