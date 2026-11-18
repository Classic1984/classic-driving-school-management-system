<?php

namespace App\Http\Controllers;

use App\Services\GeneralLedgerService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GeneralLedgerController extends Controller
{
    protected const PER_PAGE = 25;

    /**
     * The general ledger for a given year: every paid payment and paid
     * expense as a balanced debit/credit entry, plus the trial balance
     * those entries roll up into. The trial balance always reflects every
     * entry for the year; only the entry list itself is paginated.
     */
    public function index(Request $request): View
    {
        $year = $this->year($request);
        $entries = GeneralLedgerService::entries($year);
        $trialBalance = GeneralLedgerService::trialBalance($entries);

        $page = $request->integer('page', 1);
        $paginatedEntries = new LengthAwarePaginator(
            $entries->forPage($page, self::PER_PAGE)->values(),
            $entries->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('general-ledger.index', ['entries' => $paginatedEntries, 'year' => $year, 'trialBalance' => $trialBalance]);
    }

    /**
     * Download a CSV of the ledger entries for the given year.
     */
    public function export(Request $request): StreamedResponse
    {
        $year = $this->year($request);
        $entries = GeneralLedgerService::entries($year);

        return response()->streamDownload(function () use ($entries) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Date', 'Reference', 'Description', 'Debit Account', 'Credit Account', 'Amount']);

            foreach ($entries as $entry) {
                fputcsv($handle, [
                    $entry['date']->format('Y-m-d'),
                    $entry['reference'],
                    $entry['description'],
                    $entry['debit_account'],
                    $entry['credit_account'],
                    number_format($entry['amount'], 2, '.', ''),
                ]);
            }

            fclose($handle);
        }, "general-ledger-{$year}.csv", ['Content-Type' => 'text/csv']);
    }

    /**
     * Download a printable PDF of the trial balance and ledger entries.
     */
    public function exportPdf(Request $request): Response
    {
        $year = $this->year($request);
        $entries = GeneralLedgerService::entries($year);
        $trialBalance = GeneralLedgerService::trialBalance($entries);

        $pdf = Pdf::loadView('general-ledger.pdf', compact('year', 'entries', 'trialBalance'));

        return $pdf->download("general-ledger-{$year}.pdf");
    }

    protected function year(Request $request): int
    {
        return (int) $request->query('year', now()->year);
    }
}
