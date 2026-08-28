<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    /**
     * A student's own read-only view of their training: per-course
     * progress (practical days attended, balance owed, and status up to
     * Certified), theory-class attendance summary, and a link to verify
     * any certificate they've earned. Nothing here is editable - it
     * mirrors what staff already see on the student's own profile, just
     * scoped to the student themselves.
     */
    public function index(Request $request): View
    {
        /** @var Student $student */
        $student = $request->user()->student()->with('courses')->first();

        $enrollments = $student->courses->map(function (Course $course) use ($student) {
            /** @var Enrollment $enrollment */
            $enrollment = $course->pivot;

            $certificate = $enrollment->hasCertificate()
                ? Certificate::where('student_id', $student->id)->where('course_id', $course->id)->first()
                : null;

            return [
                'course' => $course,
                'enrollment' => $enrollment,
                'attendedDays' => $enrollment->attendedDays(),
                'totalDays' => $course->totalTrainingDays(),
                'balance' => $enrollment->balance(),
                'statusLabel' => $enrollment->statusLabel(),
                'certificate' => $certificate,
            ];
        })->values();

        return view('student.dashboard', [
            'student' => $student,
            'enrollments' => $enrollments,
            'theoryProgress' => $student->theoryProgress(),
        ]);
    }
}
