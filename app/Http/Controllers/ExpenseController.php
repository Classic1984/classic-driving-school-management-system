<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\ActivityLog;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $expenses = Expense::latest('expense_date')->paginate(10);

        return view('expenses.index', compact('expenses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('expenses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $expense = Expense::create($request->validated());

        ActivityLog::record('Recorded an expense of ₦'.number_format((float) $expense->amount, 2)." ({$expense->category})");

        return Redirect::route('expenses.index')->with('status', 'expense-created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense): View
    {
        return view('expenses.show', compact('expense'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense): View
    {
        return view('expenses.edit', compact('expense'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $expense->update($request->validated());

        ActivityLog::record("Updated an expense ({$expense->category})");

        return Redirect::route('expenses.index')->with('status', 'expense-updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense): RedirectResponse
    {
        $category = $expense->category;
        $expense->delete();

        ActivityLog::record("Deleted an expense ({$category})");

        return Redirect::route('expenses.index')->with('status', 'expense-deleted');
    }
}
