<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\DiscountRequest;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentCorrectionRequest;
use App\Models\StudentService;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;
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

        // Once an enrollment actually goes overdue, the system locks it on
        // its own (see Enrollment::applyLockingRules()) - so "overdue" was
        // never really a third state distinct from "locked", just the same
        // enrollment briefly caught before the lock refresh runs. Rather
        // than surface that transient overlap as its own box (and risk the
        // same student showing up in both an "Outstanding Payments" widget
        // and the "Locked Students" widget at once), this is everyone who
        // owes money and isn't locked - a completed enrollment can still
        // owe a balance (training completion doesn't clear it), so only
        // "locked" is excluded here, not "completed".
        $upcomingPayments = $outstandingEnrollments
            ->filter(fn (Enrollment $enrollment) => $enrollment->status !== 'locked')
            ->sortBy(fn (Enrollment $enrollment) => $enrollment->due_date?->timestamp ?? PHP_INT_MAX)
            ->values();

        $trainingProgress = Enrollment::with(['student', 'course'])
            ->latest('enrolled_at')
            ->take(8)
            ->get();

        // A raw absence log grows forever and the same student can be
        // marked absent more than once, so this is narrowed to the last 7
        // days and collapsed to each student's single most recent absence
        // in that window - a "who's absent right now" list, not a
        // full history.
        $absentStudents = Attendance::where('status', 'absent')
            ->where('date', '>=', now()->subDays(7))
            ->with(['student', 'course'])
            ->latest('date')
            ->get()
            ->unique('student_id')
            ->values();

        $lockedEnrollments = Enrollment::where('status', 'locked')
            ->with(['student', 'course'])
            ->latest('updated_at')
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

        // Learner's Permit and Online Certificate both have no tracked
        // turnaround (they're usually issued same-day), so neither ever
        // appears in the Service Processing widget above - this is their
        // own list of students still waiting, oldest charge first, so a
        // request that's lingered past the usual same-day turnaround
        // stands out.
        $learnersPermitRequests = $this->pendingRequestsFor("Learner's Permit");
        $onlineCertificateRequests = $this->pendingRequestsFor('Online Certificate');

        // Driver's License Processing does have a tracked turnaround, so
        // once it's marked "processing" it already shows above with a
        // progress bar - this only covers the gap before that: charged,
        // but processing hasn't been started yet.
        $driversLicenseRequests = StudentService::whereHas('service', fn ($query) => $query->where('name', "Driver's License Processing"))
            ->where('processing_status', 'not_started')
            ->with(['student', 'service'])
            ->oldest('created_at')
            ->get();

        // Programme Upgrade Window: every active enrollment that actually has
        // a longer programme to upgrade into, split into the two states
        // staff care about - split here rather than in the view so each
        // half can be counted and listed independently behind its own
        // dashboard widget.
        $upgradeCandidates = Enrollment::where('status', '!=', 'completed')
            ->with(['student', 'course'])
            ->get()
            ->filter(fn (Enrollment $enrollment) => $enrollment->eligibleUpgradeCourses()->isNotEmpty());

        $upgradeEligible = $upgradeCandidates
            ->filter(fn (Enrollment $enrollment) => $enrollment->isWithinUpgradeWindow())
            ->sortBy(fn (Enrollment $enrollment) => $enrollment->attendedDays())
            ->values();

        $upgradeClosed = $upgradeCandidates
            ->filter(fn (Enrollment $enrollment) => ! $enrollment->isWithinUpgradeWindow())
            ->sortByDesc(fn (Enrollment $enrollment) => $enrollment->attendedDays())
            ->values();

        $completedEnrollments = Enrollment::where('status', 'completed')->get();

        // At-Risk Students: active enrollments that still owe money and are
        // also showing early signs of dropping out (no training login well
        // past the automatic check-in text) or defaulting (balance due
        // soon, before it goes overdue and locks on its own) - a proactive
        // watchlist for staff to follow up on, rather than only finding out
        // after the fact once an enrollment is already locked. A fully
        // paid student who simply hasn't trained in a while is never
        // flagged here - see Enrollment::isAttendanceRisk(). "High" risk
        // (both signals at once) is shown before "medium" (either signal
        // alone).
        $atRiskEnrollments = Enrollment::where('status', 'active')
            ->with(['student', 'course'])
            ->get()
            ->filter(fn (Enrollment $enrollment) => $enrollment->isAtRisk())
            ->sortByDesc(fn (Enrollment $enrollment) => ($enrollment->riskLevel() === 'high' ? 1000 : 0) + $enrollment->daysSinceLastTraining())
            ->take(15)
            ->values();

        // Revenue Leakage: money already earned but never collected. The
        // training fee itself can never leak this way - an enrollment can't
        // be marked completed while it still has a training balance (see
        // EnrollmentController::complete()) - but a completed enrollment's
        // certificate fees, and a service already marked "completed"
        // (delivered), have no such guard, so they can sit unpaid
        // indefinitely once nobody has a reason to look at that student
        // again.
        $leakedCertificateFees = $completedEnrollments->load(['student', 'course'])
            ->flatMap(function (Enrollment $enrollment) {
                $rows = collect();

                if ($enrollment->onlineCertificateBalance() > 0) {
                    $rows->push([
                        'student' => $enrollment->student,
                        'label' => "Online Certificate — {$enrollment->course->name}",
                        'balance' => $enrollment->onlineCertificateBalance(),
                        'since' => $enrollment->updated_at,
                    ]);
                }

                if ($enrollment->studentCertificateBalance() > 0) {
                    $rows->push([
                        'student' => $enrollment->student,
                        'label' => "Student Certificate — {$enrollment->course->name}",
                        'balance' => $enrollment->studentCertificateBalance(),
                        'since' => $enrollment->updated_at,
                    ]);
                }

                return $rows;
            });

        $leakedServiceFees = StudentService::where('processing_status', 'completed')
            ->with(['student', 'service'])
            ->get()
            ->filter(fn (StudentService $studentService) => $studentService->balance() > 0)
            ->map(fn (StudentService $studentService) => [
                'student' => $studentService->student,
                'label' => $studentService->service->name,
                'balance' => $studentService->balance(),
                'since' => $studentService->updated_at,
            ]);

        $revenueLeakage = $leakedCertificateFees->concat($leakedServiceFees)
            ->sortByDesc('balance')
            ->values();

        // Top-line KPI cards: the numbers a Director should see at a glance
        // without reading any of the detail widgets below.
        $kpis = [
            'active_students' => Student::where('status', 'active')->count(),
            'training_today' => $trainingStats['today'],
            'pending_payments' => $outstandingEnrollments->sum(fn (Enrollment $enrollment) => $enrollment->balance()),
            'completed_training' => $completedEnrollments->count(),
            'active_vehicles' => Vehicle::where('status', 'active')->count(),
            'certificates_due' => $completedEnrollments->filter(fn (Enrollment $enrollment) => ! $enrollment->hasCertificate())->count(),
            'revenue_leakage' => $revenueLeakage->sum('balance'),
            'at_risk_students' => $atRiskEnrollments->count(),
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
            'pending_approvals' => DiscountRequest::where('status', 'pending')->count()
                + StudentCorrectionRequest::where('status', 'pending')->count(),
        ];

        return view('dashboard', compact('stats', 'newStudentTotals', 'paymentTotals', 'upcomingPayments', 'trainingProgress', 'absentStudents', 'trainingStats', 'lockedEnrollments', 'serviceProcessing', 'upgradeEligible', 'upgradeClosed', 'kpis', 'todaysOperations', 'revenueLeakage', 'learnersPermitRequests', 'onlineCertificateRequests', 'driversLicenseRequests', 'atRiskEnrollments'));
    }

    /**
     * Every not-yet-completed charge for the named catalog service,
     * oldest first - shared by the Learner's Permit and Online
     * Certificate widgets, which (unlike Driver's License Processing)
     * have no tracked turnaround and so never appear in the Service
     * Processing widget in any state.
     *
     * @return Collection<int, StudentService>
     */
    protected function pendingRequestsFor(string $serviceName): Collection
    {
        return StudentService::whereHas('service', fn ($query) => $query->where('name', $serviceName))
            ->where('processing_status', '!=', 'completed')
            ->with(['student', 'service'])
            ->oldest('created_at')
            ->get();
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
