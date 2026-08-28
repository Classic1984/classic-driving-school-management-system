<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assessment>
 */
class AssessmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'course_id' => Course::factory(),
            'result' => fake()->randomElement(Assessment::RESULTS),
            'score' => fake()->optional()->numberBetween(0, 100),
            'remarks' => fake()->optional()->sentence(),
            'assessed_by' => null,
            'assessed_at' => now(),
        ];
    }
}
