<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $attendances = Attendance::with(['student', 'course', 'instructor', 'vehicle'])->latest('date')->paginate(10);

        return view('attendances.index', compact('attendances'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('attendances.create', $this->formOptions());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttendanceRequest $request): RedirectResponse
    {
        $attendance = Attendance::create([
            ...$request->validated(),
            'logged_by' => $request->user()->id,
        ]);
        $attendance->load(['student', 'course']);

        // Recompute immediately in case this is the student's last training
        // day, rather than waiting for the next payment or the daily
        // scheduled refresh.
        $this->recalculateEnrollment($attendance->student_id, $attendance->course_id);

        ActivityLog::record("Logged training attendance for {$attendance->student->name} ({$attendance->course->name})");

        if ($request->boolean('redirect_to_training_record')) {
            return Redirect::route('students.training-record', $attendance->student_id)->with('status', 'training-logged');
        }

        if ($request->boolean('redirect_to_student')) {
            return Redirect::route('students.show', $attendance->student_id)->with('status', 'training-logged');
        }

        return Redirect::route('attendances.index')->with('status', 'attendance-created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Attendance $attendance): View
    {
        $attendance->load(['student', 'course', 'instructor', 'vehicle']);

        return view('attendances.show', compact('attendance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attendance $attendance): View
    {
        return view('attendances.edit', [...$this->formOptions(), 'attendance' => $attendance]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAttendanceRequest $request, Attendance $attendance): RedirectResponse
    {
        $previousStudentId = $attendance->student_id;
        $previousCourseId = $attendance->course_id;

        $attendance->update($request->validated());
        $attendance->load(['student', 'course']);

        // A correction can change the training day count for either the
        // original enrollment (student/course reassigned) or the new one,
        // and can flip a completed enrollment back to active/locked.
        $this->recalculateEnrollment($previousStudentId, $previousCourseId);
        $this->recalculateEnrollment($attendance->student_id, $attendance->course_id);

        ActivityLog::record("Updated a training attendance record for {$attendance->student->name} ({$attendance->course->name})");

        return Redirect::route('attendances.index')->with('status', 'attendance-updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance): RedirectResponse
    {
        $attendance->load(['student', 'course']);
        $studentId = $attendance->student_id;
        $courseId = $attendance->course_id;
        $description = "Deleted a training attendance record for {$attendance->student->name} ({$attendance->course->name})";

        $attendance->delete();

        $this->recalculateEnrollment($studentId, $courseId);

        ActivityLog::record($description);

        return Redirect::route('attendances.index')->with('status', 'attendance-deleted');
    }

    /**
     * Recompute the enrollment's status now that its attendance record has
     * changed, including reverting a "completed" status if the correction
     * dropped attendance back below the required training days.
     */
    protected function recalculateEnrollment(int $studentId, int $courseId): void
    {
        Enrollment::where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->first()
            ?->reconcile();
    }

    /**
     * Get the option lists shared by the create and edit forms.
     *
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        return [
            'students' => Student::orderBy('name')->get(),
            'courses' => Course::orderBy('name')->get(),
            'instructors' => Instructor::orderBy('name')->get(),
            'vehicles' => Vehicle::where('status', 'active')->orderBy('name')->get(),
        ];
    }
}
