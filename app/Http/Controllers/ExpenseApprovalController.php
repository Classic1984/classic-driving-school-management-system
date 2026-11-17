<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Expense;
use App\Notifications\ExpenseApprovedNotification;
use App\Notifications\ExpenseRejectedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ExpenseApprovalController extends Controller
{
    /**
     * Approve a pending expense request. Approval and payment are
     * deliberately separate events - this does not itself move any
     * money, it only clears the expense to be paid (see pay() below).
     */
    public function approve(Expense $expense): RedirectResponse
    {
        abort_unless($expense->isPending(), 404);

        $expense->update([
            'status' => 'approved',
            'resolved_by' => request()->user()->id,
            'resolved_at' => now(),
        ]);

        if ($expense->requestedBy) {
            $expense->requestedBy->notify(new ExpenseApprovedNotification($expense));
        }

        ActivityLog::record('Approved an expense request of ₦'.number_format((float) $expense->amount, 2)." ({$expense->category})");

        return Redirect::route('approvals.index')->with('status', 'expense-request-approved');
    }

    /**
     * Reject a pending expense request. It never enters the expenditure
     * totals - rejecting one is final, with an optional comment for why.
     */
    public function reject(Request $request, Expense $expense): RedirectResponse
    {
        abort_unless($expense->isPending(), 404);

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $expense->update([
            'status' => 'rejected',
            'resolved_by' => request()->user()->id,
            'resolved_at' => now(),
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        if ($expense->requestedBy) {
            $expense->requestedBy->notify(new ExpenseRejectedNotification($expense));
        }

        ActivityLog::record("Rejected an expense request ({$expense->category})");

        return Redirect::route('approvals.index')->with('status', 'expense-request-rejected');
    }

    /**
     * Record that an approved expense has actually been paid out. This is
     * the moment it enters the expenditure totals - approving a request
     * only clears it to be paid, it doesn't move any money itself.
     */
    public function pay(Expense $expense): RedirectResponse
    {
        abort_unless($expense->isApproved(), 404);

        $expense->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        ActivityLog::record('Paid an approved expense of ₦'.number_format((float) $expense->amount, 2)." ({$expense->category})");

        return Redirect::route('expenses.index')->with('status', 'expense-paid');
    }
}
