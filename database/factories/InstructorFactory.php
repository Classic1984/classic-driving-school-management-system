<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Instructor>
 */
class InstructorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'license_number' => fake()->unique()->bothify('INS-#####'),
            'specialization' => fake()->randomElement(['manual', 'automatic', 'both']),
            'hire_date' => fake()->date(),
            'status' => 'active',
        ];
    }
}
