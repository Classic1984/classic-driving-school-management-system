<?php

namespace App\Http\Controllers;

use App\Models\StudentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LearnersPermitReportController extends Controller
{
    /**
     * Periods this report can be filtered by, applied to when the permit
     * was marked obtained. There's no dedicated "obtained at" column -
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
     * How many students actually obtained a Learner's Permit during the
     * selected period - the historical counterpart to the dashboard's
     * Learner's Permit Requests widget, which only shows what's still
     * pending.
     */
    public function index(Request $request): View
    {
        $period = $this->period($request);

        $obtained = $this->query($period)->with('student')->latest('updated_at')->get();
        $label = self::LABELS[$period];

        return view('learners-permit-report.index', compact('obtained', 'period', 'label'));
    }

    /**
     * Download a CSV (Excel-compatible) export of the same list.
     */
    public function export(Request $request): StreamedResponse
    {
        $period = $this->period($request);
        $obtained = $this->query($period)->with('student')->latest('updated_at')->get();

        return response()->streamDownload(function () use ($obtained) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Student ID', 'Student Name', 'Charged Date', 'Obtained Date']);

            foreach ($obtained as $studentService) {
                fputcsv($handle, $this->row($studentService));
            }

            fclose($handle);
        }, "learners-permit-report-{$period}.csv", ['Content-Type' => 'text/csv']);
    }

    /**
     * Download a printable PDF export of the same list.
     */
    public function exportPdf(Request $request): Response
    {
        $period = $this->period($request);
        $obtained = $this->query($period)->with('student')->latest('updated_at')->get();
        $label = self::LABELS[$period];

        $pdf = Pdf::loadView('learners-permit-report.pdf', compact('obtained', 'label'));

        return $pdf->download("learners-permit-report-{$period}.pdf");
    }

    protected function period(Request $request): string
    {
        $period = $request->query('period', 'all_time');

        return in_array($period, self::PERIODS, true) ? $period : 'all_time';
    }

    protected function query(string $period): Builder
    {
        $query = StudentService::whereHas('service', fn ($q) => $q->where('name', "Learner's Permit"))
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
