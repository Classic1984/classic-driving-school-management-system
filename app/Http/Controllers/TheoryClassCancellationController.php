<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\TheoryClassCancellation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class TheoryClassCancellationController extends Controller
{
    /**
     * Display upcoming and past cancelled theory classes, plus the form
     * to cancel another one.
     */
    public function index(): View
    {
        $cancellations = TheoryClassCancellation::with('cancelledBy')
            ->orderByDesc('class_date')
            ->get();

        return view('theory-class-cancellations.index', compact('cancellations'));
    }

    /**
     * Cancel a theory class date - the weekly reminder will text a
     * cancellation notice for it instead of the usual reminder.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'class_date' => ['required', 'date', 'after_or_equal:today', 'unique:theory_class_cancellations,class_date'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        TheoryClassCancellation::create([
            'class_date' => $data['class_date'],
            'reason' => $data['reason'] ?? null,
            'cancelled_by' => $request->user()->id,
        ]);

        ActivityLog::record("Cancelled the theory class for {$data['class_date']}");

        return Redirect::route('theory-class-cancellations.index')->with('status', 'cancellation-created');
    }

    /**
     * Un-cancel a theory class date, e.g. if it was cancelled by mistake.
     */
    public function destroy(TheoryClassCancellation $theoryClassCancellation): RedirectResponse
    {
        $classDate = $theoryClassCancellation->class_date->toDateString();
        $theoryClassCancellation->delete();

        ActivityLog::record("Un-cancelled the theory class for {$classDate}");

        return Redirect::route('theory-class-cancellations.index')->with('status', 'cancellation-removed');
    }
}
