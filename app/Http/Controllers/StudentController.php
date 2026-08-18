<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\ActivityLog;
use App\Models\Course;
use App\Models\DiscountRequest;
use App\Models\Instructor;
use App\Models\Service;
use App\Models\Student;
use App\Models\Vehicle;
use App\Services\EnrollmentService;
use App\Services\StudentChargeResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Student::with('courses');

        if ($search = $request->query('search')) {
            $query->where(function ($inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($courseId = $request->query('course_id')) {
            $query->whereHas('courses', fn ($inner) => $inner->where('courses.id', $courseId));
        }

        if ($payment = $request->query('payment')) {
            if ($payment === 'locked') {
                $query->whereHas('courses', fn ($inner) => $inner->where('course_student.status', 'locked'));
            } elseif ($payment === 'clear') {
                $query->whereDoesntHave('courses', fn ($inner) => $inner->where('course_student.status', 'locked'));
            }
        }

        $students = $query->latest()->paginate(10)->appends($request->query());
        $courses = Course::orderBy('name')->get();

        return view('students.index', compact('students', 'courses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $courses = Course::orderBy('name')->get();

        return view('students.create', compact('courses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentRequest $request, EnrollmentService $enrollmentService): RedirectResponse
    {
        $data = $request->validated();
        unset(
            $data['photo'], $data['course_id'], $data['starts_double_period'], $data['amount_paid'], $data['payment_method'],
            $data['discount_choice'], $data['custom_discount_percentage'], $data['custom_discount_amount'],
            $data['discount_reason'], $data['discount_reason_note'],
        );

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('student-photos', 'public');
        }

        $student = Student::create($data);
        $discountPending = false;

        if ($request->validated('course_id')) {
            $course = Course::findOrFail($request->validated('course_id'));

            $enrollmentService->enroll($student, $course, $request->user(), $student->enrollment_date, [
                'starts_double_period' => $request->boolean('starts_double_period'),
                'discount_choice' => $request->validated('discount_choice'),
                'custom_discount_percentage' => $request->validated('custom_discount_percentage'),
                'custom_discount_amount' => $request->validated('custom_discount_amount'),
                'discount_reason' => $request->validated('discount_reason'),
                'discount_reason_note' => $request->validated('discount_reason_note'),
                'amount_paid' => $request->validated('amount_paid'),
                'payment_method' => $request->validated('payment_method'),
            ]);

            $discountPending = DiscountRequest::where('student_id', $student->id)
                ->where('course_id', $course->id)
                ->where('status', 'pending')
                ->exists();
        }

        ActivityLog::record("Registered student {$student->name}");

        return Redirect::route('students.show', $student)
            ->with('status', $discountPending ? 'student-created-discount-pending' : 'student-created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student): View
    {
        $student->load([
            'courses',
            'payments' => fn ($query) => $query->with(['recordedBy', 'allocations.enrollment.course', 'allocations.studentService.service'])->latest('payment_date'),
            'certificates' => fn ($query) => $query->with('course')->latest('issue_date'),
            'attendances' => fn ($query) => $query->with('instructor')->latest('date'),
            'studentServices' => fn ($query) => $query->with('service'),
        ]);
        $instructors = Instructor::orderBy('name')->get();
        $chargedServiceIds = $student->studentServices->pluck('service_id');
        $availableServices = Service::where('is_active', true)->whereNotIn('id', $chargedServiceIds)->orderBy('name')->get();
        $financialOverview = StudentChargeResolver::allCharges($student);

        return view('students.show', compact('student', 'instructors', 'availableServices', 'financialOverview'));
    }

    /**
     * A training-only view of this student for the day-to-day Student Login
     * Training workflow: their login history and the quick-log form,
     * without any payment/balance/fee details.
     */
    public function trainingRecord(Student $student): View
    {
        $student->load(['courses', 'attendances' => fn ($query) => $query->with(['instructor', 'vehicle', 'loggedBy'])->latest('date')]);
        $instructors = Instructor::orderBy('name')->get();
        $vehicles = Vehicle::where('status', 'active')->orderBy('name')->get();

        return view('students.training-record', compact('student', 'instructors', 'vehicles'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student): View
    {
        return view('students.edit', compact('student'));
    }

    /**
     * Fields that can only be changed by a Director once a student is
     * already registered - changing them (especially the training
     * program, handled separately via course enrollment) has downstream
     * effects on training duration, payments, and certificates, so a
     * non-Director's attempt to change them is silently discarded here
     * regardless of what the request contains. Hidden/disabled inputs on
     * the edit form back this up, but this is the real enforcement.
     *
     * @var list<string>
     */
    protected const DIRECTOR_LOCKED_FIELDS = ['name', 'date_of_birth', 'phone'];

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $data = $request->validated();
        unset($data['photo']);

        if ($request->hasFile('photo')) {
            if ($student->photo_path) {
                Storage::disk('public')->delete($student->photo_path);
            }

            $data['photo_path'] = $request->file('photo')->store('student-photos', 'public');
        }

        $isDirector = $request->user()->isDirector();
        $originalValues = $student->only(self::DIRECTOR_LOCKED_FIELDS);

        if (! $isDirector) {
            foreach (self::DIRECTOR_LOCKED_FIELDS as $field) {
                unset($data[$field]);
            }
        }

        $student->update($data);

        if ($isDirector) {
            foreach (self::DIRECTOR_LOCKED_FIELDS as $field) {
                $old = $field === 'date_of_birth' ? optional($originalValues[$field])->format('Y-m-d') : $originalValues[$field];
                $new = $field === 'date_of_birth' ? optional($student->date_of_birth)->format('Y-m-d') : $student->{$field};

                if ($old !== $new) {
                    $label = match ($field) {
                        'date_of_birth' => 'Date of Birth',
                        default => ucfirst($field),
                    };

                    ActivityLog::record("Changed {$student->name}'s {$label}: {$old} → {$new}");
                }
            }
        }

        ActivityLog::record("Updated student {$student->name}");

        return Redirect::route('students.index')->with('status', 'student-updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student): RedirectResponse
    {
        if ($student->photo_path) {
            Storage::disk('public')->delete($student->photo_path);
        }

        $name = $student->name;
        $student->delete();

        ActivityLog::record("Deleted student {$name}");

        return Redirect::route('students.index')->with('status', 'student-deleted');
    }
}
