<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_computes_income_expenses_and_balance_per_month(): void
    {
        $director = User::factory()->director()->create();

        Payment::factory()->create(['amount' => 500, 'status' => 'paid', 'payment_date' => '2026-03-10']);
        Payment::factory()->create(['amount' => 300, 'status' => 'paid', 'payment_date' => '2026-03-20']);
        // Not counted: unpaid payment, and a payment in a different month.
        Payment::factory()->create(['amount' => 999, 'status' => 'pending', 'payment_date' => '2026-03-15']);
        Payment::factory()->create(['amount' => 999, 'status' => 'paid', 'payment_date' => '2026-04-01']);

        Expense::factory()->create(['amount' => 200, 'expense_date' => '2026-03-05', 'category' => 'fuel']);
        Expense::factory()->create(['amount' => 50, 'expense_date' => '2026-03-25', 'category' => 'internet']);
        // Not counted: expense in a different month/year.
        Expense::factory()->create(['amount' => 999, 'expense_date' => '2025-03-05', 'category' => 'fuel']);

        $response = $this->actingAs($director)->get('/finance?year=2026');

        $response->assertOk();
        $response->assertSee('March');
        // March: income 800, expenses 250, balance 550.
        $response->assertSeeInOrder(['March', '800.00', '250.00', '550.00']);
    }

    public function test_summary_shows_todays_automatically_deducted_balance(): void
    {
        $director = User::factory()->director()->create();

        Payment::factory()->create(['amount' => 1000, 'status' => 'paid', 'payment_date' => now()->toDateString()]);
        Expense::factory()->create(['amount' => 400, 'expense_date' => now()->toDateString(), 'category' => 'fuel']);
        // Not counted: paid yesterday, expensed yesterday.
        Payment::factory()->create(['amount' => 999, 'status' => 'paid', 'payment_date' => now()->subDay()->toDateString()]);
        Expense::factory()->create(['amount' => 999, 'expense_date' => now()->subDay()->toDateString(), 'category' => 'fuel']);

        $response = $this->actingAs($director)->get('/finance');

        $response->assertOk();
        // Today: income 1000, expenses 400, balance 600.
        $response->assertSeeInOrder(["Today's Balance", '1,000.00', '400.00', '600.00']);
    }

    public function test_summary_defaults_to_the_current_year(): void
    {
        $director = User::factory()->director()->create();
        Payment::factory()->create(['amount' => 123, 'status' => 'paid', 'payment_date' => now()->toDateString()]);

        $response = $this->actingAs($director)->get('/finance');

        $response->assertOk();
        $response->assertSee((string) now()->year);
    }

    public function test_director_can_export_the_summary_as_csv(): void
    {
        $director = User::factory()->director()->create();
        Payment::factory()->create(['amount' => 500, 'status' => 'paid', 'payment_date' => '2026-03-10']);
        Expense::factory()->create(['amount' => 200, 'expense_date' => '2026-03-05', 'category' => 'fuel']);

        $response = $this->actingAs($director)->get('/finance/export?year=2026');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('March', $content);
        $this->assertStringContainsString('500.00', $content);
        $this->assertStringContainsString('200.00', $content);
        $this->assertStringContainsString('Year Total', $content);
    }

    public function test_admin_cannot_export_the_finance_summary(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/finance/export');

        $response->assertForbidden();
    }
}
