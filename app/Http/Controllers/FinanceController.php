<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceController extends Controller
{
    /**
     * Display the income/expenses/balance summary for a given year, broken
     * down by month. Income is the sum of paid student payments; expenses
     * are the Director's recorded expenses for the school.
     */
    public function summary(Request $request): View
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

        return view('finance.summary', compact('year', 'months', 'totals'));
    }
}
