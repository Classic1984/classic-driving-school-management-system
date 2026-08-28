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
     * An instructor's own read-only view of today's schedule: any theory
     * class assigned to them today, plus who's present/absent among
     * students enrolled in the courses they teach. Marking attendance or
     * recording assessments is a separate, later phase - this is
     * look-but-don't-touch.
     */
    public function index(Request $request): View
    {
        /** @var Instructor $instructor */
        $instructor = $request->user()->instructor()->with('courses')->first();

        [$presentToday, $absentToday] = $this->todaysRoster($instructor);

        $todaysTheoryClass = TheoryClass::whereDate('class_date', today())
            ->where('instructor_id', $instructor->id)
            ->first();

        return view('instructor.dashboard', [
            'instructor' => $instructor,
            'presentToday' => $presentToday,
            'absentToday' => $absentToday,
            'todaysTheoryClass' => $todaysTheoryClass,
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
