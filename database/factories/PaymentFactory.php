<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
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
            'amount' => fake()->randomFloat(2, 20, 500),
            'payment_date' => fake()->date(),
            'payment_method' => fake()->randomElement(['cash', 'card', 'bank_transfer', 'mobile_money']),
            'status' => 'paid',
            'reference_number' => fake()->unique()->bothify('PAY-#####'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
