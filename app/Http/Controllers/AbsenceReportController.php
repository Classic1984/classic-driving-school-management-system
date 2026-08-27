<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Enrollment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AbsenceReportController extends Controller
{
    /**
     * Periods the dashboard's Absences cards link into.
     */
    protected const PERIODS = ['today', 'week', 'month', 'year'];

    protected const LABELS = [
        'today' => 'Today',
        'week' => 'This Week',
        'month' => 'This Month',
        'year' => 'This Year',
    ];

    /**
     * The click-through list of students marked absent during the selected
     * period, sealed by app:finalize-daily-attendance.
     */
    public function index(Request $request): View
    {
        $period = $this->period($request);

        $attendances = $this->query($period)->with(['student', 'course'])->latest('date')->get();
        $enrollmentStatuses = $this->enrollmentStatuses($attendances);
        $attendancesByDate = $this->groupByDate($attendances);
        $label = self::LABELS[$period];

        return view('absence-report.index', compact('attendances', 'attendancesByDate', 'enrollmentStatuses', 'period', 'label'));
    }

    /**
     * Download a CSV (Excel-compatible) export of the same list.
     */
    public function export(Request $request): StreamedResponse
    {
        $period = $this->period($request);
        $attendances = $this->query($period)->with(['student', 'course'])->latest('date')->get();
        $enrollmentStatuses = $this->enrollmentStatuses($attendances);

        return response()->streamDownload(function () use ($attendances, $enrollmentStatuses) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Student ID', 'Student Name', 'Absence Date', 'Course', 'Training Status']);

            foreach ($attendances as $attendance) {
                fputcsv($handle, $this->row($attendance, $enrollmentStatuses));
            }

            fclose($handle);
        }, "absence-report-{$period}.csv", ['Content-Type' => 'text/csv']);
    }

    /**
     * Download a printable PDF export of the same list.
     */
    public function exportPdf(Request $request): Response
    {
        $period = $this->period($request);
        $attendances = $this->query($period)->with(['student', 'course'])->latest('date')->get();
        $enrollmentStatuses = $this->enrollmentStatuses($attendances);
        $attendancesByDate = $this->groupByDate($attendances);
        $label = self::LABELS[$period];

        $pdf = Pdf::loadView('absence-report.pdf', compact('attendancesByDate', 'enrollmentStatuses', 'label'));

        return $pdf->download("absence-report-{$period}.pdf");
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

        return Attendance::where('status', 'absent')
            ->whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString());
    }

    /**
     * Group attendance rows by date - the report shows a roster of student
     * names per day rather than one flat row per record. Relies on the
     * caller having already sorted by "latest('date')", so groups come out
     * most-recent-day-first.
     */
    protected function groupByDate(Collection $attendances): Collection
    {
        return $attendances->groupBy(fn (Attendance $attendance) => $attendance->date->format('Y-m-d'));
    }

    /**
     * Look up every attendance row's enrollment status in one query
     * instead of one per row, keyed by "studentId:courseId".
     *
     * @return array<string, string>
     */
    protected function enrollmentStatuses(Collection $attendances): array
    {
        $pairs = $attendances->map(fn (Attendance $attendance) => [
            'student_id' => $attendance->student_id,
            'course_id' => $attendance->course_id,
        ])->unique(fn (array $pair) => "{$pair['student_id']}:{$pair['course_id']}");

        if ($pairs->isEmpty()) {
            return [];
        }

        return Enrollment::where(function ($query) use ($pairs) {
            foreach ($pairs as $pair) {
                $query->orWhere(fn ($inner) => $inner->where('student_id', $pair['student_id'])->where('course_id', $pair['course_id']));
            }
        })->get()->mapWithKeys(fn (Enrollment $enrollment) => [
            "{$enrollment->student_id}:{$enrollment->course_id}" => $enrollment->trainingStatusLabel(),
        ])->all();
    }

    /**
     * @param  array<string, string>  $enrollmentStatuses
     * @return array<int, string>
     */
    protected function row(Attendance $attendance, array $enrollmentStatuses): array
    {
        return [
            $attendance->student->student_id_number,
            $attendance->student->name,
            $attendance->date->format('Y-m-d'),
            $attendance->course->name,
            $enrollmentStatuses["{$attendance->student_id}:{$attendance->course_id}"] ?? '',
        ];
    }
}
