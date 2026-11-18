<?php

namespace Tests\Feature;

use App\Models\BankReconciliation;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankReconciliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_director_can_start_a_reconciliation(): void
    {
        $director = User::factory()->director()->create();

        $response = $this->actingAs($director)->post(route('bank-reconciliation.store'), [
            'period_start' => '2026-11-01',
            'period_end' => '2026-11-30',
            'opening_balance' => 100000,
            'closing_balance' => 150000,
        ]);

        $reconciliation = BankReconciliation::first();
        $response->assertRedirect(route('bank-reconciliation.show', $reconciliation));
        $this->assertSame('in_progress', $reconciliation->status);
        $this->assertSame($director->id, $reconciliation->created_by);
    }

    public function test_adding_a_line_that_matches_a_paid_payment_auto_matches(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        Payment::factory()->create(['student_id' => $student->id, 'amount' => 50000, 'status' => 'paid', 'payment_date' => '2026-11-10']);

        $reconciliation = BankReconciliation::create(['period_start' => '2026-11-01', 'period_end' => '2026-11-30', 'status' => 'in_progress', 'created_by' => $director->id]);

        $response = $this->actingAs($director)->post(route('bank-reconciliation.lines.store', $reconciliation), [
            'line_date' => '2026-11-10',
            'description' => 'Bank deposit',
            'amount' => 50000,
        ]);

        $response->assertRedirect();
        $line = $reconciliation->lines()->first();
        $this->assertSame('matched', $line->status);
        $this->assertSame('payment', $line->matched_type);
    }

    public function test_adding_a_line_with_no_matching_system_entry_stays_unmatched(): void
    {
        $director = User::factory()->director()->create();
        $reconciliation = BankReconciliation::create(['period_start' => '2026-11-01', 'period_end' => '2026-11-30', 'status' => 'in_progress', 'created_by' => $director->id]);

        $this->actingAs($director)->post(route('bank-reconciliation.lines.store', $reconciliation), [
            'line_date' => '2026-11-10',
            'description' => 'Unexplained deposit',
            'amount' => 777777,
        ]);

        $line = $reconciliation->lines()->first();
        $this->assertSame('unmatched', $line->status);
    }

    public function test_two_same_amount_candidates_are_left_for_manual_matching(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        Payment::factory()->create(['student_id' => $student->id, 'amount' => 30000, 'status' => 'paid', 'payment_date' => '2026-11-05']);
        Payment::factory()->create(['student_id' => $student->id, 'amount' => 30000, 'status' => 'paid', 'payment_date' => '2026-11-06']);

        $reconciliation = BankReconciliation::create(['period_start' => '2026-11-01', 'period_end' => '2026-11-30', 'status' => 'in_progress', 'created_by' => $director->id]);

        $this->actingAs($director)->post(route('bank-reconciliation.lines.store', $reconciliation), [
            'line_date' => '2026-11-05',
            'description' => 'Bank deposit',
            'amount' => 30000,
        ]);

        $line = $reconciliation->lines()->first();
        $this->assertSame('unmatched', $line->status);
    }

    public function test_a_director_can_manually_match_a_line(): void
    {
        $director = User::factory()->director()->create();
        $expense = Expense::factory()->create(['category' => 'fuel', 'amount' => 15000, 'status' => 'paid', 'expense_date' => '2026-11-12']);

        $reconciliation = BankReconciliation::create(['period_start' => '2026-11-01', 'period_end' => '2026-11-30', 'status' => 'in_progress', 'created_by' => $director->id]);
        $line = $reconciliation->lines()->create(['line_date' => '2026-11-12', 'description' => 'Card payment', 'amount' => -15000]);

        $response = $this->actingAs($director)->post(route('bank-reconciliation.lines.match', [$reconciliation, $line]), [
            'source' => "expense|{$expense->id}",
        ]);

        $response->assertRedirect();
        $line->refresh();
        $this->assertSame('matched', $line->status);
        $this->assertSame('expense', $line->matched_type);
        $this->assertSame($expense->id, $line->matched_id);
    }

    public function test_a_director_can_unmatch_a_line(): void
    {
        $director = User::factory()->director()->create();
        $reconciliation = BankReconciliation::create(['period_start' => '2026-11-01', 'period_end' => '2026-11-30', 'status' => 'in_progress', 'created_by' => $director->id]);
        $line = $reconciliation->lines()->create(['line_date' => '2026-11-12', 'description' => 'x', 'amount' => 1000, 'status' => 'matched', 'matched_type' => 'expense', 'matched_id' => 999]);

        $this->actingAs($director)->post(route('bank-reconciliation.lines.unmatch', [$reconciliation, $line]));

        $line->refresh();
        $this->assertSame('unmatched', $line->status);
        $this->assertNull($line->matched_type);
    }

    public function test_a_director_can_ignore_a_line(): void
    {
        $director = User::factory()->director()->create();
        $reconciliation = BankReconciliation::create(['period_start' => '2026-11-01', 'period_end' => '2026-11-30', 'status' => 'in_progress', 'created_by' => $director->id]);
        $line = $reconciliation->lines()->create(['line_date' => '2026-11-12', 'description' => 'Bank fee', 'amount' => -500]);

        $this->actingAs($director)->post(route('bank-reconciliation.lines.ignore', [$reconciliation, $line]));

        $this->assertSame('ignored', $line->fresh()->status);
    }

    public function test_uploading_a_csv_creates_and_matches_lines(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        Payment::factory()->create(['student_id' => $student->id, 'amount' => 45000, 'status' => 'paid', 'payment_date' => '2026-11-08']);

        $reconciliation = BankReconciliation::create(['period_start' => '2026-11-01', 'period_end' => '2026-11-30', 'status' => 'in_progress', 'created_by' => $director->id]);

        $csv = "date,description,amount\n2026-11-08,Deposit,45000\n2026-11-09,Unrelated,999\n";
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('statement.csv', $csv);

        $response = $this->actingAs($director)->post(route('bank-reconciliation.upload', $reconciliation), [
            'statement' => $file,
        ]);

        $response->assertRedirect();
        $this->assertSame(2, $reconciliation->lines()->count());
        $this->assertSame(1, $reconciliation->lines()->where('status', 'matched')->count());
    }

    public function test_completing_a_reconciliation_locks_further_changes(): void
    {
        $director = User::factory()->director()->create();
        $reconciliation = BankReconciliation::create(['period_start' => '2026-11-01', 'period_end' => '2026-11-30', 'status' => 'in_progress', 'created_by' => $director->id]);

        $response = $this->actingAs($director)->post(route('bank-reconciliation.complete', $reconciliation));

        $response->assertRedirect(route('bank-reconciliation.show', $reconciliation));
        $reconciliation->refresh();
        $this->assertSame('completed', $reconciliation->status);
        $this->assertSame($director->id, $reconciliation->completed_by);

        $this->actingAs($director)->post(route('bank-reconciliation.lines.store', $reconciliation), [
            'line_date' => '2026-11-15', 'description' => 'x', 'amount' => 100,
        ])->assertNotFound();
    }

    public function test_a_secretary_cannot_access_bank_reconciliation(): void
    {
        $secretary = User::factory()->secretary()->create();

        $this->actingAs($secretary)->get(route('bank-reconciliation.index'))->assertForbidden();
    }

    public function test_an_accountant_cannot_access_bank_reconciliation(): void
    {
        $accountant = User::factory()->accountant()->create();

        $this->actingAs($accountant)->get(route('bank-reconciliation.index'))->assertForbidden();
    }
}
