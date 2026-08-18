<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentAllocationRequest;
use App\Models\ActivityLog;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Student;
use App\Models\StudentService;
use App\Services\StudentChargeResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class PaymentAllocationController extends Controller
{
    /**
     * Show the multi-service payment entry screen: a student picker, and -
     * once a student is selected - the grid of their open charges
     * (training, course-outline certificates, and flat services) to
     * allocate this payment across.
     */
    public function create(Request $request): View
    {
        $students = Student::orderBy('name')->get();
        $student = null;
        $charges = collect();

        if ($request->filled('student_id')) {
            $student = Student::find($request->integer('student_id'));
            $charges = $student ? StudentChargeResolver::openCharges($student) : collect();
        }

        return view('payments.record', compact('students', 'student', 'charges'));
    }

    /**
     * Record a payment and allocate it across the selected charges.
     */
    public function store(StorePaymentAllocationRequest $request): RedirectResponse
    {
        $student = Student::findOrFail($request->validated('student_id'));
        $allocations = $request->validated('allocations');

        // Resolved before the payment exists, since a charge that this
        // payment pays off in full would no longer show up as "open" -
        // and therefore wouldn't be found - afterward.
        $charges = StudentChargeResolver::openCharges($student)->keyBy(fn ($charge) => "{$charge['type']}:{$charge['id']}");

        $payment = DB::transaction(function () use ($request, $student, $allocations, $charges) {
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

            foreach ($allocations as $row) {
                $type = $row['type'];
                $id = $row['id'];
                $studentService = null;

                // A "new_service" row bills the student for a service
                // they haven't been charged for before and pays for it in
                // the same action - charge it now, then allocate against
                // the charge just created.
                if ($type === 'new_service') {
                    $studentService = StudentService::firstOrCreate(
                        ['student_id' => $student->id, 'service_id' => $id],
                        ['price' => $charges->get("new_service:{$id}")['price']]
                    );

                    $type = 'service';
                    $id = $studentService->id;
                }

                PaymentAllocation::create([
                    'payment_id' => $payment->id,
                    'allocation_type' => $type,
                    'enrollment_id' => $type === 'service' ? null : $id,
                    'student_service_id' => $type === 'service' ? $id : null,
                    'amount' => $row['amount'],
                ]);

                if ($type === 'service') {
                    ($studentService ?? StudentService::find($id))->maybeAutoStartProcessing();
                }
            }

            return $payment;
        });

        foreach ($student->courses as $course) {
            $course->pivot->refreshStatus();
        }

        $summary = collect($allocations)
            ->map(fn ($row) => $charges->get("{$row['type']}:{$row['id']}")['label'] ?? $row['type'])
            ->implode(', ');

        ActivityLog::record('Recorded a payment of ₦'.number_format((float) $payment->amount, 2)." for {$student->name} ({$summary})");

        return Redirect::route('students.show', $student)->with('status', 'payment-created');
    }
}
