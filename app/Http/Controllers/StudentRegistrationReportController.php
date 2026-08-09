<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentRegistrationReportController extends Controller
{
    /**
     * Periods the dashboard's New Students cards link into.
     */
    protected const PERIODS = ['today', 'week', 'month', 'year'];

    protected const LABELS = [
        'today' => 'Today',
        'week' => 'This Week',
        'month' => 'This Month',
        'year' => 'This Year',
    ];

    /**
     * The click-through list of students who registered during the
     * selected period.
     */
    public function index(Request $request): View
    {
        $period = $this->period($request);

        $students = $this->query($period)->with('courses')->latest('enrollment_date')->get();
        $label = self::LABELS[$period];

        return view('student-registration-report.index', compact('students', 'period', 'label'));
    }

    /**
     * Download a CSV (Excel-compatible) export of the same list.
     */
    public function export(Request $request): StreamedResponse
    {
        $period = $this->period($request);
        $students = $this->query($period)->with('courses')->latest('enrollment_date')->get();

        return response()->streamDownload(function () use ($students) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Student ID', 'Student Name', 'Email', 'Phone', 'Course(s)', 'Registration Date', 'Status']);

            foreach ($students as $student) {
                fputcsv($handle, $this->row($student));
            }

            fclose($handle);
        }, "student-registration-report-{$period}.csv", ['Content-Type' => 'text/csv']);
    }

    /**
     * Download a printable PDF export of the same list.
     */
    public function exportPdf(Request $request): Response
    {
        $period = $this->period($request);
        $students = $this->query($period)->with('courses')->latest('enrollment_date')->get();
        $label = self::LABELS[$period];

        $pdf = Pdf::loadView('student-registration-report.pdf', compact('students', 'label'));

        return $pdf->download("student-registration-report-{$period}.pdf");
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

        return Student::whereDate('enrollment_date', '>=', $from->toDateString())
            ->whereDate('enrollment_date', '<=', $to->toDateString());
    }

    /**
     * @return array<int, string>
     */
    protected function row(Student $student): array
    {
        return [
            $student->student_id_number,
            $student->name,
            $student->email,
            $student->phone,
            $student->courses->pluck('name')->implode(', '),
            $student->enrollment_date->format('Y-m-d'),
            ucfirst($student->status),
        ];
    }
}
