<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\StudentChargeResolver;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReferralSourceReportController extends Controller
{
    /**
     * Periods this report can be filtered by, applied to when the student
     * registered (their enrollment_date). Defaults to "all_time" since a
     * channel's real value - completion and revenue - only shows up once
     * its students have had time to actually pay and finish training, not
     * on the day they registered.
     */
    protected const PERIODS = ['week', 'month', 'year', 'all_time'];

    protected const LABELS = [
        'week' => 'This Week',
        'month' => 'This Month',
        'year' => 'This Year',
        'all_time' => 'All Time',
    ];

    /**
     * Human-readable labels for the "How Did You Know About Us?" values
     * captured at registration - the same options offered on the
     * registration form.
     *
     * @var array<string, string>
     */
    protected const SOURCE_LABELS = [
        'flyer' => 'Flyer',
        'referral' => 'Referral',
        'facebook' => 'Facebook',
        'other' => 'Other',
    ];

    /**
     * Which registration channel is actually paying its way: how many
     * students it brought in, how many finished training versus
     * withdrew, and how much revenue it produced - so a Director can see
     * where the marketing money is best spent, not just where the
     * inquiries come from.
     */
    public function index(Request $request): View
    {
        $period = $this->period($request);

        $students = $this->students($period);
        $rows = $this->sourceRows($students);
        $summary = $this->summary($rows);
        $label = self::LABELS[$period];

        return view('referral-source-report.index', compact('summary', 'rows', 'period', 'label'));
    }

    /**
     * Download a CSV (Excel-compatible) export of the source breakdown.
     */
    public function export(Request $request): StreamedResponse
    {
        $period = $this->period($request);
        $rows = $this->sourceRows($this->students($period));

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Source', 'Total Students', 'Active', 'Completed', 'Withdrawn', 'Completion Rate', 'Revenue Collected', 'Outstanding Balance', 'Avg. Revenue / Student']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['source'],
                    $row['total'],
                    $row['active'],
                    $row['completed'],
                    $row['withdrawn'],
                    "{$row['completion_rate']}%",
                    number_format($row['revenue'], 2),
                    number_format($row['outstanding'], 2),
                    number_format($row['avg_revenue'], 2),
                ]);
            }

            fclose($handle);
        }, "referral-source-report-{$period}.csv", ['Content-Type' => 'text/csv']);
    }

    /**
     * Download a printable PDF of the same summary and breakdown.
     */
    public function exportPdf(Request $request): Response
    {
        $period = $this->period($request);

        $students = $this->students($period);
        $rows = $this->sourceRows($students);
        $summary = $this->summary($rows);
        $label = self::LABELS[$period];

        $pdf = Pdf::loadView('referral-source-report.pdf', compact('summary', 'rows', 'label'));

        return $pdf->download("referral-source-report-{$period}.pdf");
    }

    protected function period(Request $request): string
    {
        $period = $request->query('period', 'all_time');

        return in_array($period, self::PERIODS, true) ? $period : 'all_time';
    }

    /**
     * @return Collection<int, Student>
     */
    protected function students(string $period): Collection
    {
        $query = Student::with(['payments', 'courses', 'studentServices.service']);

        match ($period) {
            'week' => $query->whereDate('enrollment_date', '>=', now()->startOfWeek()->toDateString())
                ->whereDate('enrollment_date', '<=', now()->endOfWeek()->toDateString()),
            'month' => $query->whereYear('enrollment_date', now()->year)->whereMonth('enrollment_date', now()->month),
            'year' => $query->whereYear('enrollment_date', now()->year),
            default => null,
        };

        return $query->get();
    }

    /**
     * One row per source (students with no source given are grouped under
     * "Not Specified"), ordered by revenue collected first - the channel
     * actually worth the most, not just the one with the most sign-ups.
     *
     * @param  Collection<int, Student>  $students
     * @return Collection<int, array{source: string, total: int, active: int, completed: int, withdrawn: int, completion_rate: float, revenue: float, outstanding: float, avg_revenue: float}>
     */
    protected function sourceRows(Collection $students): Collection
    {
        return $students->groupBy(fn (Student $student) => self::SOURCE_LABELS[$student->referral_source] ?? 'Not Specified')
            ->map(function (Collection $group, string $source) {
                $total = $group->count();
                $revenue = $group->sum(fn (Student $student) => $student->payments->where('status', 'paid')->sum('amount'));
                $outstanding = $group->sum(fn (Student $student) => StudentChargeResolver::allCharges($student)->sum('balance'));

                return [
                    'source' => $source,
                    'total' => $total,
                    'active' => $group->where('status', 'active')->count(),
                    'completed' => $group->where('status', 'completed')->count(),
                    'withdrawn' => $group->where('status', 'withdrawn')->count(),
                    'completion_rate' => $total > 0 ? round($group->where('status', 'completed')->count() / $total * 100, 1) : 0.0,
                    'revenue' => (float) $revenue,
                    'outstanding' => (float) $outstanding,
                    'avg_revenue' => $total > 0 ? round($revenue / $total, 2) : 0.0,
                ];
            })
            ->sortByDesc('revenue')
            ->values();
    }

    /**
     * @param  Collection<int, array{source: string, total: int, active: int, completed: int, withdrawn: int, completion_rate: float, revenue: float, outstanding: float, avg_revenue: float}>  $rows
     * @return array{total: int, revenue: float, outstanding: float}
     */
    protected function summary(Collection $rows): array
    {
        return [
            'total' => $rows->sum('total'),
            'revenue' => $rows->sum('revenue'),
            'outstanding' => $rows->sum('outstanding'),
        ];
    }
}
