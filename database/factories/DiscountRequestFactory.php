<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\DiscountRequest;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiscountRequest>
 */
class DiscountRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $originalFee = fake()->randomFloat(2, 50000, 200000);
        $discountPercentage = fake()->randomElement([5, 10, 15, 20]);
        $discountAmount = round($originalFee * $discountPercentage / 100, 2);

        $student = Student::factory()->create();
        $course = Course::factory()->create(['fee' => $originalFee]);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => $originalFee]);
        $enrollment = $student->courses()->where('course_id', $course->id)->first()->pivot;

        return [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'enrollment_id' => $enrollment->id,
            'requested_by' => User::factory()->secretary(),
            'original_fee' => $originalFee,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $discountAmount,
            'final_fee' => $originalFee - $discountAmount,
            'reason' => fake()->randomElement(['promotional_offer', 'referral_bonus', 'scholarship']),
            'reason_note' => null,
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
