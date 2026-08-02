<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Beginner Driving', 'Defensive Driving', 'Highway Driving', 'Refresher Course']),
            'description' => fake()->sentence(),
            'course_type' => fake()->randomElement(['manual', 'automatic', 'both']),
            'duration_hours' => fake()->numberBetween(5, 40),
            'duration_weeks' => fake()->randomElement([1, 2, 3, 4]),
            'fee' => fake()->randomFloat(2, 50, 500),
            'status' => 'active',
        ];
    }
}
