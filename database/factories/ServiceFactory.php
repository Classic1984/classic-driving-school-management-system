<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(["Driver's License Processing", "Learner's Permit"]),
            'price' => fake()->randomFloat(2, 1000, 50000),
            'is_active' => true,
        ];
    }
}
