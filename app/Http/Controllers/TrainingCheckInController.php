<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Where a student's printed scan code actually leads - no login, no
 * session; opening the URL (via any QR scanner or a phone camera) is
 * what logs today's training for them. Looked up by token rather than
 * route-model-bound, so an unrecognized or tampered-with code renders a
 * clear "not valid" result instead of a bare 404, the same pattern
 * CertificateVerificationController uses.
 */
class TrainingCheckInController extends Controller
{
    public function show(Request $request, string $token): View
    {
        $student = Student::where('training_scan_token', $token)->first();

        if ($student === null) {
            return view('training-check-in.show', ['result' => 'invalid']);
        }

        $activeCourses = $student->activeCourseEnrollments();

        if ($activeCourses->isEmpty()) {
            $lockedEnrollment = $student->courses()->wherePivot('status', 'locked')->first();

            return view('training-check-in.show', [
                'result' => 'not-eligible',
                'student' => $student,
                'lockedReason' => $lockedEnrollment?->pivot->lockedReasonLabel(),
            ]);
        }

        $course = $activeCourses->count() === 1
            ? $activeCourses->first()
            : $activeCourses->firstWhere('id', (int) $request->query('course_id'));

        if ($course === null) {
            return view('training-check-in.show', [
                'result' => 'choose-course',
                'student' => $student,
                'courses' => $activeCourses,
                'token' => $token,
            ]);
        }

        $today = now()->toDateString();

        $alreadyLogged = Attendance::where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->where('date', $today)
            ->exists();

        if ($alreadyLogged) {
            return view('training-check-in.show', [
                'result' => 'already-logged',
                'student' => $student,
                'course' => $course,
            ]);
        }

        Attendance::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => $today,
            'status' => 'present',
            'notes' => 'Logged via training scan code.',
        ]);

        $course->pivot->reconcile();

        ActivityLog::record("Logged training attendance for {$student->name} ({$course->name}) via scan code");

        return view('training-check-in.show', [
            'result' => 'logged',
            'student' => $student,
            'course' => $course,
        ]);
    }
}
