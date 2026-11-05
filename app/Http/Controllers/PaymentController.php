<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\ActivityLog;
use App\Models\CorporatePayment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    /**
     * Periods the dashboard's Total Payments cards link into.
     */
    protected const PERIODS = ['week', 'month', 'last_6_months', 'all_time'];

    protected const LABELS = [
        'week' => 'This Week',
        'month' => 'This Month',
        'last_6_months' => 'Last 6 Months',
        'all_time' => 'All Time',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $period = $this->period($request);
        $month = $this->month($request);

        $payments = $this->query($period, $month)
            ->with(['student', 'course', 'recordedBy', 'allocations.enrollment.course', 'allocations.studentService.service', 'reversal'])
            ->latest('payment_date')
            ->paginate(10)
            ->appends($request->query());

        // The dashboard's Total Payments cards (and this page's own tiles)
        // fold in CorporatePayment amounts alongside individual student
        // Payments - see DashboardController - so these totals do too,
        // otherwise clicking through from the dashboard lands on a page
        // showing a different number for the same period. The "Payment
        // Records" table below stays individual-payments-only though:
        // CorporatePayment rows don't share its student/course-shaped
        // columns, so they're surfaced separately under Corporate
        // Invoicing rather than merged into this listing.
        $corporateTodayTotal = CorporatePayment::whereDate('payment_date', today())->sum('amount');
        $todayTotal = Payment::where('status', 'paid')
            ->whereDate('payment_date', today())
            ->sum('amount')
            + $corporateTodayTotal;

        $corporatePeriodTotal = $this->corporateQuery($period, $month)->sum('amount');
        $periodTotal = $this->query($period, $month)->where('status', 'paid')->sum('amount') + $corporatePeriodTotal;
        $periodLabel = $month ? $month->format('F Y') : self::LABELS[$period];

        return view('payments.index', compact('payments', 'todayTotal', 'periodTotal', 'periodLabel', 'period', 'corporateTodayTotal', 'corporatePeriodTotal'));
    }

    /**
     * Download a CSV export of the payments for the given period (or every
     * record, if no period is selected).
     */
    public function export(Request $request): StreamedResponse
    {
        $period = $this->period($request);
        $month = $this->month($request);
        $payments = $this->query($period, $month)->with(['student', 'course'])->latest('payment_date')->get();

        return response()->streamDownload(function () use ($payments) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Date', 'Student', 'Course', 'Amount', 'Method', 'Status', 'Reference Number']);

            foreach ($payments as $payment) {
                fputcsv($handle, [
                    $payment->payment_date->format('Y-m-d'),
                    $payment->student->name,
                    $payment->course->name ?? 'Multiple Services',
                    number_format((float) $payment->amount, 2, '.', ''),
                    $payment->payment_method,
                    $payment->status,
                    $payment->reference_number,
                ]);
            }

            fclose($handle);
        }, "payments-{$period}.csv", ['Content-Type' => 'text/csv']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('payments.create', $this->formOptions());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $payment = Payment::create([...$request->validated(), 'recorded_by' => $request->user()->id]);
        $payment->load(['student', 'course']);

        $this->refreshEnrollmentStatus($payment->student_id, $payment->course_id);

        ActivityLog::record('Recorded a payment of ₦'.number_format((float) $payment->amount, 2)." for {$payment->student->name} ({$payment->course->name})");

        return Redirect::route('payments.index')->with('status', 'payment-created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment): View
    {
        $payment->load(['student', 'course', 'recordedBy', 'allocations.enrollment.course', 'allocations.studentService.service', 'corrections.correctedBy', 'reversal.reversedBy']);

        return view('payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment): View
    {
        return view('payments.edit', [...$this->formOptions(), 'payment' => $payment]);
    }

    /**
     * Show a printable receipt for the specified payment: what it paid
     * for, and the resulting balance on each charge it touched.
     */
    public function receipt(Payment $payment): View
    {
        $payment->load(['student.courses', 'course.instructors', 'recordedBy', 'allocations.enrollment.course.instructors', 'allocations.studentService.service']);

        $balances = $payment->allocations
            ->unique(fn (PaymentAllocation $allocation) => $allocation->chargeKey())
            ->map(fn (PaymentAllocation $allocation) => ['label' => $allocation->label(), 'balance' => $allocation->chargeBalance()]);

        return view('payments.receipt', compact('payment', 'balances'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentRequest $request, Payment $payment): RedirectResponse
    {
        $previousStudentId = $payment->student_id;
        $previousCourseId = $payment->course_id;

        $payment->update($request->validated());
        $payment->load(['student', 'course']);

        $this->refreshEnrollmentStatus($previousStudentId, $previousCourseId);
        $this->refreshEnrollmentStatus($payment->student_id, $payment->course_id);

        $courseName = $payment->course->name ?? 'Multiple Services';
        ActivityLog::record("Updated a payment for {$payment->student->name} ({$courseName})");

        return Redirect::route('payments.index')->with('status', 'payment-updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment): RedirectResponse
    {
        $payment->load(['student', 'course']);
        $studentId = $payment->student_id;
        $courseId = $payment->course_id;
        $courseName = $payment->course->name ?? 'Multiple Services';
        $description = "Deleted a payment for {$payment->student->name} ({$courseName})";

        $payment->delete();

        $this->refreshEnrollmentStatus($studentId, $courseId);

        ActivityLog::record($description);

        return Redirect::route('payments.index')->with('status', 'payment-deleted');
    }

    /**
     * Recompute the locked/active status for a student's enrollment in a
     * course immediately, so payments unlock training without waiting for
     * the daily scheduled refresh.
     */
    protected function refreshEnrollmentStatus(int $studentId, ?int $courseId): void
    {
        // A payment covering only services (or split across several
        // charges via the allocation flow) has no single course_id - there's
        // no one enrollment to refresh, so this is a no-op rather than an
        // error.
        if ($courseId === null) {
            return;
        }

        Enrollment::where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->first()
            ?->refreshStatus();
    }

    /**
     * Get the option lists shared by the create and edit forms.
     *
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        return [
            'students' => Student::orderBy('name')->get(),
            'courses' => Course::orderBy('name')->get(),
        ];
    }

    protected function period(Request $request): string
    {
        $period = $request->query('period', 'all_time');

        return in_array($period, self::PERIODS, true) ? $period : 'all_time';
    }

    /**
     * An explicit "YYYY-MM" calendar month from the Revenue Trend chart's
     * per-bar links, overriding $period below so a past month (not just
     * "this month") can be drilled into.
     */
    protected function month(Request $request): ?Carbon
    {
        $month = $request->query('month');

        if ($month && \DateTime::createFromFormat('Y-m', $month) !== false) {
            return Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        }

        return null;
    }

    /**
     * Payments recorded during the given period, unfiltered for "all_time".
     * $month, when given, overrides $period with an explicit calendar month.
     */
    protected function query(string $period, ?Carbon $month = null): Builder
    {
        $query = Payment::query();

        if ($month) {
            return $query->whereYear('payment_date', $month->year)->whereMonth('payment_date', $month->month);
        }

        match ($period) {
            'week' => $query->whereDate('payment_date', '>=', now()->startOfWeek()->toDateString())
                ->whereDate('payment_date', '<=', now()->endOfWeek()->toDateString()),
            'month' => $query->whereYear('payment_date', now()->year)->whereMonth('payment_date', now()->month),
            // Same 6-calendar-month window (current month plus the 5 before
            // it) as the dashboard's Revenue Trend chart, so its "6-Month
            // Total" tile links to exactly what it's summing.
            'last_6_months' => $query->whereDate('payment_date', '>=', now()->subMonthsNoOverflow(5)->startOfMonth()->toDateString())
                ->whereDate('payment_date', '<=', now()->endOfMonth()->toDateString()),
            default => null,
        };

        return $query;
    }

    /**
     * CorporatePayments recorded during the given period, unfiltered for
     * "all_time" - same date-range logic as query(), just against the
     * corporate_payments table instead of payments.
     */
    protected function corporateQuery(string $period, ?Carbon $month = null): Builder
    {
        $query = CorporatePayment::query();

        if ($month) {
            return $query->whereYear('payment_date', $month->year)->whereMonth('payment_date', $month->month);
        }

        match ($period) {
            'week' => $query->whereDate('payment_date', '>=', now()->startOfWeek()->toDateString())
                ->whereDate('payment_date', '<=', now()->endOfWeek()->toDateString()),
            'month' => $query->whereYear('payment_date', now()->year)->whereMonth('payment_date', now()->month),
            'last_6_months' => $query->whereDate('payment_date', '>=', now()->subMonthsNoOverflow(5)->startOfMonth()->toDateString())
                ->whereDate('payment_date', '<=', now()->endOfMonth()->toDateString()),
            default => null,
        };

        return $query;
    }
}
