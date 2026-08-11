<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\ActivityLog;
use App\Models\Course;
use App\Models\DiscountAuditLog;
use App\Models\Instructor;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Student;
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
    public function store(StoreStudentRequest $request): RedirectResponse
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

        // Assigning a training program (and the discount/payment that ride
        // along with it) is Director-only - a non-Director's course_id is
        // ignored entirely, leaving the student registered but unenrolled,
        // regardless of what a tampered request might contain.
        if ($request->user()->isDirector() && $request->validated('course_id')) {
            $course = Course::findOrFail($request->validated('course_id'));
            $originalFee = (float) $course->fee;
            [$discountPercentage, $discountAmount] = $this->resolveDiscount($request, $originalFee);
            $finalFee = max(0, $originalFee - $discountAmount);

            // A weekday student starting double period immediately burns through
            // 4 training days in just 2 calendar days, so their balance is due
            // that much sooner than the course's normal grace period.
            $gracePeriodDays = (! $course->isWeekend() && $request->boolean('starts_double_period'))
                ? 2
                : $course->gracePeriodDays();

            $student->courses()->attach($course->id, [
                'enrolled_at' => $student->enrollment_date->toDateString(),
                'due_date' => $student->enrollment_date->copy()->addDays($gracePeriodDays)->toDateString(),
                'status' => 'active',
                'fee' => $finalFee,
                'original_fee' => $originalFee,
                'discount_percentage' => $discountAmount > 0 ? $discountPercentage : null,
                'discount_amount' => $discountAmount > 0 ? $discountAmount : null,
                'discount_reason' => $discountAmount > 0 ? $request->validated('discount_reason') : null,
                'discount_reason_note' => $discountAmount > 0 ? $request->validated('discount_reason_note') : null,
                'discount_approved_by' => $discountAmount > 0 ? $request->user()->id : null,
                // Certificate fees are part of the course outline, not an
                // opt-in add-on - every student enrolling in a course that
                // offers a certificate is charged for it from day one, the
                // same way the training fee itself is locked in here.
                'online_certificate_fee' => $course->online_certificate_fee,
                'student_certificate_fee' => $course->student_certificate_fee,
            ]);

            if ($discountAmount > 0) {
                DiscountAuditLog::create([
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                    'applied_by' => $request->user()->id,
                    'original_fee' => $originalFee,
                    'discount_percentage' => $discountPercentage,
                    'discount_amount' => $discountAmount,
                    'final_fee' => $finalFee,
                    'reason' => $request->validated('discount_reason'),
                    'reason_note' => $request->validated('discount_reason_note'),
                ]);
            }

            if ($amountPaid = $request->validated('amount_paid')) {
                Payment::create([
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                    'amount' => $amountPaid,
                    'payment_date' => $student->enrollment_date->toDateString(),
                    'payment_method' => $request->validated('payment_method'),
                    'status' => 'paid',
                    'recorded_by' => $request->user()->id,
                ]);
            }
        }

        ActivityLog::record("Registered student {$student->name}");

        return Redirect::route('students.show', $student)->with('status', 'student-created');
    }

    /**
     * Work out the discount percentage and naira amount for this
     * registration from the validated discount fields.
     *
     * @return array{0: ?float, 1: float}
     */
    protected function resolveDiscount(StoreStudentRequest $request, float $originalFee): array
    {
        $choice = $request->validated('discount_choice');

        if (! $choice) {
            return [null, 0.0];
        }

        if ($choice === 'custom') {
            if ($amount = $request->validated('custom_discount_amount')) {
                $amount = min((float) $amount, $originalFee);

                return [
                    $originalFee > 0 ? round($amount / $originalFee * 100, 2) : 0.0,
                    round($amount, 2),
                ];
            }

            $percentage = (float) $request->validated('custom_discount_percentage');

            return [$percentage, round($originalFee * $percentage / 100, 2)];
        }

        // Presets are fixed naira amounts, not percentages.
        $amount = min((float) $choice, $originalFee);

        return [
            $originalFee > 0 ? round($amount / $originalFee * 100, 2) : 0.0,
            round($amount, 2),
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student): View
    {
        $student->load([
            'courses',
            'payments',
            'certificates' => fn ($query) => $query->with('course')->latest('issue_date'),
            'attendances' => fn ($query) => $query->with('instructor')->latest('date'),
            'studentServices' => fn ($query) => $query->with('service'),
        ]);
        $instructors = Instructor::orderBy('name')->get();
        $chargedServiceIds = $student->studentServices->pluck('service_id');
        $availableServices = Service::where('is_active', true)->whereNotIn('id', $chargedServiceIds)->orderBy('name')->get();

        return view('students.show', compact('student', 'instructors', 'availableServices'));
    }

    /**
     * A training-only view of this student for the day-to-day Student Login
     * Training workflow: their login history and the quick-log form,
     * without any payment/balance/fee details.
     */
    public function trainingRecord(Student $student): View
    {
        $student->load(['courses', 'attendances' => fn ($query) => $query->with(['instructor', 'loggedBy'])->latest('date')]);
        $instructors = Instructor::orderBy('name')->get();

        return view('students.training-record', compact('student', 'instructors'));
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
