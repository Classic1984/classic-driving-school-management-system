<?php

namespace Database\Factories;

use App\Models\Instructor;
use App\Models\TheoryClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TheoryClass>
 */
class TheoryClassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_date' => fake()->unique()->date(),
            'start_time' => '10:00:00',
            'topic' => fake()->optional()->sentence(3),
            'instructor_id' => Instructor::factory(),
            'notes' => fake()->optional()->sentence(),
            'created_by' => null,
        ];
    }
}
