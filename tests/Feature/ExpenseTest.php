<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_director_can_store_an_expense_in_each_of_the_newly_added_categories(): void
    {
        $director = User::factory()->director()->create();
        $categories = ['new_car', 'new_engine', 'vehicle_insurance', 'vehicle_registration', 'marketing', 'miscellaneous'];

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

    public function test_the_expense_form_lists_the_newly_added_categories(): void
    {
        $director = User::factory()->director()->create();

        $response = $this->actingAs($director)->get('/expenses/create');

        $response->assertOk();
        foreach (['New Car', 'New Engine', 'Vehicle Insurance', 'Vehicle Registration/Roadworthiness Renewal', 'Marketing/Advertising', 'Miscellaneous/Other'] as $label) {
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

    public function test_director_can_attach_a_receipt_photo_when_recording_an_expense(): void
    {
        Storage::fake('public');
        $director = User::factory()->director()->create();

        $response = $this->actingAs($director)->post('/expenses', [
            'category' => 'fuel',
            'amount' => 5000,
            'expense_date' => now()->toDateString(),
            'receipt_photo' => UploadedFile::fake()->image('receipt.jpg'),
        ]);

        $response->assertSessionHasNoErrors();

        $expense = Expense::where('category', 'fuel')->firstOrFail();
        $this->assertNotNull($expense->receipt_photo_path);
        Storage::disk('public')->assertExists($expense->receipt_photo_path);
    }

    public function test_recording_an_expense_without_a_receipt_photo_still_works(): void
    {
        $director = User::factory()->director()->create();

        $response = $this->actingAs($director)->post('/expenses', [
            'category' => 'fuel',
            'amount' => 5000,
            'expense_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasNoErrors();

        $expense = Expense::where('category', 'fuel')->firstOrFail();
        $this->assertNull($expense->receipt_photo_path);
    }

    public function test_updating_an_expense_with_a_new_receipt_photo_deletes_the_old_one(): void
    {
        Storage::fake('public');
        $director = User::factory()->director()->create();
        $expense = Expense::factory()->create(['receipt_photo_path' => 'expense-receipts/old.jpg']);
        Storage::disk('public')->put('expense-receipts/old.jpg', 'old-contents');

        $response = $this->actingAs($director)->put("/expenses/{$expense->id}", [
            'category' => $expense->category,
            'amount' => $expense->amount,
            'expense_date' => $expense->expense_date->format('Y-m-d'),
            'receipt_photo' => UploadedFile::fake()->image('new-receipt.jpg'),
        ]);

        $response->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing('expense-receipts/old.jpg');
        $newPath = $expense->fresh()->receipt_photo_path;
        $this->assertNotSame('expense-receipts/old.jpg', $newPath);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_updating_an_expense_without_a_new_photo_keeps_the_existing_one(): void
    {
        Storage::fake('public');
        $director = User::factory()->director()->create();
        $expense = Expense::factory()->create(['receipt_photo_path' => 'expense-receipts/existing.jpg']);
        Storage::disk('public')->put('expense-receipts/existing.jpg', 'contents');

        $response = $this->actingAs($director)->put("/expenses/{$expense->id}", [
            'category' => $expense->category,
            'amount' => $expense->amount,
            'expense_date' => $expense->expense_date->format('Y-m-d'),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('expense-receipts/existing.jpg', $expense->fresh()->receipt_photo_path);
        Storage::disk('public')->assertExists('expense-receipts/existing.jpg');
    }

    public function test_deleting_an_expense_removes_its_receipt_photo(): void
    {
        Storage::fake('public');
        $director = User::factory()->director()->create();
        $expense = Expense::factory()->create(['receipt_photo_path' => 'expense-receipts/to-delete.jpg']);
        Storage::disk('public')->put('expense-receipts/to-delete.jpg', 'contents');

        $this->actingAs($director)->delete("/expenses/{$expense->id}")->assertRedirect('/expenses');

        Storage::disk('public')->assertMissing('expense-receipts/to-delete.jpg');
    }

    public function test_a_receipt_photo_must_be_an_image(): void
    {
        Storage::fake('public');
        $director = User::factory()->director()->create();

        $response = $this->actingAs($director)->post('/expenses', [
            'category' => 'fuel',
            'amount' => 5000,
            'expense_date' => now()->toDateString(),
            'receipt_photo' => UploadedFile::fake()->create('receipt.pdf', 100),
        ]);

        $response->assertSessionHasErrors('receipt_photo');
        $this->assertDatabaseCount('expenses', 0);
    }
}
