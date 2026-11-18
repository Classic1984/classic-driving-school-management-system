<?php

namespace App\Http\Controllers;

use App\Services\GeneralLedgerService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinancialStatementController extends Controller
{
    /**
     * The Income Statement and Balance Sheet for a given year, built
     * entirely from the General Ledger - no separate figures to keep in
     * sync with it.
     */
    public function index(Request $request): View
    {
        $year = $this->year($request);
        $incomeStatement = GeneralLedgerService::incomeStatement($year);
        $balanceSheet = GeneralLedgerService::balanceSheet($year);
        $asOfDate = Carbon::create($year, 12, 31)->format('M j, Y');

        return view('financial-statements.index', compact('year', 'incomeStatement', 'balanceSheet', 'asOfDate'));
    }

    /**
     * Download a CSV of both statements for the year.
     */
    public function export(Request $request): StreamedResponse
    {
        $year = $this->year($request);
        $incomeStatement = GeneralLedgerService::incomeStatement($year);
        $balanceSheet = GeneralLedgerService::balanceSheet($year);

        return response()->streamDownload(function () use ($incomeStatement, $balanceSheet) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Income Statement']);
            fputcsv($handle, ['Account', 'Amount']);
            foreach ($incomeStatement['revenue'] as $account => $amount) {
                fputcsv($handle, [$account, number_format($amount, 2, '.', '')]);
            }
            fputcsv($handle, ['Total Revenue', number_format($incomeStatement['total_revenue'], 2, '.', '')]);
            foreach ($incomeStatement['expenses'] as $account => $amount) {
                fputcsv($handle, [$account, number_format(-$amount, 2, '.', '')]);
            }
            fputcsv($handle, ['Total Expenses', number_format(-$incomeStatement['total_expenses'], 2, '.', '')]);
            fputcsv($handle, ['Net Income', number_format($incomeStatement['net_income'], 2, '.', '')]);

            fputcsv($handle, []);
            fputcsv($handle, ['Balance Sheet']);
            fputcsv($handle, ['Account', 'Amount']);
            foreach ($balanceSheet['assets'] as $account => $amount) {
                fputcsv($handle, [$account, number_format($amount, 2, '.', '')]);
            }
            fputcsv($handle, ['Total Assets', number_format($balanceSheet['total_assets'], 2, '.', '')]);
            fputcsv($handle, ['Total Liabilities', number_format($balanceSheet['total_liabilities'], 2, '.', '')]);
            foreach ($balanceSheet['equity'] as $account => $amount) {
                fputcsv($handle, [$account, number_format($amount, 2, '.', '')]);
            }
            fputcsv($handle, ['Total Equity', number_format($balanceSheet['total_equity'], 2, '.', '')]);

            fclose($handle);
        }, "financial-statements-{$year}.csv", ['Content-Type' => 'text/csv']);
    }

    /**
     * Download a printable PDF of both statements.
     */
    public function exportPdf(Request $request): Response
    {
        $year = $this->year($request);
        $incomeStatement = GeneralLedgerService::incomeStatement($year);
        $balanceSheet = GeneralLedgerService::balanceSheet($year);
        $asOfDate = Carbon::create($year, 12, 31)->format('M j, Y');

        $pdf = Pdf::loadView('financial-statements.pdf', compact('year', 'incomeStatement', 'balanceSheet', 'asOfDate'));

        return $pdf->download("financial-statements-{$year}.pdf");
    }

    protected function year(Request $request): int
    {
        return (int) $request->query('year', now()->year);
    }
}
