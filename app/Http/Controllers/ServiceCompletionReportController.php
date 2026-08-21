<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\StudentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ServiceCompletionReportController extends Controller
{
    /**
     * Periods this report can be filtered by, applied to when the charge
     * was marked completed. There's no dedicated "completed at" column -
     * processing_status only ever changes via the explicit status update
     * action, so updated_at doubles as that date.
     */
    protected const PERIODS = ['today', 'week', 'month', 'year', 'all_time'];

    protected const LABELS = [
        'today' => 'Today',
        'week' => 'This Week',
        'month' => 'This Month',
        'year' => 'This Year',
        'all_time' => 'All Time',
    ];

    /**
     * How many students actually obtained/completed a given catalog
     * service during the selected period - the historical counterpart to
     * a dashboard "requests" widget, which only shows what's still
     * pending. Works for any service (Learner's Permit, Driver's License
     * Processing, Online Certificate, or any other charged the same way),
     * not just one hardcoded by name.
     */
    public function index(Request $request, Service $service): View
    {
        $period = $this->period($request);

        $completed = $this->query($period, $service)->with('student')->latest('updated_at')->get();
        $label = self::LABELS[$period];

        return view('service-reports.index', compact('service', 'completed', 'period', 'label'));
    }

    /**
     * Download a CSV (Excel-compatible) export of the same list.
     */
    public function export(Request $request, Service $service): StreamedResponse
    {
        $period = $this->period($request);
        $completed = $this->query($period, $service)->with('student')->latest('updated_at')->get();

        return response()->streamDownload(function () use ($completed) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Student ID', 'Student Name', 'Charged Date', 'Completed Date']);

            foreach ($completed as $studentService) {
                fputcsv($handle, $this->row($studentService));
            }

            fclose($handle);
        }, "{$service->name}-report-{$period}.csv", ['Content-Type' => 'text/csv']);
    }

    /**
     * Download a printable PDF export of the same list.
     */
    public function exportPdf(Request $request, Service $service): Response
    {
        $period = $this->period($request);
        $completed = $this->query($period, $service)->with('student')->latest('updated_at')->get();
        $label = self::LABELS[$period];

        $pdf = Pdf::loadView('service-reports.pdf', compact('service', 'completed', 'label'));

        return $pdf->download("{$service->name}-report-{$period}.pdf");
    }

    protected function period(Request $request): string
    {
        $period = $request->query('period', 'all_time');

        return in_array($period, self::PERIODS, true) ? $period : 'all_time';
    }

    protected function query(string $period, Service $service): Builder
    {
        $query = StudentService::where('service_id', $service->id)
            ->where('processing_status', 'completed');

        [$from, $to] = match ($period) {
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            'all_time' => [null, null],
            default => [today(), today()],
        };

        if ($from !== null) {
            $query->whereDate('updated_at', '>=', $from->toDateString())
                ->whereDate('updated_at', '<=', $to->toDateString());
        }

        return $query;
    }

    /**
     * @return array<int, string>
     */
    protected function row(StudentService $studentService): array
    {
        return [
            $studentService->student->student_id_number,
            $studentService->student->name,
            $studentService->created_at->format('Y-m-d'),
            $studentService->updated_at->format('Y-m-d'),
        ];
    }
}
