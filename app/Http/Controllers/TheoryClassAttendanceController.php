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

class TheoryClassAttendanceController extends Controller
{
    /**
     * Record (or correct) one student's attendance for a theory class.
     * Upserted on (theory_class_id, student_id) so this same endpoint
     * covers both the first "mark present" tap from the roster and any
     * later correction (score, remarks, or a status change) to that same
     * record.
     */
    public function store(Request $request, TheoryClass $theoryClass): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => [
                'required',
                'integer',
                Rule::exists('students', 'id'),
            ],
            'status' => ['required', 'in:present,absent,late,excused'],
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $student = Student::findOrFail($data['student_id']);

        TheoryClassAttendance::updateOrCreate(
            ['theory_class_id' => $theoryClass->id, 'student_id' => $student->id],
            [
                'status' => $data['status'],
                'score' => $data['score'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'marked_by' => $request->user()->id,
            ]
        );

        ActivityLog::record("Marked {$student->name} {$data['status']} for the theory class on {$theoryClass->class_date->toFormattedDateString()}");

        return Redirect::route('theory-classes.show', $theoryClass)->with('status', 'theory-attendance-saved');
    }
}
