<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\ActivityLog;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));

        $courses = Course::with(['instructors', 'students'])
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $courseStats = [
            'total' => Course::count(),
            'total_students' => Student::count(),
            'instructors' => Instructor::count(),
            'active' => Course::where('status', 'active')->count(),
        ];

        return view('courses.index', compact('courses', 'courseStats'));
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

        // Enrolling/moving students into a course - i.e. assigning or
        // changing a student's training program - is Director-only; a
        // non-Director's "students" selection is ignored entirely, not
        // just hidden in the form.
        if ($request->user()->isDirector()) {
            $this->syncStudentEnrollments($course, $request->validated('students', []));
        }

        ActivityLog::record("Created course {$course->name}");

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

        // See store(): enrollment/roster changes are Director-only.
        if ($request->user()->isDirector()) {
            $this->syncStudentEnrollments($course, $request->validated('students', []));
        }

        // A duration_weeks change alters totalTrainingDays() for every
        // enrollment in this course - reconcile them all so a "Completed"
        // enrollment that no longer meets the (now larger) requirement is
        // correctly reverted, rather than silently going stale.
        Enrollment::where('course_id', $course->id)->get()->each(fn (Enrollment $enrollment) => $enrollment->reconcile());

        ActivityLog::record("Updated course {$course->name}");

        return Redirect::route('courses.index')->with('status', 'course-updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course): RedirectResponse
    {
        $name = $course->name;
        $course->delete();

        ActivityLog::record("Deleted course {$name}");

        return Redirect::route('courses.index')->with('status', 'course-deleted');
    }

    /**
     * Attach newly selected students as fresh enrollments (with a due date
     * computed from the course's grace period) and detach deselected ones,
     * without disturbing the enrollment data of students who remain selected.
     * Director-only - see the isDirector() checks around both call sites.
     *
     * @param  array<int, int>  $selectedStudentIds
     */
    protected function syncStudentEnrollments(Course $course, array $selectedStudentIds): void
    {
        $currentStudentIds = $course->students()->pluck('students.id')->all();

        $toDetach = array_diff($currentStudentIds, $selectedStudentIds);
        $toAttach = array_diff($selectedStudentIds, $currentStudentIds);

        if (! empty($toDetach)) {
            $detachedNames = Student::whereIn('id', $toDetach)->pluck('name');
            $course->students()->detach($toDetach);

            foreach ($detachedNames as $name) {
                ActivityLog::record("Removed {$name} from {$course->name}");
            }
        }

        foreach ($toAttach as $studentId) {
            $course->students()->attach($studentId, [
                'enrolled_at' => now()->toDateString(),
                'due_date' => now()->addDays($course->gracePeriodDays())->toDateString(),
                'status' => 'active',
                'fee' => $course->fee,
                'original_fee' => $course->fee,
                // Certificate fees are part of the course outline, not an
                // opt-in add-on - mirrors EnrollmentService::enroll(),
                // which every other enrollment path goes through.
                'online_certificate_fee' => $course->online_certificate_fee,
                'student_certificate_fee' => $course->student_certificate_fee,
            ]);

            if ($student = Student::find($studentId)) {
                ActivityLog::record("Enrolled {$student->name} in {$course->name}");

                // Reopens a previously "completed" student now that they
                // have a new course in progress.
                $student->refreshStatus();
            }
        }
    }
}
