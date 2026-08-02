<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceController extends Controller
{
    /**
     * Display the income/expenses/balance summary for a given year, broken
     * down by month. Income is the sum of paid student payments; expenses
     * are the Director's recorded expenses for the school.
     */
    public function summary(Request $request): View
    {
        [$year, $months, $totals] = $this->computeSummary($request);
        $today = $this->computeToday();

        return view('finance.summary', compact('year', 'months', 'totals', 'today'));
    }

    /**
     * Download a CSV export of the same month-by-month summary.
     */
    public function export(Request $request): StreamedResponse
    {
        [$year, $months, $totals] = $this->computeSummary($request);

        return response()->streamDownload(function () use ($months, $totals) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Month', 'Income', 'Expenses', 'Balance']);

            foreach ($months as $month) {
                fputcsv($handle, [
                    $month['label'],
                    number_format($month['income'], 2, '.', ''),
                    number_format($month['expenses'], 2, '.', ''),
                    number_format($month['balance'], 2, '.', ''),
                ]);
            }

            fputcsv($handle, [
                'Year Total',
                number_format($totals['income'], 2, '.', ''),
                number_format($totals['expenses'], 2, '.', ''),
                number_format($totals['balance'], 2, '.', ''),
            ]);

            fclose($handle);
        }, "finance-summary-{$year}.csv", ['Content-Type' => 'text/csv']);
    }

    /**
     * @return array{0: int, 1: \Illuminate\Support\Collection, 2: array<string, float>}
     */
    protected function computeSummary(Request $request): array
    {
        $year = (int) $request->query('year', now()->year);

        $payments = Payment::where('status', 'paid')
            ->whereYear('payment_date', $year)
            ->get(['amount', 'payment_date']);

        $expenses = Expense::whereYear('expense_date', $year)->get(['amount', 'expense_date']);

        $months = collect(range(1, 12))->map(function (int $month) use ($payments, $expenses, $year) {
            $income = $payments->filter(fn ($payment) => $payment->payment_date->month === $month)->sum('amount');
            $expenseTotal = $expenses->filter(fn ($expense) => $expense->expense_date->month === $month)->sum('amount');

            return [
                'label' => Carbon::create($year, $month, 1)->format('F'),
                'income' => $income,
                'expenses' => $expenseTotal,
                'balance' => $income - $expenseTotal,
            ];
        });

        $totals = [
            'income' => $months->sum('income'),
            'expenses' => $months->sum('expenses'),
            'balance' => $months->sum('balance'),
        ];

        return [$year, $months, $totals];
    }

    /**
     * Today's income, expenses incurred, and the resulting balance —
     * automatically deducted with no extra entry required.
     *
     * @return array<string, float>
     */
    protected function computeToday(): array
    {
        $income = Payment::where('status', 'paid')->whereDate('payment_date', today())->sum('amount');
        $expenses = Expense::whereDate('expense_date', today())->sum('amount');

        return [
            'income' => $income,
            'expenses' => $expenses,
            'balance' => $income - $expenses,
        ];
    }
}
