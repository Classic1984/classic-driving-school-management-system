<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Instructor;
use App\Models\TheoryClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class TheoryClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $theoryClasses = TheoryClass::with(['instructor', 'attendances'])
            ->orderByDesc('class_date')
            ->paginate(15);

        return view('theory-classes.index', compact('theoryClasses'));
    }

    /**
     * Display the specified resource.
     */
    public function show(TheoryClass $theoryClass): View
    {
        $theoryClass->load(['instructor', 'attendances.student']);

        $expectedStudents = $theoryClass->expectedStudents();
        $attendanceByStudentId = $theoryClass->attendances->keyBy('student_id');

        $roster = $expectedStudents->map(fn ($student) => [
            'student' => $student,
            'attendance' => $attendanceByStudentId->get($student->id),
        ])->sortBy('student.name')->values();

        $instructors = Instructor::orderBy('name')->get();

        return view('theory-classes.show', compact('theoryClass', 'roster', 'instructors'));
    }

    /**
     * Update the class-level details (topic, instructor, notes).
     */
    public function update(Request $request, TheoryClass $theoryClass): RedirectResponse
    {
        $data = $request->validate([
            'topic' => ['nullable', 'string', 'max:255'],
            'instructor_id' => ['nullable', 'integer', 'exists:instructors,id'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string'],
        ]);

        $theoryClass->update($data);

        ActivityLog::record("Updated theory class details for {$theoryClass->class_date->toFormattedDateString()}");

        return Redirect::route('theory-classes.show', $theoryClass)->with('status', 'theory-class-updated');
    }
}
