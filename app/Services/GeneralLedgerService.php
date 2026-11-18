<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * A double-entry ledger derived from the school's existing paid Payments
 * and paid Expenses - not a separate system of record. Every transaction
 * already recorded elsewhere becomes one balanced entry here: a debit and
 * a credit of the same amount, between the two accounts it moved money
 * between. Editing a Payment or Expense still goes through their own
 * existing correction flows; this only reads what they already store.
 */
class GeneralLedgerService
{
    public const CASH_ACCOUNT = 'Cash/Bank';

    public const INCOME_ACCOUNT = 'Student Fees Income';

    /**
     * Every ledger entry for the given year, oldest first.
     *
     * @return Collection<int, array{date: Carbon, type: string, reference: string, description: string, debit_account: string, credit_account: string, amount: float}>
     */
    public static function entries(int $year): Collection
    {
        $paymentEntries = Payment::where('status', 'paid')
            ->whereYear('payment_date', $year)
            ->with('student')
            ->get()
            ->map(fn (Payment $payment) => [
                'date' => $payment->payment_date,
                'type' => 'payment',
                'reference' => $payment->receipt_number,
                'description' => 'Payment received — '.($payment->student->name ?? 'Unknown student'),
                'debit_account' => self::CASH_ACCOUNT,
                'credit_account' => self::INCOME_ACCOUNT,
                'amount' => (float) $payment->amount,
            ]);

        $expenseEntries = Expense::paid()
            ->whereYear('expense_date', $year)
            ->get()
            ->map(fn (Expense $expense) => self::expenseEntry($expense));

        return $paymentEntries->concat($expenseEntries)
            ->sortBy('date')
            ->values();
    }

    /**
     * @return array{date: Carbon, type: string, reference: string, description: string, debit_account: string, credit_account: string, amount: float}
     */
    protected static function expenseEntry(Expense $expense): array
    {
        $account = Expense::CATEGORIES[$expense->category] ?? ucfirst(str_replace('_', ' ', $expense->category));

        $entry = [
            'date' => $expense->expense_date,
            'type' => 'expense',
            'reference' => 'EXP-'.$expense->id,
            'description' => $expense->description !== null && $expense->description !== ''
                ? $expense->description
                : $account,
            'amount' => (float) $expense->amount,
        ];

        // An investment/saving return is money coming back in, so it debits
        // Cash and credits its own income account - the reverse of every
        // other expense category, which debits the category and credits
        // Cash going out.
        return $expense->isIncome()
            ? $entry + ['debit_account' => self::CASH_ACCOUNT, 'credit_account' => $account]
            : $entry + ['debit_account' => $account, 'credit_account' => self::CASH_ACCOUNT];
    }

    /**
     * Each account's total debits and credits across the given entries,
     * plus whether the ledger balances (total debits == total credits,
     * which it always will by construction unless a rounding error makes
     * it worth surfacing).
     *
     * @param  Collection<int, array{debit_account: string, credit_account: string, amount: float}>  $entries
     * @return array{accounts: Collection<string, array{debit: float, credit: float}>, total_debit: float, total_credit: float, balanced: bool}
     */
    public static function trialBalance(Collection $entries): array
    {
        $accounts = [];

        foreach ($entries as $entry) {
            $accounts[$entry['debit_account']] ??= ['debit' => 0.0, 'credit' => 0.0];
            $accounts[$entry['credit_account']] ??= ['debit' => 0.0, 'credit' => 0.0];

            $accounts[$entry['debit_account']]['debit'] += $entry['amount'];
            $accounts[$entry['credit_account']]['credit'] += $entry['amount'];
        }

        $accounts = collect($accounts)->sortKeys();

        $totalDebit = round((float) $accounts->sum('debit'), 2);
        $totalCredit = round((float) $accounts->sum('credit'), 2);

        return [
            'accounts' => $accounts,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'balanced' => abs($totalDebit - $totalCredit) < 0.01,
        ];
    }
}
