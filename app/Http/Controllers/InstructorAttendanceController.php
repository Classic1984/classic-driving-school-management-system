<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInstructorAttendanceRequest;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class InstructorAttendanceController extends Controller
{
    /**
     * Log today's practical training attendance for one student, from the
     * instructor's own scoped dashboard. Course ownership is enforced in
     * StoreInstructorAttendanceRequest; instructor_id, type, and date are
     * always set server-side, never taken from the submitted form.
     */
    public function store(StoreInstructorAttendanceRequest $request): RedirectResponse
    {
        $instructor = $request->user()->instructor;

        $attendance = Attendance::create([
            ...$request->validated(),
            'instructor_id' => $instructor->id,
            'type' => 'practical',
            'date' => today(),
            'logged_by' => $request->user()->id,
        ]);
        $attendance->load(['student', 'course']);

        Enrollment::where('student_id', $attendance->student_id)
            ->where('course_id', $attendance->course_id)
            ->first()
            ?->reconcile();

        ActivityLog::record("Logged training attendance for {$attendance->student->name} ({$attendance->course->name}) via the instructor app");

        return Redirect::route('instructor.dashboard')->with('status', 'attendance-logged');
    }
}
