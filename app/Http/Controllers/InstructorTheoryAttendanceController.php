<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Student;
use App\Models\TheoryClass;
use App\Models\TheoryClassAttendance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class InstructorTheoryAttendanceController extends Controller
{
    /**
     * Mark one student's attendance for a theory class, restricted to
     * classes actually assigned to the logged-in instructor - unlike the
     * staff-facing TheoryClassAttendanceController, an instructor can't
     * mark attendance for a class taught by someone else.
     */
    public function store(Request $request, TheoryClass $theoryClass): RedirectResponse
    {
        abort_unless($theoryClass->instructor_id === $request->user()->instructor->id, 403);

        $data = $request->validate([
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')],
            'status' => ['required', 'in:present,absent,late,excused'],
        ]);

        $student = Student::findOrFail($data['student_id']);

        TheoryClassAttendance::updateOrCreate(
            ['theory_class_id' => $theoryClass->id, 'student_id' => $student->id],
            [
                'status' => $data['status'],
                'marked_by' => $request->user()->id,
            ]
        );

        ActivityLog::record("Marked {$student->name} {$data['status']} for the theory class on {$theoryClass->class_date->toFormattedDateString()} via the instructor app");

        return Redirect::route('instructor.dashboard')->with('status', 'theory-attendance-saved');
    }
}
