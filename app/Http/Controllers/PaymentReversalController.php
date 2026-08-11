<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentReversalRequest;
use App\Models\ActivityLog;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentReversal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class PaymentReversalController extends Controller
{
    /**
     * Show the confirmation form for reversing a payment.
     */
    public function create(Payment $payment): View
    {
        $payment->load(['student', 'allocations.enrollment.course', 'allocations.studentService.service']);

        return view('payments.reverse', compact('payment'));
    }

    /**
     * Reverse a payment: the payment record itself is never deleted or
     * edited - a reversal is logged and the payment's status moves to
     * "refunded", which automatically excludes it from every balance
     * calculation everywhere in the system.
     */
    public function store(StorePaymentReversalRequest $request, Payment $payment): RedirectResponse
    {
        $payment->load(['student', 'allocations.enrollment']);

        DB::transaction(function () use ($request, $payment) {
            PaymentReversal::create([
                'payment_id' => $payment->id,
                'reversed_by' => $request->user()->id,
                'amount' => $payment->amount,
                'reason' => $request->validated('reason'),
            ]);

            $payment->update(['status' => 'refunded']);
        });

        $payment->allocations->pluck('enrollment_id')->filter()->unique()
            ->each(fn (int $enrollmentId) => Enrollment::find($enrollmentId)?->reconcile());

        ActivityLog::record('Reversed a payment of ₦'.number_format((float) $payment->amount, 2)." for {$payment->student->name} (receipt {$payment->receipt_number})");

        return Redirect::route('payments.show', $payment)->with('status', 'payment-reversed');
    }
}
