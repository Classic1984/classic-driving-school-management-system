<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentCorrectionRequest;
use App\Models\ActivityLog;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\PaymentCorrectionLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class PaymentCorrectionController extends Controller
{
    /**
     * Show the form to correct how this payment's amount is split across
     * its charges - re-allocating the same total, never changing it.
     */
    public function edit(Payment $payment): View
    {
        $payment->load(['student', 'allocations.enrollment.course', 'allocations.studentService.service']);

        return view('payments.correct', compact('payment'));
    }

    /**
     * Apply the correction: log the before/after allocation with who made
     * it and why, then update the allocations themselves.
     */
    public function update(StorePaymentCorrectionRequest $request, Payment $payment): RedirectResponse
    {
        $payment->load(['allocations.enrollment.course', 'allocations.studentService.service']);

        $originalAllocations = $this->snapshot($payment);
        $newAmounts = collect($request->validated('allocations'))->keyBy('id');

        DB::transaction(function () use ($payment, $newAmounts) {
            foreach ($payment->allocations as $allocation) {
                $allocation->update(['amount' => $newAmounts[$allocation->id]['amount']]);
            }
        });

        $payment->refresh()->load(['allocations.enrollment.course', 'allocations.studentService.service']);

        PaymentCorrectionLog::create([
            'payment_id' => $payment->id,
            'corrected_by' => $request->user()->id,
            'reason' => $request->validated('reason'),
            'original_allocations' => $originalAllocations,
            'new_allocations' => $this->snapshot($payment),
        ]);

        $payment->allocations->pluck('enrollment_id')->filter()->unique()
            ->each(fn (int $enrollmentId) => Enrollment::find($enrollmentId)?->refreshStatus());

        ActivityLog::record("Corrected the allocation for payment {$payment->receipt_number}");

        return Redirect::route('payments.show', $payment)->with('status', 'payment-corrected');
    }

    /**
     * A label/amount snapshot of a payment's current allocations, for the
     * audit log's before/after record.
     *
     * @return array<int, array{label: string, amount: float}>
     */
    protected function snapshot(Payment $payment): array
    {
        return $payment->allocations
            ->map(fn (PaymentAllocation $allocation) => ['label' => $allocation->label(), 'amount' => (float) $allocation->amount])
            ->values()
            ->all();
    }
}
