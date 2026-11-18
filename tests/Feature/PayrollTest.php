<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Instructor;
use App\Models\PayrollRun;
use App\Models\PayrollSalary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_director_can_set_a_salary_for_a_staff_user(): void
    {
        $director = User::factory()->director()->create();
        $secretary = User::factory()->secretary()->create();

        $response = $this->actingAs($director)->post(route('payroll.salaries.update'), [
            'payee_type' => 'user',
            'payee_id' => $secretary->id,
            'monthly_amount' => 120000,
            'active' => '1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payroll_salaries', [
            'payee_type' => 'user',
            'payee_id' => $secretary->id,
            'monthly_amount' => 120000.00,
            'active' => true,
        ]);
    }

    public function test_a_director_can_set_a_salary_for_an_instructor(): void
    {
        $director = User::factory()->director()->create();
        $instructor = Instructor::factory()->create();

        $response = $this->actingAs($director)->post(route('payroll.salaries.update'), [
            'payee_type' => 'instructor',
            'payee_id' => $instructor->id,
            'monthly_amount' => 90000,
            'active' => '1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payroll_salaries', [
            'payee_type' => 'instructor',
            'payee_id' => $instructor->id,
            'monthly_amount' => 90000.00,
        ]);
    }

    public function test_generating_a_payroll_run_snapshots_every_active_salary(): void
    {
        $director = User::factory()->director()->create();
        $secretary = User::factory()->secretary()->create(['name' => 'Active Secretary']);
        $inactiveInstructor = Instructor::factory()->create(['name' => 'Inactive Instructor']);

        PayrollSalary::create(['payee_type' => 'user', 'payee_id' => $secretary->id, 'monthly_amount' => 100000, 'active' => true]);
        PayrollSalary::create(['payee_type' => 'instructor', 'payee_id' => $inactiveInstructor->id, 'monthly_amount' => 70000, 'active' => false]);

        $response = $this->actingAs($director)->post(route('payroll.runs.store'), ['month' => '2026-11']);

        $run = PayrollRun::first();
        $response->assertRedirect(route('payroll.runs.show', $run));
        $this->assertSame('draft', $run->status);
        $this->assertCount(1, $run->items);
        $this->assertSame('Active Secretary', $run->items->first()->payee_name);
        $this->assertEquals(100000.00, $run->totalAmount());
    }

    public function test_generating_a_duplicate_run_for_the_same_month_is_rejected(): void
    {
        $director = User::factory()->director()->create();
        $secretary = User::factory()->secretary()->create();
        PayrollSalary::create(['payee_type' => 'user', 'payee_id' => $secretary->id, 'monthly_amount' => 100000, 'active' => true]);

        $this->actingAs($director)->post(route('payroll.runs.store'), ['month' => '2026-11']);
        $response = $this->actingAs($director)->post(route('payroll.runs.store'), ['month' => '2026-11']);

        $response->assertSessionHasErrors('month');
        $this->assertSame(1, PayrollRun::count());
    }

    public function test_processing_a_payroll_run_creates_a_paid_expense_per_person(): void
    {
        $director = User::factory()->director()->create();
        $secretary = User::factory()->secretary()->create(['name' => 'Bola Adewale']);
        PayrollSalary::create(['payee_type' => 'user', 'payee_id' => $secretary->id, 'monthly_amount' => 100000, 'active' => true]);

        $this->actingAs($director)->post(route('payroll.runs.store'), ['month' => '2026-11']);
        $run = PayrollRun::first();

        $response = $this->actingAs($director)->post(route('payroll.runs.pay', $run));

        $response->assertRedirect(route('payroll.runs.show', $run));
        $run->refresh();
        $this->assertSame('paid', $run->status);
        $this->assertSame($director->id, $run->processed_by);

        $item = $run->items->first();
        $this->assertNotNull($item->expense_id);

        $expense = Expense::find($item->expense_id);
        $this->assertSame('salary', $expense->category);
        $this->assertSame('paid', $expense->status);
        $this->assertEquals(100000.00, $expense->amount);
        $this->assertStringContainsString('Bola Adewale', $expense->description);
    }

    public function test_a_paid_payroll_run_cannot_be_processed_again(): void
    {
        $director = User::factory()->director()->create();
        $secretary = User::factory()->secretary()->create();
        PayrollSalary::create(['payee_type' => 'user', 'payee_id' => $secretary->id, 'monthly_amount' => 100000, 'active' => true]);
        $this->actingAs($director)->post(route('payroll.runs.store'), ['month' => '2026-11']);
        $run = PayrollRun::first();
        $this->actingAs($director)->post(route('payroll.runs.pay', $run));

        $response = $this->actingAs($director)->post(route('payroll.runs.pay', $run));

        $response->assertNotFound();
        $this->assertSame(1, Expense::count());
    }

    public function test_a_processed_payroll_run_shows_up_in_the_general_ledger(): void
    {
        $director = User::factory()->director()->create();
        $secretary = User::factory()->secretary()->create();
        PayrollSalary::create(['payee_type' => 'user', 'payee_id' => $secretary->id, 'monthly_amount' => 100000, 'active' => true]);
        $this->actingAs($director)->post(route('payroll.runs.store'), ['month' => '2026-11']);
        $run = PayrollRun::first();
        $this->actingAs($director)->post(route('payroll.runs.pay', $run));

        $entries = \App\Services\GeneralLedgerService::entries(now()->year);
        $trialBalance = \App\Services\GeneralLedgerService::trialBalance($entries);

        $this->assertTrue($trialBalance['balanced']);
        $this->assertEquals(100000.00, $trialBalance['accounts']['Salary']['debit']);
    }

    public function test_a_secretary_cannot_access_payroll(): void
    {
        $secretary = User::factory()->secretary()->create();

        $this->actingAs($secretary)->get(route('payroll.index'))->assertForbidden();
        $this->actingAs($secretary)->post(route('payroll.salaries.update'), [])->assertForbidden();
    }

    public function test_an_accountant_cannot_access_payroll(): void
    {
        $accountant = User::factory()->accountant()->create();

        $this->actingAs($accountant)->get(route('payroll.index'))->assertForbidden();
    }

    public function test_a_director_can_download_a_payslip_pdf(): void
    {
        $director = User::factory()->director()->create();
        $secretary = User::factory()->secretary()->create();
        PayrollSalary::create(['payee_type' => 'user', 'payee_id' => $secretary->id, 'monthly_amount' => 100000, 'active' => true]);
        $this->actingAs($director)->post(route('payroll.runs.store'), ['month' => '2026-11']);
        $run = PayrollRun::first();
        $this->actingAs($director)->post(route('payroll.runs.pay', $run));
        $item = $run->items->first();

        $response = $this->actingAs($director)->get(route('payroll.runs.payslip', [$run, $item]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }
}
