<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Models\TheoryClass;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstructorDashboardController extends Controller
{
    /**
     * An instructor's own view of today's schedule: any theory class
     * assigned to them today (with its roster, markable right here), plus
     * who's present/absent among students enrolled in the practical
     * courses they teach (absentees markable present/late/excused right
     * here too). Recording assessments is a separate, later phase.
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

        return view('instructor.dashboard', [
            'instructor' => $instructor,
            'presentToday' => $presentToday,
            'absentToday' => $absentToday,
            'todaysTheoryClass' => $todaysTheoryClass,
            'theoryRoster' => $theoryRoster,
        ]);
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
