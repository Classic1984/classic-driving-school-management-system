<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceApplicantRequest;
use App\Models\ActivityLog;
use App\Models\Service;
use App\Models\Student;
use App\Models\StudentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

/**
 * Driver's License Processing and Learner's Permit are flat catalog
 * Services (see Service/StudentService) billed independently of any
 * course enrollment - this controller gives each its own standalone
 * section (list + filters + a lightweight registration flow) instead of
 * only surfacing as Dashboard widgets, so staff stop treating every
 * paying customer as a "driving student."
 */
class ServiceApplicationController extends Controller
{
    protected const PERIODS = ['today', 'week', 'month', 'year', 'all_time'];

    protected const SOURCES = ['walk_in', 'existing_student'];

    protected const PROCESSING_STATUSES = ['not_started', 'processing', 'completed'];

    protected const PAYMENT_STATUSES = ['paid', 'part_payment', 'unpaid'];

    public function driversLicenseIndex(Request $request): View
    {
        return $this->index($request, "Driver's License Processing", 'driver-license', "Driver's License");
    }

    public function learnersPermitIndex(Request $request): View
    {
        return $this->index($request, "Learner's Permit", 'learners-permit', "Learner's Permit");
    }

    public function driversLicenseRegister(): View
    {
        return $this->register("Driver's License Processing", 'driver-license', "Driver's License");
    }

    public function learnersPermitRegister(): View
    {
        return $this->register("Learner's Permit", 'learners-permit', "Learner's Permit");
    }

    public function driversLicenseStore(StoreServiceApplicantRequest $request): RedirectResponse
    {
        return $this->store($request, "Driver's License Processing");
    }

    public function learnersPermitStore(StoreServiceApplicantRequest $request): RedirectResponse
    {
        return $this->store($request, "Learner's Permit");
    }

    /**
     * The applicant list for one catalog service: stat tiles covering
     * every charge ever made for it, and a filterable, paginated table of
     * the charges themselves.
     */
    protected function index(Request $request, string $serviceName, string $routePrefix, string $title): View
    {
        $service = Service::where('name', $serviceName)->first();

        if (! $service) {
            return $this->missingServiceView($serviceName, $title);
        }

        $query = StudentService::where('service_id', $service->id)->with('student.courses');

        $period = $request->query('period', 'all_time');
        if (in_array($period, self::PERIODS, true)) {
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

        return view('service-applications.index', [
            'title' => $title,
            'service' => $service,
            'routePrefix' => $routePrefix,
            'applications' => $applications,
            'stats' => $stats,
            'period' => $period,
            'source' => $source,
            'status' => $status,
        ]);
    }

    /**
     * The "Register Applicant" screen: pick an existing student, or
     * register a new walk-in applicant - either way ends up at the
     * Record Payment screen with this service preselected, ready to
     * charge and pay for in one action.
     */
    protected function register(string $serviceName, string $routePrefix, string $title): View
    {
        $service = Service::where('name', $serviceName)->first();

        if (! $service) {
            return $this->missingServiceView($serviceName, $title);
        }

        $students = Student::orderBy('name')->get();

        return view('service-applications.register', [
            'title' => $title,
            'service' => $service,
            'routePrefix' => $routePrefix,
            'students' => $students,
        ]);
    }

    /**
     * Register a brand new walk-in applicant - only the fields the
     * students table actually requires beyond what this flow already
     * knows. No course, no payment yet: this hands off straight to
     * Record Payment (preselected on this service) to finish the job.
     */
    protected function store(StoreServiceApplicantRequest $request, string $serviceName): RedirectResponse
    {
        $service = Service::where('name', $serviceName)->first();

        if (! $service) {
            return Redirect::back()->withErrors(['name' => "The \"{$serviceName}\" service hasn't been set up yet - ask a director to add it under Services first."]);
        }

        $student = Student::create([
            ...$request->validated(),
            'enrollment_date' => now()->toDateString(),
        ]);

        ActivityLog::record("Registered walk-in applicant {$student->name} for {$service->name}");

        return Redirect::route('payments.record.create', [
            'student_id' => $student->id,
            'charge_type' => 'new_service',
            'charge_id' => $service->id,
        ]);
    }

    /**
     * A friendly stand-in for the raw 404 a missing catalog Service would
     * otherwise cause - production databases aren't guaranteed to have
     * every catalog row the app's code assumes by exact name (the deploy
     * process only runs migrations, not ServicePriceListSeeder), so a
     * director renaming or never creating "Driver's License Processing"/
     * "Learner's Permit" shouldn't look like this page is broken.
     */
    protected function missingServiceView(string $serviceName, string $title): View
    {
        return view('service-applications.missing', [
            'title' => $title,
            'serviceName' => $serviceName,
        ]);
    }
}
