<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Instructor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InstructorActivityReportController extends Controller
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
     * Sessions conducted, training days logged, and distinct students
     * trained per instructor during the selected period.
     */
    public function index(Request $request): View
    {
        $period = $this->period($request);

        $rows = $this->rows($period);
        $label = self::LABELS[$period];

        return view('instructor-activity-report.index', compact('rows', 'period', 'label'));
    }

    /**
     * Download a CSV (Excel-compatible) export of the same list.
     */
    public function export(Request $request): StreamedResponse
    {
        $period = $this->period($request);
        $rows = $this->rows($period);

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Instructor', 'Sessions Conducted', 'Training Days Logged', 'Students Trained']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['instructor']->name,
                    $row['sessions'],
                    $row['training_days'],
                    $row['students_trained'],
                ]);
            }

            fclose($handle);
        }, "instructor-activity-report-{$period}.csv", ['Content-Type' => 'text/csv']);
    }

    /**
     * Download a printable PDF export of the same list.
     */
    public function exportPdf(Request $request): Response
    {
        $period = $this->period($request);
        $rows = $this->rows($period);
        $label = self::LABELS[$period];

        $pdf = Pdf::loadView('instructor-activity-report.pdf', compact('rows', 'label'));

        return $pdf->download("instructor-activity-report-{$period}.pdf");
    }

    protected function period(Request $request): string
    {
        $period = $request->query('period', 'today');

        return in_array($period, self::PERIODS, true) ? $period : 'today';
    }

    /**
     * One row per instructor with their activity for the period, ordered by
     * most sessions conducted first.
     *
     * @return Collection<int, array{instructor: Instructor, sessions: int, training_days: int, students_trained: int}>
     */
    protected function rows(string $period): Collection
    {
        [$from, $to] = match ($period) {
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [today(), today()],
        };

        $attendances = Attendance::where('status', 'present')
            ->whereNotNull('instructor_id')
            ->whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->get();

        $byInstructor = $attendances->groupBy('instructor_id');

        return Instructor::orderBy('name')->get()
            ->map(function (Instructor $instructor) use ($byInstructor) {
                $sessions = $byInstructor->get($instructor->id, collect());

                return [
                    'instructor' => $instructor,
                    'sessions' => $sessions->count(),
                    'training_days' => (int) $sessions->sum(fn (Attendance $attendance) => $attendance->duration ?? 1),
                    'students_trained' => $sessions->pluck('student_id')->unique()->count(),
                ];
            })
            ->filter(fn (array $row) => $row['sessions'] > 0)
            ->sortByDesc('sessions')
            ->values();
    }
}
