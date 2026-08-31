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
        $newAllocations = $this->snapshot($payment);

        PaymentCorrectionLog::create([
            'payment_id' => $payment->id,
            'corrected_by' => $request->user()->id,
            'reason' => $request->validated('reason'),
            'original_allocations' => $originalAllocations,
            'new_allocations' => $newAllocations,
        ]);

        // reconcile() rather than refreshStatus() - a correction can move
        // money off an enrollment's training allocation (e.g. into a
        // service/certificate allocation on the same payment) and drop its
        // balance() back above zero, which must be able to revert an
        // already-"completed" enrollment. refreshStatus() deliberately
        // never un-completes an enrollment, so it would leave a completed
        // enrollment silently under-paid.
        $payment->allocations->pluck('enrollment_id')->filter()->unique()
            ->each(fn (int $enrollmentId) => Enrollment::find($enrollmentId)?->reconcile());

        $changes = $this->describeChanges($originalAllocations, $newAllocations);
        ActivityLog::record("Corrected the allocation for payment {$payment->receipt_number}: {$changes}");

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

    /**
     * A human-readable "label ₦old → ₦new" summary of every allocation
     * whose amount actually changed, for the general Activity Log entry -
     * the correction re-splits the same total, so it's the per-allocation
     * amounts (not the payment total) that tell the real story.
     *
     * @param  array<int, array{label: string, amount: float}>  $original
     * @param  array<int, array{label: string, amount: float}>  $new
     */
    protected function describeChanges(array $original, array $new): string
    {
        $changes = collect($new)
            ->filter(fn (array $allocation, int $index) => $allocation['amount'] !== $original[$index]['amount'])
            ->map(fn (array $allocation, int $index) => "{$allocation['label']} ₦".number_format($original[$index]['amount'], 2).' → ₦'.number_format($allocation['amount'], 2))
            ->values();

        return $changes->isEmpty() ? 'no amounts changed' : $changes->implode(', ');
    }
}
