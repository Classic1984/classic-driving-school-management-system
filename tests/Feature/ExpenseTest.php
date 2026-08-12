<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_expense_routes(): void
    {
        $expense = Expense::factory()->create();

        $this->get('/expenses')->assertRedirect('/login');
        $this->get('/expenses/create')->assertRedirect('/login');
        $this->get("/expenses/{$expense->id}")->assertRedirect('/login');
        $this->get("/expenses/{$expense->id}/edit")->assertRedirect('/login');
        $this->post('/expenses', [])->assertRedirect('/login');
        $this->put("/expenses/{$expense->id}", [])->assertRedirect('/login');
        $this->delete("/expenses/{$expense->id}")->assertRedirect('/login');
    }

    public function test_non_directors_are_forbidden_from_expense_routes(): void
    {
        $admin = User::factory()->admin()->create();
        $secretary = User::factory()->secretary()->create();
        $expense = Expense::factory()->create();

        $this->actingAs($admin)->get('/expenses')->assertForbidden();
        $this->actingAs($secretary)->get('/expenses')->assertForbidden();
        $this->actingAs($admin)->delete("/expenses/{$expense->id}")->assertForbidden();
    }

    public function test_director_can_view_expense_index(): void
    {
        $director = User::factory()->director()->create();
        Expense::factory()->create(['category' => 'fuel']);

        $response = $this->actingAs($director)->get('/expenses');

        $response->assertOk();
        $response->assertSee('Fuel');
    }

    public function test_director_can_store_an_expense(): void
    {
        $director = User::factory()->director()->create();

        $response = $this->actingAs($director)->post('/expenses', [
            'category' => 'office_rent',
            'amount' => 15000,
            'expense_date' => now()->toDateString(),
            'description' => 'Monthly rent',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/expenses');

        $this->assertDatabaseHas('expenses', [
            'category' => 'office_rent',
            'amount' => 15000,
        ]);
    }

    public function test_director_can_store_an_expense_in_each_new_category(): void
    {
        $director = User::factory()->director()->create();
        $categories = ['food', 'clothes_shoes', 'house_materials', 'kids', 'debt', 'dssp_payment', 'laundry', 'perfume', 'investment_saving', 'gift'];

        foreach ($categories as $category) {
            $response = $this->actingAs($director)->post('/expenses', [
                'category' => $category,
                'amount' => 5000,
                'expense_date' => now()->toDateString(),
            ]);

            $response->assertSessionHasNoErrors();
            $this->assertDatabaseHas('expenses', ['category' => $category, 'amount' => 5000]);
        }
    }

    public function test_the_expense_form_lists_the_new_categories(): void
    {
        $director = User::factory()->director()->create();

        $response = $this->actingAs($director)->get('/expenses/create');

        $response->assertOk();
        foreach (['Food', 'Clothes & Shoes', 'House Materials', 'Kids', 'Debt', 'DSSP Payment', 'Laundry', 'Perfume', 'Investment/Saving', 'Gift'] as $label) {
            $response->assertSee($label);
        }
    }

    public function test_storing_an_expense_requires_valid_data(): void
    {
        $director = User::factory()->director()->create();

        $response = $this->actingAs($director)->post('/expenses', [
            'category' => 'not-a-real-category',
            'amount' => -5,
            'expense_date' => '',
        ]);

        $response->assertSessionHasErrors(['category', 'amount', 'expense_date']);
        $this->assertDatabaseCount('expenses', 0);
    }

    public function test_director_can_update_an_expense(): void
    {
        $director = User::factory()->director()->create();
        $expense = Expense::factory()->create(['category' => 'fuel', 'amount' => 100]);

        $response = $this->actingAs($director)->put("/expenses/{$expense->id}", [
            'category' => 'salary',
            'amount' => 200,
            'expense_date' => $expense->expense_date->format('Y-m-d'),
            'description' => 'Updated',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/expenses');

        $expense->refresh();
        $this->assertSame('salary', $expense->category);
        $this->assertEquals(200, $expense->amount);
    }

    public function test_director_can_delete_an_expense(): void
    {
        $director = User::factory()->director()->create();
        $expense = Expense::factory()->create();

        $response = $this->actingAs($director)->delete("/expenses/{$expense->id}");

        $response->assertRedirect('/expenses');
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }
}
