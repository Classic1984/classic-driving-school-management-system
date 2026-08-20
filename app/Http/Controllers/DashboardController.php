<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentService;
use App\Models\Vehicle;
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
            'new_leads' => Lead::where('status', 'new')->count(),
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

        $outstandingEnrollments = Enrollment::with(['student', 'course'])
            ->get()
            ->filter(fn (Enrollment $enrollment) => $enrollment->balance() > 0);

        $outstandingPayments = $outstandingEnrollments
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

        // Only services with a tracked turnaround (processing_days set)
        // produce a meaningful progress figure - see
        // StudentService::processingProgressPercent().
        $serviceProcessing = StudentService::where('processing_status', 'processing')
            ->whereHas('service', fn ($query) => $query->whereNotNull('processing_days'))
            ->with(['student', 'service'])
            ->get()
            ->sortBy(fn (StudentService $studentService) => $studentService->expectedReadyAt())
            ->values();

        // Programme Upgrade Window alerts: enrollments still within (or just
        // past) their five-day upgrade window that actually have a longer
        // programme to upgrade into - a stale enrollment that closed weeks
        // ago isn't news to staff, so only day 6 onward is shown as
        // "Closed" here, one day past the window, rather than forever.
        $upgradeAlerts = Enrollment::where('status', '!=', 'completed')
            ->with(['student', 'course'])
            ->get()
            ->filter(fn (Enrollment $enrollment) => $enrollment->attendedDays() <= Enrollment::UPGRADE_WINDOW_DAYS + 1
                && $enrollment->eligibleUpgradeCourses()->isNotEmpty())
            ->sortBy(fn (Enrollment $enrollment) => $enrollment->attendedDays())
            ->values();

        $completedEnrollments = Enrollment::where('status', 'completed')->get();

        // Top-line KPI cards: the numbers a Director should see at a glance
        // without reading any of the detail widgets below.
        $kpis = [
            'active_students' => Student::where('status', 'active')->count(),
            'training_today' => $trainingStats['today'],
            'pending_payments' => $outstandingEnrollments->sum(fn (Enrollment $enrollment) => $enrollment->balance()),
            'completed_training' => $completedEnrollments->count(),
            'active_vehicles' => Vehicle::where('status', 'active')->count(),
            'certificates_due' => $completedEnrollments->filter(fn (Enrollment $enrollment) => ! $enrollment->hasCertificate())->count(),
        ];

        $todaysAttendance = Attendance::where('status', 'present')->whereDate('date', today());

        // Today's Operations: a same-day snapshot, distinct from the KPI
        // cards above (which are cumulative/current totals) - what
        // actually happened or needs attention today specifically.
        $todaysOperations = [
            'students_trained' => $trainingStats['today'],
            'training_sessions' => (clone $todaysAttendance)->count(),
            'instructors_active' => (clone $todaysAttendance)->distinct('instructor_id')->count('instructor_id'),
            'vehicles_in_use' => (clone $todaysAttendance)->whereNotNull('vehicle_id')->distinct('vehicle_id')->count('vehicle_id'),
            'payments_received_today' => $stats['payments'],
            'payments_pending_count' => $outstandingEnrollments->count(),
            'approaching_completion' => Enrollment::where('status', 'active')
                ->get()
                ->filter(fn (Enrollment $enrollment) => $enrollment->remainingTrainingDays() > 0
                    && $enrollment->remainingTrainingDays() <= Enrollment::TRAINING_DAYS_REMAINING_THRESHOLD)
                ->count(),
            'locked_students' => Enrollment::where('status', 'locked')->count(),
        ];

        return view('dashboard', compact('stats', 'newStudentTotals', 'paymentTotals', 'outstandingPayments', 'trainingProgress', 'trainingStats', 'lockedEnrollments', 'serviceProcessing', 'upgradeAlerts', 'kpis', 'todaysOperations'));
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
