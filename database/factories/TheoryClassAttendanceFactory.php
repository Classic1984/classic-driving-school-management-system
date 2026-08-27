<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\TheoryClass;
use App\Models\TheoryClassAttendance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TheoryClassAttendance>
 */
class TheoryClassAttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'theory_class_id' => TheoryClass::factory(),
            'student_id' => Student::factory(),
            'status' => fake()->randomElement(['present', 'absent', 'late', 'excused']),
            'score' => fake()->optional()->numberBetween(0, 100),
            'remarks' => fake()->optional()->sentence(),
            'marked_by' => null,
        ];
    }
}
