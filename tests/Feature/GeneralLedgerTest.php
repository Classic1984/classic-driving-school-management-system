<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Services\GeneralLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeneralLedgerTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_paid_payment_and_paid_expense_becomes_a_balanced_ledger_entry(): void
    {
        $student = Student::factory()->create();
        Payment::factory()->create([
            'student_id' => $student->id,
            'amount' => 50000,
            'status' => 'paid',
            'payment_date' => '2026-03-10',
        ]);
        Expense::factory()->create([
            'category' => 'fuel',
            'amount' => 15000,
            'status' => 'paid',
            'expense_date' => '2026-03-12',
        ]);
        Expense::factory()->create([
            'category' => 'investment_return',
            'amount' => 20000,
            'status' => 'paid',
            'expense_date' => '2026-03-15',
        ]);
        // A pending expense has no money movement yet, so it must not
        // appear in the ledger at all.
        Expense::factory()->pending()->create([
            'category' => 'office_rent',
            'amount' => 999999,
            'expense_date' => '2026-03-20',
        ]);

        $entries = GeneralLedgerService::entries(2026);
        $trialBalance = GeneralLedgerService::trialBalance($entries);

        $this->assertCount(3, $entries);
        $this->assertTrue($trialBalance['balanced']);
        $this->assertEquals($trialBalance['total_debit'], $trialBalance['total_credit']);

        $accounts = $trialBalance['accounts'];
        $this->assertEquals(70000.0, $accounts['Cash/Bank']['debit']);
        $this->assertEquals(15000.0, $accounts['Cash/Bank']['credit']);
        $this->assertEquals(50000.0, $accounts['Student Fees Income']['credit']);
        $this->assertEquals(15000.0, $accounts['Fuel']['debit']);
        $this->assertEquals(20000.0, $accounts['Investment/Saving Return']['credit']);
        $this->assertArrayNotHasKey('Office Rent', $accounts);
    }

    public function test_a_years_ledger_has_no_opening_balance_when_nothing_predates_it(): void
    {
        $student = Student::factory()->create();
        Payment::factory()->create(['student_id' => $student->id, 'amount' => 50000, 'status' => 'paid', 'payment_date' => '2026-03-10']);

        $entries = GeneralLedgerService::entries(2026);

        $this->assertCount(1, $entries);
        $this->assertSame('payment', $entries->first()['type']);
    }

    public function test_a_later_years_ledger_carries_forward_the_prior_years_net_cash_position(): void
    {
        $student = Student::factory()->create();
        // 2025: ₦200,000 in, ₦80,000 out — net ₦120,000 should carry into 2026.
        Payment::factory()->create(['student_id' => $student->id, 'amount' => 200000, 'status' => 'paid', 'payment_date' => '2025-06-01']);
        Expense::factory()->create(['category' => 'fuel', 'amount' => 80000, 'status' => 'paid', 'expense_date' => '2025-07-01']);
        // 2026: a fresh ₦30,000 payment.
        Payment::factory()->create(['student_id' => $student->id, 'amount' => 30000, 'status' => 'paid', 'payment_date' => '2026-02-01']);

        $this->assertEquals(120000.0, GeneralLedgerService::openingCashBalance(2026));

        $entries = GeneralLedgerService::entries(2026);
        $opening = $entries->firstWhere('type', 'opening_balance');

        $this->assertNotNull($opening);
        $this->assertSame('opening_balance', $entries->first()['type']);
        $this->assertEquals(120000.0, $opening['amount']);
        $this->assertSame('Cash/Bank', $opening['debit_account']);
        $this->assertSame('Retained Earnings', $opening['credit_account']);

        $trialBalance = GeneralLedgerService::trialBalance($entries);
        $this->assertTrue($trialBalance['balanced']);
        $this->assertEquals(150000.0, $trialBalance['accounts']['Cash/Bank']['debit']);

        // 2025's own ledger is untouched - no opening balance of its own.
        $this->assertCount(2, GeneralLedgerService::entries(2025));
    }

    public function test_opening_balance_can_be_negative_if_the_school_ran_a_cumulative_deficit(): void
    {
        Expense::factory()->create(['category' => 'fuel', 'amount' => 90000, 'status' => 'paid', 'expense_date' => '2025-07-01']);

        $this->assertEquals(-90000.0, GeneralLedgerService::openingCashBalance(2026));

        $entries = GeneralLedgerService::entries(2026);
        $opening = $entries->firstWhere('type', 'opening_balance');

        $this->assertSame('Retained Earnings', $opening['debit_account']);
        $this->assertSame('Cash/Bank', $opening['credit_account']);

        $this->assertTrue(GeneralLedgerService::trialBalance($entries)['balanced']);
    }

    public function test_a_director_can_view_the_general_ledger(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        Payment::factory()->create(['student_id' => $student->id, 'amount' => 30000, 'status' => 'paid', 'payment_date' => now()]);

        $response = $this->actingAs($director)->get(route('general-ledger.index'));

        $response->assertOk();
        $response->assertSee('Trial Balance');
        $response->assertSee('30,000.00');
    }

    public function test_an_accountant_cannot_view_the_general_ledger(): void
    {
        $accountant = User::factory()->accountant()->create();

        $this->actingAs($accountant)->get(route('general-ledger.index'))->assertForbidden();
    }

    public function test_a_secretary_cannot_view_the_general_ledger(): void
    {
        $secretary = User::factory()->secretary()->create();

        $this->actingAs($secretary)->get(route('general-ledger.index'))->assertForbidden();
    }

    public function test_a_director_can_download_the_general_ledger_as_a_pdf(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        Payment::factory()->create(['student_id' => $student->id, 'amount' => 30000, 'status' => 'paid', 'payment_date' => now()]);

        $response = $this->actingAs($director)->get(route('general-ledger.export-pdf'));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_a_director_can_export_the_general_ledger_as_csv(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        Payment::factory()->create(['student_id' => $student->id, 'amount' => 30000, 'status' => 'paid', 'payment_date' => now()]);

        $response = $this->actingAs($director)->get(route('general-ledger.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
