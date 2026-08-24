<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\ActivityLog;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
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
     * A cheap snapshot the on-screen expense-update reminder polls to
     * decide whether anything changed since the last check. Count is
     * included alongside the latest updated_at because a hard delete
     * changes nothing's updated_at but does change the count.
     */
    public function lastUpdated(): JsonResponse
    {
        return response()->json([
            'count' => Expense::count(),
            'last_updated_at' => Expense::max('updated_at'),
        ]);
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
        $data = $request->validated();
        unset($data['receipt_photo']);

        if ($request->hasFile('receipt_photo')) {
            $data['receipt_photo_path'] = $request->file('receipt_photo')->store('expense-receipts', 'public');
        }

        $expense = Expense::create($data);

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
        $data = $request->validated();
        unset($data['receipt_photo']);

        if ($request->hasFile('receipt_photo')) {
            if ($expense->receipt_photo_path) {
                Storage::disk('public')->delete($expense->receipt_photo_path);
            }

            $data['receipt_photo_path'] = $request->file('receipt_photo')->store('expense-receipts', 'public');
        }

        $expense->update($data);

        ActivityLog::record("Updated an expense ({$expense->category})");

        return Redirect::route('expenses.index')->with('status', 'expense-updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense): RedirectResponse
    {
        if ($expense->receipt_photo_path) {
            Storage::disk('public')->delete($expense->receipt_photo_path);
        }

        $category = $expense->category;
        $expense->delete();

        ActivityLog::record("Deleted an expense ({$category})");

        return Redirect::route('expenses.index')->with('status', 'expense-deleted');
    }
}
