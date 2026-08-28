<?php

namespace App\Http\Controllers;

use App\Models\AssessmentRequest;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Models\TheoryClass;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class InstructorDashboardController extends Controller
{
    /**
     * An instructor's own view of today's schedule: any theory class
     * assigned to them today (with its roster, markable right here), who's
     * present/absent among students enrolled in the practical courses they
     * teach (absentees markable present/late/excused right here too), and
     * any of their own students awaiting a final assessment recommendation.
     */
    public function index(Request $request): View
    {
        /** @var Instructor $instructor */
        $instructor = $request->user()->instructor()->with('courses')->first();

        [$presentToday, $absentToday] = $this->todaysRoster($instructor);

        $todaysTheoryClass = TheoryClass::whereDate('class_date', today())
            ->where('instructor_id', $instructor->id)
            ->first();

        $theoryRoster = collect();

        if ($todaysTheoryClass) {
            $todaysTheoryClass->load('attendances');
            $attendanceByStudentId = $todaysTheoryClass->attendances->keyBy('student_id');

            $theoryRoster = $todaysTheoryClass->expectedStudents()
                ->map(fn ($student) => [
                    'student' => $student,
                    'attendance' => $attendanceByStudentId->get($student->id),
                ])
                ->sortBy('student.name')
                ->values();
        }

        $awaitingAssessment = $this->studentsAwaitingAssessment($instructor);

        return view('instructor.dashboard', [
            'instructor' => $instructor,
            'presentToday' => $presentToday,
            'absentToday' => $absentToday,
            'todaysTheoryClass' => $todaysTheoryClass,
            'theoryRoster' => $theoryRoster,
            'awaitingAssessment' => $awaitingAssessment,
        ]);
    }

    /**
     * Enrollments in the instructor's own courses whose training is done
     * (Completed, not yet Certified), paired with any pending assessment
     * recommendation already submitted for them - so the dashboard can
     * show "awaiting Director confirmation" instead of the submit form
     * once an instructor has already sent one in.
     */
    protected function studentsAwaitingAssessment(Instructor $instructor): Collection
    {
        $courseIds = $instructor->courses->pluck('id');

        $enrollments = Enrollment::where('status', 'completed')
            ->whereIn('course_id', $courseIds)
            ->with(['student', 'course'])
            ->get()
            ->reject(fn (Enrollment $enrollment) => $enrollment->hasCertificate())
            ->values();

        $pendingByEnrollmentId = AssessmentRequest::where('status', 'pending')
            ->whereIn('enrollment_id', $enrollments->pluck('id'))
            ->get()
            ->keyBy('enrollment_id');

        return $enrollments->map(fn (Enrollment $enrollment) => [
            'enrollment' => $enrollment,
            'pendingRequest' => $pendingByEnrollmentId->get($enrollment->id),
        ])->sortBy('enrollment.student.name')->values();
    }

    /**
     * Same Present/Absent split as the main staff dashboard
     * (DashboardController::todaysAttendanceRoster), but scoped down to
     * only the courses this instructor is assigned to teach.
     */
    protected function todaysRoster(Instructor $instructor): array
    {
        $today = now();

        if ($today->isSunday()) {
            return [collect(), collect()];
        }

        $courseIds = $instructor->courses->pluck('id');

        $presentToday = Attendance::whereDate('date', today())
            ->whereIn('status', ['present', 'late'])
            ->whereIn('course_id', $courseIds)
            ->with(['student', 'course'])
            ->latest('created_at')
            ->get();

        $loggedStudentIds = Attendance::whereDate('date', today())
            ->whereIn('course_id', $courseIds)
            ->pluck('student_id');

        $scheduleToday = $today->isSaturday() ? 'weekend' : 'weekday';

        $absentToday = Enrollment::where('status', 'active')
            ->whereIn('course_id', $courseIds)
            ->whereNotIn('student_id', $loggedStudentIds)
            ->whereHas('course', fn ($query) => $query->where('schedule', $scheduleToday))
            ->with(['student', 'course'])
            ->get()
            ->unique('student_id')
            ->values();

        return [$presentToday, $absentToday];
    }
}
