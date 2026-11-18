<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Services\GeneralLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialStatementTest extends TestCase
{
    use RefreshDatabase;

    public function test_income_statement_separates_revenue_and_expenses_by_account(): void
    {
        $student = Student::factory()->create();
        Payment::factory()->create(['student_id' => $student->id, 'amount' => 100000, 'status' => 'paid', 'payment_date' => '2026-05-01']);
        Expense::factory()->create(['category' => 'fuel', 'amount' => 20000, 'status' => 'paid', 'expense_date' => '2026-05-02']);
        Expense::factory()->create(['category' => 'office_rent', 'amount' => 15000, 'status' => 'paid', 'expense_date' => '2026-05-03']);
        Expense::factory()->create(['category' => 'investment_return', 'amount' => 5000, 'status' => 'paid', 'expense_date' => '2026-05-04']);
        // A pending expense never shows up in a financial statement.
        Expense::factory()->pending()->create(['category' => 'salary', 'amount' => 999999, 'expense_date' => '2026-05-05']);

        $statement = GeneralLedgerService::incomeStatement(2026);

        $this->assertEquals(100000.0, $statement['revenue']['Student Fees Income']);
        $this->assertEquals(5000.0, $statement['revenue']['Investment/Saving Return']);
        $this->assertEquals(105000.0, $statement['total_revenue']);

        $this->assertEquals(20000.0, $statement['expenses']['Fuel']);
        $this->assertEquals(15000.0, $statement['expenses']['Office Rent']);
        $this->assertEquals(35000.0, $statement['total_expenses']);
        $this->assertArrayNotHasKey('Salary', $statement['expenses']);

        $this->assertEquals(70000.0, $statement['net_income']);
    }

    public function test_balance_sheet_balances_for_the_first_year_of_activity(): void
    {
        $student = Student::factory()->create();
        Payment::factory()->create(['student_id' => $student->id, 'amount' => 50000, 'status' => 'paid', 'payment_date' => '2026-03-01']);
        Expense::factory()->create(['category' => 'fuel', 'amount' => 10000, 'status' => 'paid', 'expense_date' => '2026-03-02']);

        $sheet = GeneralLedgerService::balanceSheet(2026);

        $this->assertTrue($sheet['balanced']);
        $this->assertEquals(40000.0, $sheet['total_assets']);
        $this->assertEquals(0.0, $sheet['total_liabilities']);
        $this->assertEquals(40000.0, $sheet['total_equity']);
        $this->assertEquals(0.0, $sheet['equity']['Retained Earnings (brought forward)']);
        $this->assertEquals(40000.0, $sheet['equity']['Net Income (2026)']);
    }

    public function test_balance_sheet_carries_the_prior_years_equity_forward_and_still_balances(): void
    {
        $student = Student::factory()->create();
        Payment::factory()->create(['student_id' => $student->id, 'amount' => 200000, 'status' => 'paid', 'payment_date' => '2025-06-01']);
        Expense::factory()->create(['category' => 'fuel', 'amount' => 80000, 'status' => 'paid', 'expense_date' => '2025-07-01']);
        Payment::factory()->create(['student_id' => $student->id, 'amount' => 30000, 'status' => 'paid', 'payment_date' => '2026-02-01']);

        $sheet = GeneralLedgerService::balanceSheet(2026);

        $this->assertTrue($sheet['balanced']);
        $this->assertEquals(150000.0, $sheet['total_assets']);
        $this->assertEquals(120000.0, $sheet['equity']['Retained Earnings (brought forward)']);
        $this->assertEquals(30000.0, $sheet['equity']['Net Income (2026)']);
        $this->assertEquals(150000.0, $sheet['total_equity']);
    }

    public function test_a_director_can_view_financial_statements(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        Payment::factory()->create(['student_id' => $student->id, 'amount' => 60000, 'status' => 'paid', 'payment_date' => now()]);

        $response = $this->actingAs($director)->get(route('financial-statements.index'));

        $response->assertOk();
        $response->assertSee('Income Statement');
        $response->assertSee('Balance Sheet');
        $response->assertSee('60,000.00');
    }

    public function test_a_secretary_cannot_view_financial_statements(): void
    {
        $secretary = User::factory()->secretary()->create();

        $this->actingAs($secretary)->get(route('financial-statements.index'))->assertForbidden();
    }

    public function test_an_accountant_cannot_view_financial_statements(): void
    {
        $accountant = User::factory()->accountant()->create();

        $this->actingAs($accountant)->get(route('financial-statements.index'))->assertForbidden();
    }

    public function test_a_director_can_download_financial_statements_as_a_pdf(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        Payment::factory()->create(['student_id' => $student->id, 'amount' => 60000, 'status' => 'paid', 'payment_date' => now()]);

        $response = $this->actingAs($director)->get(route('financial-statements.export-pdf'));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_a_director_can_export_financial_statements_as_csv(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        Payment::factory()->create(['student_id' => $student->id, 'amount' => 60000, 'status' => 'paid', 'payment_date' => now()]);

        $response = $this->actingAs($director)->get(route('financial-statements.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
