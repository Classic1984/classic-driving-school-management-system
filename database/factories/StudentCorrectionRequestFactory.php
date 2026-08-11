<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentCorrectionRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentCorrectionRequest>
 */
class StudentCorrectionRequestFactory extends Factory
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
            'requested_by' => User::factory()->secretary(),
            'field' => 'phone',
            'current_value' => fake()->phoneNumber(),
            'requested_value' => fake()->phoneNumber(),
            'reason' => fake()->sentence(),
            'status' => 'pending',
        ];
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
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
