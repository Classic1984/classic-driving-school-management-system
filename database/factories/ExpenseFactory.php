<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state. Defaults to 'paid', the same as
     * every expense predating the approval workflow - most existing
     * tests expect a factory-made expense to already be a real, counted
     * outflow; use the pending()/approved()/rejected() states below for
     * the workflow itself.
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
            'status' => 'paid',
            'paid_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'requested_by' => User::factory()->accountant(),
            'status' => 'pending',
            'resolved_by' => null,
            'resolved_at' => null,
            'rejection_reason' => null,
            'paid_at' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'requested_by' => User::factory()->accountant(),
            'status' => 'approved',
            'resolved_by' => User::factory()->director(),
            'resolved_at' => now(),
            'paid_at' => null,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'requested_by' => User::factory()->accountant(),
            'status' => 'rejected',
            'resolved_by' => User::factory()->director(),
            'resolved_at' => now(),
            'rejection_reason' => fake()->sentence(),
            'paid_at' => null,
        ]);
    }
}
