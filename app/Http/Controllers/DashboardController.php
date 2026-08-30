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
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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

        // Real aggregate counts across every enrollment, not just the
        // handful of cards shown above - "in progress" and "not started"
        // both read status=active, split by whether any training day has
        // actually been logged yet (see Enrollment::statusLabel()).
        $activeEnrollments = Enrollment::where('status', 'active')->get();
        $trainingProgressStats = [
            'total_students' => Enrollment::distinct('student_id')->count('student_id'),
            'in_progress' => $activeEnrollments->filter(fn (Enrollment $enrollment) => $enrollment->attendedDays() > 0)->count(),
            'completed' => Enrollment::where('status', 'completed')->count(),
            'not_started' => $activeEnrollments->filter(fn (Enrollment $enrollment) => $enrollment->attendedDays() === 0)->count(),
        ];

        // Every actively-enrolled student is expected the moment today's
        // training day starts; checking in (a present/late training log)
        // moves them into Present, and anyone left over is still Absent -
        // live, all day - until app:finalize-daily-attendance seals the
        // day's roster after closing. Courses only carry a coarse
        // weekday/weekend schedule, not specific days: weekend-schedule
        // courses meet Saturdays only, everything else meets Mon-Fri, and
        // nobody is expected Sunday (the school is closed).
        [$presentToday, $absentToday] = $this->todaysAttendanceRoster();

        $lockedEnrollments = Enrollment::where('status', 'locked')
            ->with(['student', 'course'])
            ->latest('updated_at')
            ->get();

        // Same "N training day(s) remaining" threshold that triggers the
        // automatic reminder text (see maybeNotifyDaysRemaining()) - this
        // is the list behind that count, not just the number.
        $approachingCompletionEnrollments = Enrollment::where('status', 'active')
            ->with(['student', 'course'])
            ->get()
            // remainingTrainingDays() runs a fresh attendance query every
            // call - compute it once per enrollment here (stashed as a
            // plain runtime attribute the view can reuse) instead of
            // calling it again for filtering, sorting, and display.
            ->each(fn (Enrollment $enrollment) => $enrollment->remainingDays = $enrollment->remainingTrainingDays())
            ->filter(fn (Enrollment $enrollment) => $enrollment->remainingDays > 0
                && $enrollment->remainingDays <= Enrollment::TRAINING_DAYS_REMAINING_THRESHOLD)
            ->sortBy('remainingDays')
            ->values();

        $trainingStats = [
            'today' => $this->distinctStudentsTrained(today(), today()),
            'week' => $this->distinctStudentsTrained(now()->startOfWeek(), now()->endOfWeek()),
            'month' => $this->distinctStudentsTrained(now()->startOfMonth(), now()->endOfMonth()),
            'year' => $this->distinctStudentsTrained(now()->startOfYear(), now()->endOfYear()),
        ];

        $absenceStats = [
            'today' => $this->distinctStudentsAbsent(today(), today()),
            'week' => $this->distinctStudentsAbsent(now()->startOfWeek(), now()->endOfWeek()),
            'month' => $this->distinctStudentsAbsent(now()->startOfMonth(), now()->endOfMonth()),
            'year' => $this->distinctStudentsAbsent(now()->startOfYear(), now()->endOfYear()),
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
        $learnersPermitRequests = $this->pendingRequestsFor("Learner's Permit", 'permit_page');
        $onlineCertificateRequests = $this->pendingRequestsFor('Online Certificate', 'certificate_page');

        // Driver's License Processing does have a tracked turnaround, so
        // once it's marked "processing" it already shows above with a
        // progress bar - this only covers the gap before that: charged,
        // but processing hasn't been started yet.
        $driversLicenseRequests = StudentService::whereHas('service', fn ($query) => $query->where('name', "Driver's License Processing"))
            ->where('processing_status', 'not_started')
            ->with(['student', 'service'])
            ->oldest('created_at')
            ->paginate(10, ['*'], 'license_page')
            ->withQueryString();

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
            'approaching_completion' => $approachingCompletionEnrollments->count(),
            'locked_students' => Enrollment::where('status', 'locked')->count(),
            'pending_approvals' => DiscountRequest::where('status', 'pending')->count()
                + StudentCorrectionRequest::where('status', 'pending')->count(),
        ];

        return view('dashboard', compact('stats', 'newStudentTotals', 'paymentTotals', 'upcomingPayments', 'trainingProgress', 'trainingProgressStats', 'presentToday', 'absentToday', 'trainingStats', 'absenceStats', 'lockedEnrollments', 'serviceProcessing', 'upgradeEligible', 'upgradeClosed', 'kpis', 'todaysOperations', 'revenueLeakage', 'learnersPermitRequests', 'onlineCertificateRequests', 'driversLicenseRequests', 'atRiskEnrollments', 'approachingCompletionEnrollments'));
    }

    /**
     * Every not-yet-completed charge for the named catalog service,
     * oldest first - shared by the Learner's Permit and Online
     * Certificate widgets, which (unlike Driver's License Processing)
     * have no tracked turnaround and so never appear in the Service
     * Processing widget in any state. Paginated under its own page-name
     * query parameter so the two widgets (and the Driver's License
     * Requests one) can each page independently on the same dashboard.
     */
    protected function pendingRequestsFor(string $serviceName, string $pageName): LengthAwarePaginator
    {
        return StudentService::whereHas('service', fn ($query) => $query->where('name', $serviceName))
            ->where('processing_status', '!=', 'completed')
            ->with(['student', 'service'])
            ->oldest('created_at')
            ->paginate(10, ['*'], $pageName)
            ->withQueryString();
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

    /**
     * Distinct students marked absent (by app:finalize-daily-attendance)
     * within the given date range - the same shape as
     * distinctStudentsTrained() above, just counting the other side of
     * the daily roster.
     */
    protected function distinctStudentsAbsent($from, $to): int
    {
        return Attendance::where('status', 'absent')
            ->whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->distinct('student_id')
            ->count('student_id');
    }

    /**
     * The live Present/Absent split for today's training day: everyone
     * who's checked in so far (Present) versus every actively-enrolled
     * student expected today who hasn't (Absent) - mirrors the rule
     * app:finalize-daily-attendance uses to seal the day after closing.
     *
     * @return array{0: Collection, 1: Collection}
     */
    protected function todaysAttendanceRoster(): array
    {
        $today = now();

        // The school is closed Sundays - nobody is expected, so nobody
        // can be marked absent either.
        if ($today->isSunday()) {
            return [collect(), collect()];
        }

        $presentToday = Attendance::whereDate('date', today())
            ->whereIn('status', ['present', 'late'])
            ->with(['student', 'course', 'instructor'])
            ->latest('created_at')
            ->get();

        $loggedStudentIds = Attendance::whereDate('date', today())->pluck('student_id');

        $scheduleToday = $today->isSaturday() ? 'weekend' : 'weekday';

        $absentToday = Enrollment::where('status', 'active')
            ->whereNotIn('student_id', $loggedStudentIds)
            ->whereHas('course', fn ($query) => $query->where('schedule', $scheduleToday))
            ->with(['student', 'course'])
            ->get()
            ->unique('student_id')
            ->values();

        return [$presentToday, $absentToday];
    }
}
