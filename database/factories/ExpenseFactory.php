<?php

namespace Database\Factories;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category' => fake()->randomElement(array_keys(Expense::CATEGORIES)),
            'amount' => fake()->randomFloat(2, 10, 2000),
            'expense_date' => fake()->date(),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
