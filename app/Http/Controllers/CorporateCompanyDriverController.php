<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuickEnrollCorporateDriverRequest;
use App\Http\Requests\StoreCorporateCompanyDriverRequest;
use App\Models\ActivityLog;
use App\Models\CorporateCompany;
use App\Models\CorporateCompanyDriver;
use App\Models\Course;
use App\Models\Student;
use App\Services\EnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class CorporateCompanyDriverController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCorporateCompanyDriverRequest $request, CorporateCompany $corporateCompany): RedirectResponse
    {
        $driver = $corporateCompany->drivers()->create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        ActivityLog::record("Added driver {$driver->name} to corporate client ({$corporateCompany->name})");

        return Redirect::to(route('corporate-companies.show', $corporateCompany).'#drivers')->with('status', 'driver-added');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CorporateCompanyDriver $corporateCompanyDriver): RedirectResponse
    {
        $company = $corporateCompanyDriver->company;
        $name = $corporateCompanyDriver->name;

        $corporateCompanyDriver->delete();

        ActivityLog::record("Removed driver {$name} from corporate client ({$company->name})");

        return Redirect::route('corporate-companies.show', $company)->with('status', 'driver-removed');
    }

    /**
     * Show the minimal form for quick-enrolling this driver as a student.
     */
    public function enrollCreate(CorporateCompanyDriver $corporateCompanyDriver): View|RedirectResponse
    {
        if ($corporateCompanyDriver->student_id !== null) {
            return Redirect::route('students.show', $corporateCompanyDriver->student);
        }

        $courses = Course::orderBy('name')->get();

        return view('corporate.companies.drivers.enroll', [
            'driver' => $corporateCompanyDriver,
            'courses' => $courses,
        ]);
    }

    /**
     * Create a real Student + Enrollment for this driver, sponsored by
     * their company. No payment is recorded here - the company already
     * paid via its own corporate invoice, and Enrollment::balance() waives
     * a corporate-sponsored student's individual balance rather than
     * tracking that money a second time.
     */
    public function enrollStore(QuickEnrollCorporateDriverRequest $request, CorporateCompanyDriver $corporateCompanyDriver, EnrollmentService $enrollmentService): RedirectResponse
    {
        if ($corporateCompanyDriver->student_id !== null) {
            return Redirect::route('students.show', $corporateCompanyDriver->student);
        }

        $course = Course::findOrFail($request->validated('course_id'));

        // license_number carries over from the driver roster automatically
        // (it isn't asked for again in this form) and the students table
        // enforces it unique - drop it rather than let an unlikely
        // collision with an unrelated existing student crash the request.
        $licenseNumber = $corporateCompanyDriver->license_number;
        if ($licenseNumber !== null && Student::where('license_number', $licenseNumber)->exists()) {
            $licenseNumber = null;
        }

        $student = Student::create([
            'name' => $corporateCompanyDriver->name,
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'date_of_birth' => $request->validated('date_of_birth'),
            'license_number' => $licenseNumber,
            'corporate_company_id' => $corporateCompanyDriver->corporate_company_id,
            'course_type' => $course->course_type,
            'enrollment_date' => now()->toDateString(),
        ]);

        $enrollmentService->enroll($student, $course, $request->user(), $student->enrollment_date, []);

        $corporateCompanyDriver->forceFill(['student_id' => $student->id])->save();

        ActivityLog::record("Enrolled corporate driver {$corporateCompanyDriver->name} as student {$student->student_id_number}, sponsored by {$corporateCompanyDriver->company->name}");

        return Redirect::route('students.show', $student)->with('status', 'student-created');
    }
}
