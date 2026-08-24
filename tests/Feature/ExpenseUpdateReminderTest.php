<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseUpdateReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/expenses/last-updated')->assertRedirect('/login');
    }

    public function test_non_directors_are_forbidden(): void
    {
        $admin = User::factory()->admin()->create();
        $secretary = User::factory()->secretary()->create();

        $this->actingAs($admin)->get('/expenses/last-updated')->assertForbidden();
        $this->actingAs($secretary)->get('/expenses/last-updated')->assertForbidden();
    }

    public function test_it_reports_a_null_snapshot_when_there_are_no_expenses(): void
    {
        $director = User::factory()->director()->create();

        $response = $this->actingAs($director)->getJson('/expenses/last-updated');

        $response->assertOk();
        $response->assertExactJson([
            'count' => 0,
            'last_updated_at' => null,
        ]);
    }

    public function test_it_reports_the_expense_count_and_latest_update_time(): void
    {
        $director = User::factory()->director()->create();
        Expense::factory()->create();
        $latest = Expense::factory()->create();

        $response = $this->actingAs($director)->getJson('/expenses/last-updated');

        $response->assertOk();
        $response->assertJson(['count' => 2]);
        $this->assertSame(
            $latest->updated_at->toDateTimeString(),
            $response->json('last_updated_at')
        );
    }

    public function test_the_snapshot_changes_when_an_existing_expense_is_deleted(): void
    {
        // A hard delete doesn't touch any remaining row's updated_at, so
        // the count is what has to change here for the reminder to notice.
        $director = User::factory()->director()->create();
        $expense = Expense::factory()->create();

        $before = $this->actingAs($director)->getJson('/expenses/last-updated')->json();

        $expense->delete();

        $after = $this->actingAs($director)->getJson('/expenses/last-updated')->json();

        $this->assertNotEquals($before, $after);
        $this->assertSame(0, $after['count']);
    }

    public function test_the_expense_layout_only_renders_the_reminder_widget_for_directors(): void
    {
        $director = User::factory()->director()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($director)->get('/dashboard')->assertSee('cdsms-expense-snapshot', false);
        $this->actingAs($admin)->get('/dashboard')->assertDontSee('cdsms-expense-snapshot', false);
    }
}
