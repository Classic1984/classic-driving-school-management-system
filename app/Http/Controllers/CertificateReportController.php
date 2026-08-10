<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateReportController extends Controller
{
    /**
     * Periods this report can be filtered by.
     */
    protected const PERIODS = ['today', 'week', 'month', 'year'];

    protected const LABELS = [
        'today' => 'Today',
        'week' => 'This Week',
        'month' => 'This Month',
        'year' => 'This Year',
    ];

    /**
     * The click-through list of certificates issued during the selected
     * period.
     */
    public function index(Request $request): View
    {
        $period = $this->period($request);

        $certificates = $this->query($period)->with(['student', 'course', 'instructor'])->latest('issue_date')->get();
        $label = self::LABELS[$period];

        return view('certificate-report.index', compact('certificates', 'period', 'label'));
    }

    /**
     * Download a CSV (Excel-compatible) export of the same list.
     */
    public function export(Request $request): StreamedResponse
    {
        $period = $this->period($request);
        $certificates = $this->query($period)->with(['student', 'course', 'instructor'])->latest('issue_date')->get();

        return response()->streamDownload(function () use ($certificates) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Certificate #', 'Student ID', 'Student Name', 'Course', 'Instructor', 'Issue Date']);

            foreach ($certificates as $certificate) {
                fputcsv($handle, $this->row($certificate));
            }

            fclose($handle);
        }, "certificate-report-{$period}.csv", ['Content-Type' => 'text/csv']);
    }

    /**
     * Download a printable PDF export of the same list.
     */
    public function exportPdf(Request $request): Response
    {
        $period = $this->period($request);
        $certificates = $this->query($period)->with(['student', 'course', 'instructor'])->latest('issue_date')->get();
        $label = self::LABELS[$period];

        $pdf = Pdf::loadView('certificate-report.pdf', compact('certificates', 'label'));

        return $pdf->download("certificate-report-{$period}.pdf");
    }

    protected function period(Request $request): string
    {
        $period = $request->query('period', 'today');

        return in_array($period, self::PERIODS, true) ? $period : 'today';
    }

    protected function query(string $period): Builder
    {
        [$from, $to] = match ($period) {
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [today(), today()],
        };

        return Certificate::whereDate('issue_date', '>=', $from->toDateString())
            ->whereDate('issue_date', '<=', $to->toDateString());
    }

    /**
     * @return array<int, string>
     */
    protected function row(Certificate $certificate): array
    {
        return [
            $certificate->certificate_number,
            $certificate->student->student_id_number,
            $certificate->student->name,
            $certificate->course->name,
            $certificate->instructor?->name ?? '',
            $certificate->issue_date->format('Y-m-d'),
        ];
    }
}
