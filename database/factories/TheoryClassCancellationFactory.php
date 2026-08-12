<?php

namespace Database\Factories;

use App\Models\TheoryClassCancellation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TheoryClassCancellation>
 */
class TheoryClassCancellationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_date' => now()->toDateString(),
            'reason' => fake()->optional()->sentence(),
            'cancelled_by' => User::factory(),
        ];
    }
}
