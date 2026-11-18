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
     * The equity account an opening balance is credited (or, if the
     * school somehow ran a cumulative deficit, debited) against - the
     * standard double-entry counterpart to carrying a prior period's
     * cash position into a new one.
     */
    public const RETAINED_EARNINGS_ACCOUNT = 'Retained Earnings';

    /**
     * Every ledger entry for the given year, oldest first. If any paid
     * payment or paid expense predates this year, the very first entry
     * is a synthetic Opening Balance - the net Cash/Bank position
     * carried forward from every prior year - so a new year's ledger
     * starts from where the last one actually left off instead of
     * resetting Cash/Bank to zero.
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

        $entries = $paymentEntries->concat($expenseEntries);

        $openingBalance = self::openingCashBalance($year);

        if (abs($openingBalance) >= 0.01) {
            $entries->push([
                'date' => Carbon::create($year, 1, 1),
                'type' => 'opening_balance',
                'reference' => 'OPENING-BALANCE',
                'description' => 'Opening Balance brought forward from '.($year - 1).' and earlier',
                'debit_account' => $openingBalance > 0 ? self::CASH_ACCOUNT : self::RETAINED_EARNINGS_ACCOUNT,
                'credit_account' => $openingBalance > 0 ? self::RETAINED_EARNINGS_ACCOUNT : self::CASH_ACCOUNT,
                'amount' => abs($openingBalance),
            ]);
        }

        return $entries->sortBy('date')->values();
    }

    /**
     * The net Cash/Bank position (every paid payment, plus every paid
     * expense signed the same way cashMovements() signs them) across
     * every year before the given one - what a new year's books should
     * actually start from, rather than zero.
     */
    public static function openingCashBalance(int $year): float
    {
        $yearStart = Carbon::create($year, 1, 1)->toDateString();

        $income = (float) Payment::where('status', 'paid')
            ->where('payment_date', '<', $yearStart)
            ->sum('amount');

        $income += (float) Expense::paid()
            ->where('expense_date', '<', $yearStart)
            ->whereIn('category', Expense::INCOME_CATEGORIES)
            ->sum('amount');

        $outflow = (float) Expense::paid()
            ->where('expense_date', '<', $yearStart)
            ->whereNotIn('category', Expense::INCOME_CATEGORIES)
            ->sum('amount');

        return round($income - $outflow, 2);
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
     * Every Cash/Bank movement between two dates (inclusive), signed for
     * matching against a bank statement - positive for money in, negative
     * for money out - each tagged with the Payment or Expense it came
     * from, for Bank Reconciliation to match against.
     *
     * @return Collection<int, array{date: Carbon, description: string, amount: float, source_type: string, source_id: int}>
     */
    public static function cashMovements(Carbon $from, Carbon $to): Collection
    {
        $paymentMovements = Payment::where('status', 'paid')
            ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])
            ->with('student')
            ->get()
            ->map(fn (Payment $payment) => [
                'date' => $payment->payment_date,
                'description' => 'Payment received — '.($payment->student->name ?? 'Unknown student'),
                'amount' => (float) $payment->amount,
                'source_type' => 'payment',
                'source_id' => $payment->id,
            ]);

        $expenseMovements = Expense::paid()
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(function (Expense $expense) {
                $entry = self::expenseEntry($expense);

                return [
                    'date' => $expense->expense_date,
                    'description' => $entry['description'],
                    'amount' => $expense->isIncome() ? (float) $expense->amount : -(float) $expense->amount,
                    'source_type' => 'expense',
                    'source_id' => $expense->id,
                ];
            });

        return $paymentMovements->concat($expenseMovements)
            ->sortBy('date')
            ->values();
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
