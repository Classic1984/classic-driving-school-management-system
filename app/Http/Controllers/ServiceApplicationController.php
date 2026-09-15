<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRegistrationRequest;
use App\Models\ActivityLog;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Service;
use App\Models\Student;
use App\Models\StudentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

/**
 * Driver's License Processing and Learner's Permit are flat catalog
 * Services (see Service/StudentService) billed independently of any
 * course enrollment - this controller gives each its own standalone
 * section (a combined register-and-pay form as the landing page, plus a
 * separate applicant list) instead of only surfacing as Dashboard
 * widgets, so staff stop treating every paying customer as a "driving
 * student."
 */
class ServiceApplicationController extends Controller
{
    protected const PERIODS = ['today', 'week', 'month', 'year', 'all_time'];

    protected const SOURCES = ['walk_in', 'existing_student'];

    protected const PROCESSING_STATUSES = ['not_started', 'processing', 'completed'];

    protected const PAYMENT_STATUSES = ['paid', 'part_payment', 'unpaid'];

    public function driversLicenseIndex(): View
    {
        return $this->form("Driver's License Processing", 'driver-license', "Driver's License");
    }

    public function learnersPermitIndex(): View
    {
        return $this->form("Learner's Permit", 'learners-permit', "Learner's Permit");
    }

    public function driversLicenseApplicants(Request $request): View
    {
        return $this->applicants($request, "Driver's License Processing", 'driver-license', "Driver's License");
    }

    public function learnersPermitApplicants(Request $request): View
    {
        return $this->applicants($request, "Learner's Permit", 'learners-permit', "Learner's Permit");
    }

    public function onlineCertificateIndex(): View
    {
        return $this->form('Online Certificate', 'online-certificate', 'Online Certificate');
    }

    public function studentCertificateIndex(): View
    {
        return $this->form('Student Certificate', 'student-certificate', 'Student Certificate');
    }

    public function onlineCertificateApplicants(Request $request): View
    {
        return $this->applicants($request, 'Online Certificate', 'online-certificate', 'Online Certificate');
    }

    public function studentCertificateApplicants(Request $request): View
    {
        return $this->applicants($request, 'Student Certificate', 'student-certificate', 'Student Certificate');
    }

    public function driversLicenseStore(StoreServiceRegistrationRequest $request): RedirectResponse
    {
        return $this->store($request, "Driver's License Processing");
    }

    public function learnersPermitStore(StoreServiceRegistrationRequest $request): RedirectResponse
    {
        return $this->store($request, "Learner's Permit");
    }

    public function onlineCertificateStore(StoreServiceRegistrationRequest $request): RedirectResponse
    {
        return $this->store($request, 'Online Certificate');
    }

    public function studentCertificateStore(StoreServiceRegistrationRequest $request): RedirectResponse
    {
        return $this->store($request, 'Student Certificate');
    }

    /**
     * The landing page for this section: register an applicant (existing
     * student or new walk-in) and take their payment in one combined
     * form, so staff never have to jump to a separate payment screen to
     * finish the job.
     */
    protected function form(string $serviceName, string $routePrefix, string $title): View
    {
        $service = Service::where('name', $serviceName)->first();

        if (! $service) {
            return $this->missingServiceView($serviceName, $title);
        }

        $students = Student::orderBy('name')->get();

        return view('service-applications.index', [
            'title' => $title,
            'service' => $service,
            'routePrefix' => $routePrefix,
            'students' => $students,
        ]);
    }

    /**
     * The applicant list for one catalog service: stat tiles covering
     * every charge ever made for it, and a filterable, paginated table of
     * the charges themselves.
     */
    protected function applicants(Request $request, string $serviceName, string $routePrefix, string $title): View
    {
        $service = Service::where('name', $serviceName)->first();

        if (! $service) {
            return $this->missingServiceView($serviceName, $title);
        }

        $query = StudentService::where('service_id', $service->id)->with('student.courses');

        // An exact date takes priority over the relative Period filter -
        // it's how a director looks up "who registered/paid on the 1st of
        // Jan" instead of only ever being able to browse in today/week/
        // month/year buckets.
        $date = $this->exactDate($request);
        $period = $request->query('period', 'all_time');

        if ($date !== null) {
            $query->whereDate('created_at', $date);
        } elseif (in_array($period, self::PERIODS, true)) {
            // Bounds are cast to plain dates rather than left as Carbon
            // datetimes, which stringify with a "00:00:00" time component
            // that would silently exclude a charge made exactly on the
            // Monday the week starts - the same bug already fixed once
            // this session in DashboardController.
            match ($period) {
                'today' => $query->whereDate('created_at', today()),
                'week' => $query->whereBetween('created_at', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()]),
                'month' => $query->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month),
                'year' => $query->whereYear('created_at', now()->year),
                default => null,
            };
        }

        $source = $request->query('source');
        if ($source === 'walk_in') {
            $query->whereHas('student', fn ($q) => $q->whereDoesntHave('courses'));
        } elseif ($source === 'existing_student') {
            $query->whereHas('student', fn ($q) => $q->whereHas('courses'));
        }

        $status = $request->query('status');
        if (in_array($status, self::PROCESSING_STATUSES, true)) {
            $query->where('processing_status', $status);
        } elseif (in_array($status, self::PAYMENT_STATUSES, true)) {
            // A correlated subquery compared directly in WHERE, rather than
            // a withSum() alias filtered via HAVING - paginate() re-runs
            // this query wrapped in its own count subquery, and SQLite
            // rejects a HAVING clause that isn't grouped by an aggregate
            // ("HAVING clause on a non-aggregate query"), which a plain
            // WHERE comparison against a real column never triggers.
            $paidExpression = "coalesce((select sum(pa.amount) from payment_allocations pa
                inner join payments p on p.id = pa.payment_id
                where pa.student_service_id = student_services.id and p.status = 'paid'), 0)";

            match ($status) {
                'paid' => $query->whereRaw("{$paidExpression} >= student_services.price"),
                'part_payment' => $query->whereRaw("{$paidExpression} > 0 and {$paidExpression} < student_services.price"),
                'unpaid' => $query->whereRaw("{$paidExpression} <= 0"),
            };
        }

        $applications = $query->latest('created_at')->paginate(15)->withQueryString();

        $baseline = StudentService::where('service_id', $service->id);
        $stats = [
            'total' => (clone $baseline)->count(),
            'walk_in' => (clone $baseline)->whereHas('student', fn ($q) => $q->whereDoesntHave('courses'))->count(),
            'existing_student' => (clone $baseline)->whereHas('student', fn ($q) => $q->whereHas('courses'))->count(),
            'pending_processing' => (clone $baseline)->where('processing_status', '!=', 'completed')->count(),
        ];

        return view('service-applications.applicants', [
            'title' => $title,
            'service' => $service,
            'routePrefix' => $routePrefix,
            'applications' => $applications,
            'stats' => $stats,
            'period' => $period,
            'date' => $date,
            'source' => $source,
            'status' => $status,
        ]);
    }

    /**
     * A specific calendar date to filter by, if the request named a valid
     * one - takes priority over the relative Period filter when present.
     */
    protected function exactDate(Request $request): ?string
    {
        $date = $request->query('date');

        return ($date && \DateTime::createFromFormat('Y-m-d', $date) !== false) ? $date : null;
    }

    /**
     * Register an applicant - existing student or new walk-in - charge
     * them for this service (or add to their existing charge for it if
     * they were already billed), and record their payment, all in one
     * action instead of a separate hand-off to a generic payment screen.
     */
    protected function store(StoreServiceRegistrationRequest $request, string $serviceName): RedirectResponse
    {
        $service = Service::where('name', $serviceName)->first();

        if (! $service) {
            return Redirect::back()->withErrors(['name' => "The \"{$serviceName}\" service hasn't been set up yet - ask a director to add it under Services first."]);
        }

        $student = $request->validated('mode') === 'existing'
            ? Student::findOrFail($request->validated('student_id'))
            : Student::create([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'phone' => $request->validated('phone'),
                'date_of_birth' => $request->validated('date_of_birth'),
                'enrollment_date' => now()->toDateString(),
            ]);

        $studentService = StudentService::firstOrCreate(
            ['student_id' => $student->id, 'service_id' => $service->id],
            ['price' => $service->price]
        );

        $payment = DB::transaction(function () use ($request, $student, $studentService) {
            $payment = Payment::create([
                'student_id' => $student->id,
                'course_id' => null,
                'amount' => $request->validated('amount'),
                'payment_date' => $request->validated('payment_date'),
                'payment_method' => $request->validated('payment_method'),
                'status' => 'paid',
                'reference_number' => $request->validated('reference_number'),
                'notes' => $request->validated('notes'),
                'recorded_by' => $request->user()->id,
            ]);

            PaymentAllocation::create([
                'payment_id' => $payment->id,
                'allocation_type' => 'service',
                'enrollment_id' => null,
                'student_service_id' => $studentService->id,
                'amount' => $request->validated('amount'),
            ]);

            return $payment;
        });

        $studentService->maybeAutoStartProcessing();

        ActivityLog::record("Registered {$student->name} for {$service->name} and recorded a payment of ₦".number_format((float) $payment->amount, 2));

        return Redirect::route('students.show', $student)->with('status', 'payment-created');
    }

    /**
     * A friendly stand-in for the raw 404 a missing catalog Service would
     * otherwise cause - production databases aren't guaranteed to have
     * every catalog row the app's code assumes by exact name, so a
     * director renaming or never creating "Driver's License Processing"/
     * "Learner's Permit" shouldn't look like this page is broken.
     */
    protected function missingServiceView(string $serviceName, string $title): View
    {
        return view('service-applications.missing', [
            'title' => $title,
            'serviceName' => $serviceName,
            'suggested' => Service::defaultCatalogEntry($serviceName),
        ]);
    }
}
