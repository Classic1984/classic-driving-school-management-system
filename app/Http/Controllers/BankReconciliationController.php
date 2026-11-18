<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BankReconciliation;
use App\Models\BankStatementLine;
use App\Services\GeneralLedgerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class BankReconciliationController extends Controller
{
    /**
     * Every past and in-progress reconciliation, plus the form to start a
     * new one.
     */
    public function index(): View
    {
        $reconciliations = BankReconciliation::withCount('lines')->latest('period_start')->get();

        return view('bank-reconciliation.index', compact('reconciliations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'opening_balance' => ['nullable', 'numeric'],
            'closing_balance' => ['nullable', 'numeric'],
        ]);

        $reconciliation = BankReconciliation::create($validated + [
            'status' => 'in_progress',
            'created_by' => $request->user()->id,
        ]);

        ActivityLog::record('Started a bank reconciliation for '.Carbon::parse($validated['period_start'])->format('M j').' – '.Carbon::parse($validated['period_end'])->format('M j, Y'));

        return redirect()->route('bank-reconciliation.show', $reconciliation)->with('status', 'reconciliation-started');
    }

    /**
     * The reconciliation workspace: every bank statement line against its
     * match status, and every system Cash/Bank movement for the period
     * that no line has claimed yet.
     */
    public function show(BankReconciliation $bankReconciliation): View
    {
        $bankReconciliation->load('lines', 'createdBy', 'completedBy');

        $cashMovements = GeneralLedgerService::cashMovements($bankReconciliation->period_start, $bankReconciliation->period_end);
        $claimed = $bankReconciliation->lines->where('status', 'matched')->map(fn (BankStatementLine $line) => $line->matched_type.':'.$line->matched_id);
        $unmatchedMovements = $cashMovements->reject(fn (array $movement) => $claimed->contains($movement['source_type'].':'.$movement['source_id']));

        return view('bank-reconciliation.show', [
            'bankReconciliation' => $bankReconciliation,
            'unmatchedMovements' => $unmatchedMovements,
        ]);
    }

    /**
     * Manually add one bank statement line, then try to auto-match it.
     */
    public function storeLine(Request $request, BankReconciliation $bankReconciliation): RedirectResponse
    {
        abort_unless($bankReconciliation->status === 'in_progress', 404);

        $validated = $request->validate([
            'line_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric'],
        ]);

        $line = $bankReconciliation->lines()->create($validated);

        $this->autoMatch($bankReconciliation, $line);

        return back()->with('status', 'line-added');
    }

    /**
     * Upload a CSV export of the bank statement: date,description,amount
     * (amount signed - positive for money in, negative for money out).
     * Every row becomes a line, each one auto-matched the same way a
     * manually added line is.
     */
    public function uploadCsv(Request $request, BankReconciliation $bankReconciliation): RedirectResponse
    {
        abort_unless($bankReconciliation->status === 'in_progress', 404);

        $request->validate([
            'statement' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $handle = fopen($request->file('statement')->getRealPath(), 'r');
        $header = array_map(fn ($col) => strtolower(trim($col)), fgetcsv($handle) ?: []);
        $dateCol = array_search('date', $header, true);
        $descriptionCol = array_search('description', $header, true);
        $amountCol = array_search('amount', $header, true);

        if ($dateCol === false || $descriptionCol === false || $amountCol === false) {
            fclose($handle);

            return back()->withErrors(['statement' => 'The CSV must have "date", "description", and "amount" columns.']);
        }

        $imported = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (! isset($row[$dateCol], $row[$descriptionCol], $row[$amountCol]) || trim($row[$dateCol]) === '') {
                continue;
            }

            try {
                $date = Carbon::parse(trim($row[$dateCol]));
            } catch (\Exception) {
                $skipped++;

                continue;
            }

            $amount = (float) str_replace([',', '₦'], '', $row[$amountCol]);

            $line = $bankReconciliation->lines()->create([
                'line_date' => $date->toDateString(),
                'description' => trim($row[$descriptionCol]) ?: 'Bank statement line',
                'amount' => $amount,
            ]);

            $this->autoMatch($bankReconciliation, $line);
            $imported++;
        }

        fclose($handle);

        ActivityLog::record("Imported {$imported} bank statement lines into a reconciliation");

        return back()->with('status', "Imported {$imported} lines".($skipped > 0 ? ", skipped {$skipped} with an unreadable date" : '').'.');
    }

    /**
     * Try to find exactly one unclaimed system Cash/Bank movement with the
     * same amount within a few days of this line's date. More than one
     * candidate, or none, is left for the Director to resolve by hand -
     * guessing wrong here would misreport the books.
     */
    protected function autoMatch(BankReconciliation $bankReconciliation, BankStatementLine $line): void
    {
        $movements = GeneralLedgerService::cashMovements(
            $bankReconciliation->period_start->copy()->subDays(5),
            $bankReconciliation->period_end->copy()->addDays(5)
        );

        $claimed = $bankReconciliation->lines()->where('status', 'matched')->get()
            ->map(fn (BankStatementLine $claimedLine) => $claimedLine->matched_type.':'.$claimedLine->matched_id);

        $candidates = $movements->filter(function (array $movement) use ($line, $claimed) {
            if ($claimed->contains($movement['source_type'].':'.$movement['source_id'])) {
                return false;
            }

            return abs($movement['amount'] - (float) $line->amount) < 0.01
                && abs(Carbon::parse($movement['date'])->diffInDays($line->line_date)) <= 5;
        });

        if ($candidates->count() === 1) {
            $match = $candidates->first();

            $line->update([
                'status' => 'matched',
                'matched_type' => $match['source_type'],
                'matched_id' => $match['source_id'],
            ]);
        }
    }

    /**
     * Manually link a line to a specific system Cash/Bank movement -
     * for the cases auto-match leaves alone (more than one same-amount
     * candidate, or outside the date window).
     */
    public function matchLine(Request $request, BankReconciliation $bankReconciliation, BankStatementLine $bankStatementLine): RedirectResponse
    {
        abort_unless($bankReconciliation->status === 'in_progress', 404);
        abort_unless($bankStatementLine->bank_reconciliation_id === $bankReconciliation->id, 404);

        $validated = $request->validate([
            'source' => ['required', 'regex:/^(payment|expense)\|\d+$/'],
        ]);

        [$sourceType, $sourceId] = explode('|', $validated['source']);

        $bankStatementLine->update([
            'status' => 'matched',
            'matched_type' => $sourceType,
            'matched_id' => (int) $sourceId,
        ]);

        return back()->with('status', 'line-matched');
    }

    public function unmatchLine(BankReconciliation $bankReconciliation, BankStatementLine $bankStatementLine): RedirectResponse
    {
        abort_unless($bankReconciliation->status === 'in_progress', 404);
        abort_unless($bankStatementLine->bank_reconciliation_id === $bankReconciliation->id, 404);

        $bankStatementLine->update(['status' => 'unmatched', 'matched_type' => null, 'matched_id' => null]);

        return back()->with('status', 'line-unmatched');
    }

    /**
     * Mark a line as deliberately not matched to anything in the books -
     * a bank fee, interest, or other transaction that doesn't correspond
     * to a Payment or Expense and isn't expected to.
     */
    public function ignoreLine(BankReconciliation $bankReconciliation, BankStatementLine $bankStatementLine): RedirectResponse
    {
        abort_unless($bankReconciliation->status === 'in_progress', 404);
        abort_unless($bankStatementLine->bank_reconciliation_id === $bankReconciliation->id, 404);

        $bankStatementLine->update(['status' => 'ignored', 'matched_type' => null, 'matched_id' => null]);

        return back()->with('status', 'line-ignored');
    }

    /**
     * Close out a reconciliation. This is a record-keeping action only -
     * it doesn't touch any Payment or Expense, it just documents that the
     * Director reviewed this period's bank statement against the books.
     */
    public function complete(BankReconciliation $bankReconciliation, Request $request): RedirectResponse
    {
        abort_unless($bankReconciliation->status === 'in_progress', 404);

        $bankReconciliation->update([
            'status' => 'completed',
            'completed_by' => $request->user()->id,
            'completed_at' => now(),
        ]);

        ActivityLog::record('Completed the bank reconciliation for '.$bankReconciliation->period_start->format('M j').' – '.$bankReconciliation->period_end->format('M j, Y'));

        return redirect()->route('bank-reconciliation.show', $bankReconciliation)->with('status', 'reconciliation-completed');
    }
}
