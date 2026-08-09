<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $courses = Course::with(['instructors', 'students'])->latest()->paginate(10);

        return view('courses.index', compact('courses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $instructors = Instructor::orderBy('name')->get();
        $students = Student::orderBy('name')->get();

        return view('courses.create', compact('instructors', 'students'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $course = Course::create($request->safe()->except(['instructors', 'students']));

        $course->instructors()->sync($request->validated('instructors', []));
        $this->syncStudentEnrollments($course, $request->validated('students', []));

        return Redirect::route('courses.index')->with('status', 'course-created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course): View
    {
        $course->load(['instructors', 'students']);

        return view('courses.show', compact('course'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Course $course): View
    {
        $course->load(['instructors', 'students']);
        $instructors = Instructor::orderBy('name')->get();
        $students = Student::orderBy('name')->get();

        return view('courses.edit', compact('course', 'instructors', 'students'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $course->update($request->safe()->except(['instructors', 'students']));

        $course->instructors()->sync($request->validated('instructors', []));
        $this->syncStudentEnrollments($course, $request->validated('students', []));

        return Redirect::route('courses.index')->with('status', 'course-updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course): RedirectResponse
    {
        $course->delete();

        return Redirect::route('courses.index')->with('status', 'course-deleted');
    }

    /**
     * Attach newly selected students as fresh enrollments (with a due date
     * computed from the course's grace period) and detach deselected ones,
     * without disturbing the enrollment data of students who remain selected.
     *
     * @param  array<int, int>  $selectedStudentIds
     */
    protected function syncStudentEnrollments(Course $course, array $selectedStudentIds): void
    {
        $currentStudentIds = $course->students()->pluck('students.id')->all();

        $toDetach = array_diff($currentStudentIds, $selectedStudentIds);
        $toAttach = array_diff($selectedStudentIds, $currentStudentIds);

        if (! empty($toDetach)) {
            $course->students()->detach($toDetach);
        }

        foreach ($toAttach as $studentId) {
            $course->students()->attach($studentId, [
                'enrolled_at' => now()->toDateString(),
                'due_date' => now()->addDays($course->gracePeriodDays())->toDateString(),
                'status' => 'active',
                'fee' => $course->fee,
            ]);

            // Reopens a previously "completed" student now that they have a
            // new course in progress.
            Student::find($studentId)?->refreshStatus();
        }
    }
}
