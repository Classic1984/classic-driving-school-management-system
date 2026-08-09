<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $stats = [
            'students' => Student::count(),
            'payments' => Payment::where('status', 'paid')->whereDate('payment_date', today())->sum('amount'),
            'instructors' => Instructor::count(),
            'certificates' => Certificate::count(),
        ];

        $newStudentTotals = [
            'today' => Student::whereDate('enrollment_date', today())->count(),
            'week' => Student::whereBetween('enrollment_date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'month' => Student::whereYear('enrollment_date', now()->year)
                ->whereMonth('enrollment_date', now()->month)
                ->count(),
            'year' => Student::whereYear('enrollment_date', now()->year)->count(),
        ];

        $paymentTotals = null;

        if ($request->user()->isDirector()) {
            $paymentTotals = [
                'week' => Payment::where('status', 'paid')
                    ->whereBetween('payment_date', [now()->startOfWeek(), now()->endOfWeek()])
                    ->sum('amount'),
                'month' => Payment::where('status', 'paid')
                    ->whereYear('payment_date', now()->year)
                    ->whereMonth('payment_date', now()->month)
                    ->sum('amount'),
                'all_time' => Payment::where('status', 'paid')->sum('amount'),
            ];
        }

        $outstandingPayments = Enrollment::where('status', '!=', 'completed')
            ->with(['student', 'course'])
            ->get()
            ->filter(fn (Enrollment $enrollment) => $enrollment->balance() > 0)
            ->sortBy(fn (Enrollment $enrollment) => $enrollment->due_date?->timestamp ?? PHP_INT_MAX)
            ->take(10)
            ->values();

        $trainingProgress = Enrollment::with(['student', 'course'])
            ->latest('enrolled_at')
            ->take(15)
            ->get();

        $lockedEnrollments = Enrollment::where('status', 'locked')
            ->with(['student', 'course'])
            ->latest('updated_at')
            ->take(15)
            ->get();

        $trainingStats = [
            'today' => $this->distinctStudentsTrained(today(), today()),
            'week' => $this->distinctStudentsTrained(now()->startOfWeek(), now()->endOfWeek()),
            'month' => $this->distinctStudentsTrained(now()->startOfMonth(), now()->endOfMonth()),
            'year' => $this->distinctStudentsTrained(now()->startOfYear(), now()->endOfYear()),
        ];

        return view('dashboard', compact('stats', 'newStudentTotals', 'paymentTotals', 'outstandingPayments', 'trainingProgress', 'trainingStats', 'lockedEnrollments'));
    }

    /**
     * Count of distinct students with at least one saved "present" training
     * login in the given date range - actual training activity, not
     * expected attendance.
     */
    protected function distinctStudentsTrained($from, $to): int
    {
        return Attendance::where('status', 'present')
            ->whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->distinct('student_id')
            ->count('student_id');
    }
}
