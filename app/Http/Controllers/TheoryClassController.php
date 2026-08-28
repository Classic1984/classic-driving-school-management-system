<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Instructor;
use App\Models\TheoryClass;
use App\Models\TheoryClassCancellation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
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

        // The weekly reminder job normally creates today's class
        // automatically at 8am - this only matters as a fallback for
        // when that scheduled run doesn't happen, so staff aren't stuck
        // waiting on it.
        $todaysClassExists = TheoryClass::whereDate('class_date', today())->exists();
        $todaysClassCancelled = TheoryClassCancellation::whereDate('class_date', today())->exists();

        return view('theory-classes.index', compact('theoryClasses', 'todaysClassExists', 'todaysClassCancelled'));
    }

    /**
     * Manual fallback for creating today's class roster, for when the
     * scheduled app:send-theory-class-reminder run (which normally does
     * this automatically at 8am) doesn't fire - the same failure mode
     * the scheduler heartbeat on the Activity Log page exists to catch.
     */
    public function createToday(): RedirectResponse
    {
        if (TheoryClassCancellation::whereDate('class_date', today())->exists()) {
            return Redirect::route('theory-classes.index')->with('status', 'theory-class-cancelled-today');
        }

        $theoryClass = TheoryClass::firstOrCreate(['class_date' => today()]);

        ActivityLog::record("Manually created today's theory class ({$theoryClass->class_date->toFormattedDateString()})");

        return Redirect::route('theory-classes.show', $theoryClass)->with('status', 'theory-class-created');
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
     * Update the class-level details (topic, instructor, notes), and
     * optionally upload or replace the lecture material for this class -
     * a phone's camera roll, "Files" app, or a document saved from
     * WhatsApp/email all work here through the browser's own file picker,
     * so there's nothing extra to install.
     */
    public function update(Request $request, TheoryClass $theoryClass): RedirectResponse
    {
        $data = $request->validate([
            'topic' => ['nullable', 'string', 'max:255'],
            'instructor_id' => ['nullable', 'integer', 'exists:instructors,id'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string'],
            'materials' => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png', 'max:20480'],
        ]);
        unset($data['materials']);

        if ($request->hasFile('materials')) {
            if ($theoryClass->materials_path) {
                Storage::disk('public')->delete($theoryClass->materials_path);
            }

            $file = $request->file('materials');
            $data['materials_path'] = $file->store('theory-class-materials', 'public');
            $data['materials_original_name'] = $file->getClientOriginalName();
        }

        $theoryClass->update($data);

        ActivityLog::record("Updated theory class details for {$theoryClass->class_date->toFormattedDateString()}");

        return Redirect::route('theory-classes.show', $theoryClass)->with('status', 'theory-class-updated');
    }
}
