<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\ReactivationAuditLog;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReactivationAuditLog>
 */
class ReactivationAuditLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $balanceCleared = fake()->randomFloat(2, 0, 40000);
        $additionalFee = fake()->randomElement([20000, 25000, 30000, 40000, 80000]);

        return [
            'student_id' => Student::factory(),
            'course_id' => Course::factory(),
            'reactivated_by' => User::factory(),
            'balance_cleared' => $balanceCleared,
            'additional_fee' => $additionalFee,
            'total_amount' => $balanceCleared + $additionalFee,
        ];
    }
}
