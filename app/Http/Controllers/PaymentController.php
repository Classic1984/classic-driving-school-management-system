<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $payments = Payment::with(['student', 'course'])->latest('payment_date')->paginate(10);

        return view('payments.index', compact('payments'));
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
        $payment = Payment::create($request->validated());

        $this->refreshEnrollmentStatus($payment->student_id, $payment->course_id);

        return Redirect::route('payments.index')->with('status', 'payment-created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment): View
    {
        $payment->load(['student', 'course']);

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
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentRequest $request, Payment $payment): RedirectResponse
    {
        $previousStudentId = $payment->student_id;
        $previousCourseId = $payment->course_id;

        $payment->update($request->validated());

        $this->refreshEnrollmentStatus($previousStudentId, $previousCourseId);
        $this->refreshEnrollmentStatus($payment->student_id, $payment->course_id);

        return Redirect::route('payments.index')->with('status', 'payment-updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment): RedirectResponse
    {
        $studentId = $payment->student_id;
        $courseId = $payment->course_id;

        $payment->delete();

        $this->refreshEnrollmentStatus($studentId, $courseId);

        return Redirect::route('payments.index')->with('status', 'payment-deleted');
    }

    /**
     * Recompute the locked/active status for a student's enrollment in a
     * course immediately, so payments unlock training without waiting for
     * the daily scheduled refresh.
     */
    protected function refreshEnrollmentStatus(int $studentId, int $courseId): void
    {
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
}
