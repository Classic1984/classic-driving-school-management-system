<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Expense;
use App\Models\Instructor;
use App\Models\PayrollRun;
use App\Models\PayrollRunItem;
use App\Models\PayrollSalary;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PayrollController extends Controller
{
    /**
     * The payroll roster - every staff User and active Instructor, with
     * whatever salary (if any) is currently set for them - plus every
     * past payroll run.
     */
    public function index(): View
    {
        $salaries = PayrollSalary::with('payee')->get()->keyBy(fn (PayrollSalary $salary) => $salary->payee_type.':'.$salary->payee_id);

        $roster = User::orderBy('name')->get()
            ->map(fn (User $user) => $this->rosterRow($user, 'user', $salaries))
            ->concat(
                Instructor::where('status', 'active')->orderBy('name')->get()
                    ->map(fn (Instructor $instructor) => $this->rosterRow($instructor, 'instructor', $salaries))
            );

        $runs = PayrollRun::withCount('items')->latest('month')->get();

        return view('payroll.index', compact('roster', 'runs'));
    }

    /**
     * @return array{type: string, id: int, name: string, role_or_status: string, monthly_amount: float, active: bool}
     */
    protected function rosterRow(User|Instructor $payee, string $type, Collection $salaries): array
    {
        $salary = $salaries->get($type.':'.$payee->id);

        return [
            'type' => $type,
            'id' => $payee->id,
            'name' => $payee->name,
            'role_or_status' => $type === 'user' ? ucfirst($payee->role) : 'Instructor',
            'monthly_amount' => $salary ? (float) $salary->monthly_amount : 0.0,
            'active' => $salary?->active ?? false,
        ];
    }

    /**
     * Set or update the monthly salary for one person on the roster.
     * Director-only, same as every other money figure in this module -
     * this never itself creates an Expense, it just sets what a future
     * payroll run will pay this person.
     */
    public function updateSalary(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payee_type' => ['required', Rule::in(['user', 'instructor'])],
            'payee_id' => ['required', 'integer'],
            'monthly_amount' => ['required', 'numeric', 'min:0'],
            'active' => ['sometimes', 'boolean'],
        ]);

        $payeeClass = $validated['payee_type'] === 'user' ? User::class : Instructor::class;
        $payee = $payeeClass::findOrFail($validated['payee_id']);

        PayrollSalary::updateOrCreate(
            ['payee_type' => $validated['payee_type'], 'payee_id' => $payee->id],
            [
                'monthly_amount' => $validated['monthly_amount'],
                'active' => $request->boolean('active'),
                'set_by' => $request->user()->id,
            ]
        );

        ActivityLog::record("Set {$payee->name}'s monthly salary to ₦".number_format((float) $validated['monthly_amount'], 2));

        return back()->with('status', 'salary-updated');
    }

    /**
     * Generate a draft payroll run for a given month: one line per
     * currently active salary, snapshotting the name and amount as they
     * stand right now. Generating a run moves no money - only pay()
     * does that.
     */
    public function storeRun(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $month = $validated['month'].'-01';

        if (PayrollRun::where('month', $month)->exists()) {
            return back()->withErrors(['month' => 'A payroll run for that month already exists.']);
        }

        $activeSalaries = PayrollSalary::with('payee')->where('active', true)->get();

        abort_if($activeSalaries->isEmpty(), 422, 'No one is on the active salary roster yet.');

        $run = DB::transaction(function () use ($month, $activeSalaries) {
            $run = PayrollRun::create(['month' => $month, 'status' => 'draft']);

            foreach ($activeSalaries as $salary) {
                $run->items()->create([
                    'payee_type' => $salary->payee_type,
                    'payee_id' => $salary->payee_id,
                    'payee_name' => $salary->payee->name,
                    'amount' => $salary->monthly_amount,
                ]);
            }

            return $run;
        });

        ActivityLog::record('Generated a draft payroll run for '.Carbon::parse($month)->format('F Y')." ({$activeSalaries->count()} people)");

        return redirect()->route('payroll.runs.show', $run)->with('status', 'payroll-run-created');
    }

    public function showRun(PayrollRun $payrollRun): View
    {
        $payrollRun->load('items', 'processedBy');

        return view('payroll.show', compact('payrollRun'));
    }

    /**
     * Process a draft run: the moment it actually moves money. Creates
     * one paid Expense (category: salary) per line, exactly like a
     * Director recording any other expense directly - so every payroll
     * line immediately shows up in Finance Summary and the General
     * Ledger with no extra work needed there.
     */
    public function payRun(PayrollRun $payrollRun): RedirectResponse
    {
        abort_unless($payrollRun->isDraft(), 404);

        DB::transaction(function () use ($payrollRun) {
            $monthLabel = Carbon::parse($payrollRun->month)->format('F Y');

            foreach ($payrollRun->items as $item) {
                $expense = Expense::create([
                    'category' => 'salary',
                    'amount' => $item->amount,
                    'expense_date' => now(),
                    'description' => "Salary — {$item->payee_name} — {$monthLabel}",
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                $item->update(['expense_id' => $expense->id]);
            }

            $payrollRun->update([
                'status' => 'paid',
                'processed_by' => request()->user()->id,
                'processed_at' => now(),
            ]);
        });

        ActivityLog::record('Processed payroll for '.Carbon::parse($payrollRun->month)->format('F Y').' — ₦'.number_format($payrollRun->fresh('items')->totalAmount(), 2).' total');

        return redirect()->route('payroll.runs.show', $payrollRun)->with('status', 'payroll-run-paid');
    }

    /**
     * A printable payslip for one line of a payroll run.
     */
    public function payslipPdf(PayrollRun $payrollRun, PayrollRunItem $payrollRunItem): Response
    {
        abort_unless($payrollRunItem->payroll_run_id === $payrollRun->id, 404);

        $pdf = Pdf::loadView('payroll.payslip-pdf', [
            'payrollRun' => $payrollRun,
            'item' => $payrollRunItem,
        ]);

        return $pdf->download("payslip-{$payrollRunItem->payee_name}-{$payrollRun->month->format('Y-m')}.pdf");
    }
}
