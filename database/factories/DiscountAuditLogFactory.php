<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\DiscountAuditLog>
 */
class DiscountAuditLogFactory extends Factory
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

        return [
            'student_id' => Student::factory(),
            'course_id' => Course::factory(),
            'applied_by' => User::factory(),
            'original_fee' => $originalFee,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $discountAmount,
            'final_fee' => $originalFee - $discountAmount,
            'reason' => fake()->randomElement(['promotional_offer', 'referral_bonus', 'scholarship']),
            'reason_note' => null,
        ];
    }
}
