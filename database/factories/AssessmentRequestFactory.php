<?php

namespace Database\Factories;

use App\Models\AssessmentRequest;
use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssessmentRequest>
 */
class AssessmentRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'completed']);
        $enrollment = $student->courses()->where('course_id', $course->id)->first()->pivot;

        return [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'enrollment_id' => $enrollment->id,
            'requested_by' => User::factory()->create(['role' => 'instructor']),
            'result' => fake()->randomElement(AssessmentRequest::RESULTS),
            'score' => fake()->optional()->numberBetween(0, 100),
            'remarks' => fake()->optional()->sentence(),
            'status' => 'pending',
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'resolved_by' => User::factory()->director(),
            'resolved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'resolved_by' => User::factory()->director(),
            'resolved_at' => now(),
        ]);
    }
}
