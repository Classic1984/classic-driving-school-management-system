<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Student;
use App\Services\StudentChargeResolver;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentReportController extends Controller
{
    /**
     * Combines the three financial reports the Director needs: the daily
     * payment breakdown, all-time revenue by service, and every student
     * currently carrying a balance.
     */
    public function index(Request $request): View
    {
        $date = $this->date($request);
        $daily = $this->dailyReport($date);
        $revenueByService = $this->revenueByService();
        $outstanding = $this->outstandingReport();

        return view('payment-reports.index', compact('date', 'daily', 'revenueByService', 'outstanding'));
    }

    /**
     * Download a CSV of the outstanding balances report.
     */
    public function export(): StreamedResponse
    {
        $outstanding = $this->outstandingReport();

        return response()->streamDownload(function () use ($outstanding) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Student', 'Service', 'Total', 'Paid', 'Balance']);

            foreach ($outstanding as $row) {
                fputcsv($handle, [
                    $row['student'],
                    $row['label'],
                    number_format($row['price'], 2, '.', ''),
                    number_format($row['paid'], 2, '.', ''),
                    number_format($row['balance'], 2, '.', ''),
                ]);
            }

            fclose($handle);
        }, 'outstanding-balances-report.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Download a printable PDF combining all three reports.
     */
    public function exportPdf(Request $request): Response
    {
        $date = $this->date($request);
        $daily = $this->dailyReport($date);
        $revenueByService = $this->revenueByService();
        $outstanding = $this->outstandingReport();

        $pdf = Pdf::loadView('payment-reports.pdf', compact('date', 'daily', 'revenueByService', 'outstanding'));

        return $pdf->download('financial-reports.pdf');
    }

    protected function date(Request $request): string
    {
        $date = $request->query('date');

        if ($date && \DateTime::createFromFormat('Y-m-d', $date) !== false) {
            return $date;
        }

        return today()->toDateString();
    }

    /**
     * @return array{count: int, cash: float, bank_transfer: float, card: float, mobile_money: float, services: Collection<int, string>}
     */
    protected function dailyReport(string $date): array
    {
        $payments = Payment::where('status', 'paid')
            ->whereDate('payment_date', $date)
            ->with(['allocations.enrollment.course', 'allocations.studentService.service'])
            ->get();

        return [
            'count' => $payments->count(),
            'cash' => (float) $payments->where('payment_method', 'cash')->sum('amount'),
            'bank_transfer' => (float) $payments->where('payment_method', 'bank_transfer')->sum('amount'),
            'card' => (float) $payments->where('payment_method', 'card')->sum('amount'),
            'mobile_money' => (float) $payments->where('payment_method', 'mobile_money')->sum('amount'),
            'services' => $payments->flatMap(fn (Payment $payment) => $payment->allocations)
                ->map(fn (PaymentAllocation $allocation) => $allocation->label())
                ->unique()
                ->values(),
        ];
    }

    /**
     * All-time revenue collected, grouped by service.
     *
     * @return Collection<string, float>
     */
    protected function revenueByService(): Collection
    {
        return PaymentAllocation::whereHas('payment', fn ($query) => $query->where('status', 'paid'))
            ->with(['studentService.service'])
            ->get()
            ->groupBy(fn (PaymentAllocation $allocation) => match ($allocation->allocation_type) {
                'training' => 'Training',
                'online_certificate' => 'Online Certificate',
                'student_certificate' => 'Student Certificate',
                'service' => $allocation->studentService->service->name,
                default => 'Other',
            })
            ->map(fn (Collection $group) => (float) $group->sum('amount'))
            ->sortDesc();
    }

    /**
     * Every open charge for every student, for the Outstanding report.
     *
     * @return Collection<int, array{student: string, label: string, price: float, paid: float, balance: float}>
     */
    protected function outstandingReport(): Collection
    {
        return Student::orderBy('name')->get()
            ->flatMap(fn (Student $student) => StudentChargeResolver::outstandingCharges($student)
                ->map(fn (array $charge) => [
                    'student' => $student->name,
                    'student_id' => $student->id,
                    'label' => $charge['label'],
                    'price' => $charge['price'],
                    'paid' => $charge['paid'],
                    'balance' => $charge['balance'],
                ]));
    }
}
