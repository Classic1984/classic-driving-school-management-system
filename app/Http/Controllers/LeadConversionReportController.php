<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadConversionReportController extends Controller
{
    /**
     * Periods this report can be filtered by, applied to when the lead was
     * logged (its created_at), matching how the other click-through
     * reports filter by their subject's own date.
     */
    protected const PERIODS = ['today', 'week', 'month', 'year'];

    protected const LABELS = [
        'today' => 'Today',
        'week' => 'This Week',
        'month' => 'This Month',
        'year' => 'This Year',
    ];

    /**
     * How many leads logged in the period ended up New, Contacted,
     * Converted, or Lost - overall, and broken down by source - so staff
     * can see which channels are actually turning inquiries into students.
     */
    public function index(Request $request): View
    {
        $period = $this->period($request);

        $leads = $this->leads($period);
        $summary = $this->summary($leads);
        $rows = $this->sourceRows($leads);
        $label = self::LABELS[$period];

        return view('lead-conversion-report.index', compact('summary', 'rows', 'period', 'label'));
    }

    /**
     * Download a CSV (Excel-compatible) export of the source breakdown.
     */
    public function export(Request $request): StreamedResponse
    {
        $period = $this->period($request);
        $rows = $this->sourceRows($this->leads($period));

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Source', 'Total', 'New', 'Contacted', 'Converted', 'Lost', 'Conversion Rate']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['source'],
                    $row['total'],
                    $row['new'],
                    $row['contacted'],
                    $row['converted'],
                    $row['lost'],
                    "{$row['rate']}%",
                ]);
            }

            fclose($handle);
        }, "lead-conversion-report-{$period}.csv", ['Content-Type' => 'text/csv']);
    }

    /**
     * Download a printable PDF of the same summary and breakdown.
     */
    public function exportPdf(Request $request): Response
    {
        $period = $this->period($request);

        $leads = $this->leads($period);
        $summary = $this->summary($leads);
        $rows = $this->sourceRows($leads);
        $label = self::LABELS[$period];

        $pdf = Pdf::loadView('lead-conversion-report.pdf', compact('summary', 'rows', 'label'));

        return $pdf->download("lead-conversion-report-{$period}.pdf");
    }

    protected function period(Request $request): string
    {
        $period = $request->query('period', 'today');

        return in_array($period, self::PERIODS, true) ? $period : 'today';
    }

    /**
     * @return Collection<int, Lead>
     */
    protected function leads(string $period): Collection
    {
        [$from, $to] = match ($period) {
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [today(), today()],
        };

        return Lead::whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString())
            ->get();
    }

    /**
     * @param  Collection<int, Lead>  $leads
     * @return array{total: int, new: int, contacted: int, converted: int, lost: int, rate: float}
     */
    protected function summary(Collection $leads): array
    {
        $total = $leads->count();
        $converted = $leads->where('status', 'converted')->count();

        return [
            'total' => $total,
            'new' => $leads->where('status', 'new')->count(),
            'contacted' => $leads->where('status', 'contacted')->count(),
            'converted' => $converted,
            'lost' => $leads->where('status', 'lost')->count(),
            'rate' => $this->rate($converted, $total),
        ];
    }

    /**
     * One row per source (leads with no source given are grouped under
     * "Not Specified"), ordered by most leads first.
     *
     * @param  Collection<int, Lead>  $leads
     * @return Collection<int, array{source: string, total: int, new: int, contacted: int, converted: int, lost: int, rate: float}>
     */
    protected function sourceRows(Collection $leads): Collection
    {
        return $leads->groupBy(fn (Lead $lead) => $lead->source ?: 'Not Specified')
            ->map(function (Collection $group, string $source) {
                $total = $group->count();
                $converted = $group->where('status', 'converted')->count();

                return [
                    'source' => $source,
                    'total' => $total,
                    'new' => $group->where('status', 'new')->count(),
                    'contacted' => $group->where('status', 'contacted')->count(),
                    'converted' => $converted,
                    'lost' => $group->where('status', 'lost')->count(),
                    'rate' => $this->rate($converted, $total),
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    protected function rate(int $converted, int $total): float
    {
        return $total > 0 ? round($converted / $total * 100, 1) : 0.0;
    }
}
