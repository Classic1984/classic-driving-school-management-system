<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\User;
use App\Notifications\ExpenseApprovedNotification;
use App\Notifications\ExpenseRejectedNotification;
use App\Notifications\ExpenseRequestedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ExpenseApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_accountant_can_view_and_submit_expenses(): void
    {
        $accountant = User::factory()->accountant()->create();

        $this->actingAs($accountant)->get('/expenses')->assertOk();
        $this->actingAs($accountant)->get('/expenses/create')->assertOk();
    }

    public function test_a_secretary_cannot_access_expense_routes(): void
    {
        $secretary = User::factory()->secretary()->create();

        $this->actingAs($secretary)->get('/expenses')->assertForbidden();
        $this->actingAs($secretary)->get('/expenses/create')->assertForbidden();
        $this->actingAs($secretary)->post('/expenses', [])->assertForbidden();
    }

    public function test_an_accountants_submitted_expense_is_pending_and_notifies_every_director(): void
    {
        Notification::fake();

        $accountant = User::factory()->accountant()->create();
        $director = User::factory()->director()->create();

        $this->actingAs($accountant)->post('/expenses', [
            'category' => 'fuel',
            'amount' => 5000,
            'expense_date' => now()->toDateString(),
        ])->assertRedirect(route('expenses.index'));

        $expense = Expense::sole();
        $this->assertSame('pending', $expense->status);
        $this->assertSame($accountant->id, $expense->requested_by);
        $this->assertNull($expense->paid_at);

        Notification::assertSentTo($director, ExpenseRequestedNotification::class);
    }

    public function test_a_directors_own_expense_is_paid_immediately(): void
    {
        $director = User::factory()->director()->create();

        $this->actingAs($director)->post('/expenses', [
            'category' => 'fuel',
            'amount' => 5000,
            'expense_date' => now()->toDateString(),
        ]);

        $expense = Expense::sole();
        $this->assertSame('paid', $expense->status);
        $this->assertNull($expense->requested_by);
        $this->assertNotNull($expense->paid_at);
    }

    public function test_a_pending_expense_does_not_count_toward_the_expense_totals(): void
    {
        $director = User::factory()->director()->create();
        Expense::factory()->create(['amount' => 10000, 'expense_date' => now()->toDateString()]);
        Expense::factory()->pending()->create(['amount' => 99999, 'expense_date' => now()->toDateString()]);

        $response = $this->actingAs($director)->get('/expenses');

        $response->assertOk();
        // The pending expense's own amount legitimately still shows in the
        // raw list below (so staff can see what's pending) - only the
        // combined total must never include it.
        $response->assertSee('10,000.00');
        $response->assertDontSee('109,999.00');
    }

    public function test_a_rejected_expense_never_counts_toward_the_expense_totals(): void
    {
        $director = User::factory()->director()->create();
        Expense::factory()->create(['amount' => 10000, 'expense_date' => now()->toDateString()]);
        Expense::factory()->rejected()->create(['amount' => 50000, 'expense_date' => now()->toDateString()]);

        $response = $this->actingAs($director)->get('/expenses');

        $response->assertOk();
        $response->assertSee('10,000.00');
        $response->assertDontSee('60,000.00');
    }

    public function test_a_director_can_approve_a_pending_expense_and_the_accountant_is_notified(): void
    {
        Notification::fake();

        $director = User::factory()->director()->create();
        $accountant = User::factory()->accountant()->create();
        $expense = Expense::factory()->pending()->create(['requested_by' => $accountant->id]);

        $this->actingAs($director)
            ->patch(route('expenses.approve', $expense))
            ->assertRedirect(route('approvals.index'));

        $expense->refresh();
        $this->assertSame('approved', $expense->status);
        $this->assertSame($director->id, $expense->resolved_by);
        $this->assertNotNull($expense->resolved_at);
        // Approving only clears it to be paid - it's not a real outflow yet.
        $this->assertNull($expense->paid_at);

        Notification::assertSentTo($accountant, ExpenseApprovedNotification::class);
    }

    public function test_a_director_can_reject_a_pending_expense_with_a_reason(): void
    {
        Notification::fake();

        $director = User::factory()->director()->create();
        $accountant = User::factory()->accountant()->create();
        $expense = Expense::factory()->pending()->create(['requested_by' => $accountant->id]);

        $this->actingAs($director)
            ->patch(route('expenses.reject', $expense), ['rejection_reason' => 'Not a legitimate business expense.'])
            ->assertRedirect(route('approvals.index'));

        $expense->refresh();
        $this->assertSame('rejected', $expense->status);
        $this->assertSame('Not a legitimate business expense.', $expense->rejection_reason);

        Notification::assertSentTo($accountant, ExpenseRejectedNotification::class);
    }

    public function test_an_accountant_cannot_approve_or_reject_an_expense(): void
    {
        $accountant = User::factory()->accountant()->create();
        $expense = Expense::factory()->pending()->create();

        $this->actingAs($accountant)->patch(route('expenses.approve', $expense))->assertForbidden();
        $this->actingAs($accountant)->patch(route('expenses.reject', $expense))->assertForbidden();

        $this->assertSame('pending', $expense->fresh()->status);
    }

    public function test_approving_an_expense_that_is_not_pending_fails(): void
    {
        $director = User::factory()->director()->create();
        $expense = Expense::factory()->create();

        $this->actingAs($director)->patch(route('expenses.approve', $expense))->assertNotFound();
    }

    public function test_an_accountant_can_mark_an_approved_expense_as_paid_and_it_then_counts_toward_totals(): void
    {
        $accountant = User::factory()->accountant()->create();
        $expense = Expense::factory()->approved()->create(['amount' => 7000, 'expense_date' => now()->toDateString()]);

        $this->actingAs($accountant)
            ->patch(route('expenses.pay', $expense))
            ->assertRedirect(route('expenses.index'));

        $expense->refresh();
        $this->assertSame('paid', $expense->status);
        $this->assertNotNull($expense->paid_at);

        $response = $this->actingAs($accountant)->get('/expenses');
        $response->assertSee('7,000.00');
    }

    public function test_paying_an_expense_that_is_not_approved_fails(): void
    {
        $director = User::factory()->director()->create();
        $expense = Expense::factory()->pending()->create();

        $this->actingAs($director)->patch(route('expenses.pay', $expense))->assertNotFound();
    }

    public function test_a_pending_expense_request_appears_in_the_approval_centre(): void
    {
        $director = User::factory()->director()->create();
        $accountant = User::factory()->accountant()->create(['name' => 'Chidi Okafor']);
        Expense::factory()->pending()->create(['requested_by' => $accountant->id, 'category' => 'fuel', 'amount' => 8500]);

        $response = $this->actingAs($director)->get('/approvals');

        $response->assertOk();
        $response->assertSee('Expense Request');
        $response->assertSee('8,500.00');
        $response->assertSee('Chidi Okafor');
    }

    public function test_the_dashboards_pending_approvals_count_includes_pending_expenses(): void
    {
        $director = User::factory()->director()->create();
        Expense::factory()->pending()->create();

        $response = $this->actingAs($director)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Pending Approvals');
        $response->assertSeeInOrder(['Pending Approvals', '1']);
    }
}
