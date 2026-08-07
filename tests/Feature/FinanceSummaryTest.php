<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Student;
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

    public function test_summary_shows_a_revenue_trend_chart(): void
    {
        $director = User::factory()->director()->create();
        Payment::factory()->create(['amount' => 500, 'status' => 'paid', 'payment_date' => '2026-03-10']);
        Expense::factory()->create(['amount' => 200, 'expense_date' => '2026-03-05', 'category' => 'fuel']);

        $response = $this->actingAs($director)->get('/finance?year=2026');

        $response->assertOk();
        $response->assertSee('Revenue Trend');
        $response->assertSee('<svg', false);
        $response->assertSee('March Income: ₦500.00', false);
        $response->assertSee('March Expenses: ₦200.00', false);
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

    public function test_summary_shows_discounts_applied_during_the_year(): void
    {
        $director = User::factory()->director()->create();
        $secretary = User::factory()->secretary()->create();
        $student = Student::factory()->create(['name' => 'John Doe']);
        $course = Course::factory()->create(['name' => 'Manual Driving Basics', 'fee' => 95000]);
        $student->courses()->attach($course->id, [
            'enrolled_at' => '2026-03-01',
            'due_date' => '2026-03-05',
            'status' => 'active',
            'fee' => 85500,
            'original_fee' => 95000,
            'discount_percentage' => 10,
            'discount_amount' => 9500,
            'discount_reason' => 'promotional_offer',
            'discount_approved_by' => $secretary->id,
        ]);

        $response = $this->actingAs($director)->get('/finance?year=2026');

        $response->assertOk();
        $response->assertSee('Discounts');
        $response->assertSee('John Doe');
        $response->assertSee('9,500.00');
        $response->assertSee($secretary->name);
    }

    public function test_admin_cannot_export_the_finance_summary(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/finance/export');

        $response->assertForbidden();
    }

    public function test_director_can_download_the_summary_as_a_pdf(): void
    {
        $director = User::factory()->director()->create();
        Payment::factory()->create(['amount' => 500, 'status' => 'paid', 'payment_date' => '2026-03-10']);
        Expense::factory()->create(['amount' => 200, 'expense_date' => '2026-03-05', 'category' => 'fuel']);

        $response = $this->actingAs($director)->get('/finance/export-pdf?year=2026');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_admin_cannot_download_the_finance_summary_as_a_pdf(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/finance/export-pdf');

        $response->assertForbidden();
    }
}
