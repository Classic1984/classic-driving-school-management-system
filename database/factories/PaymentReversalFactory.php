<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\PaymentReversal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentReversal>
 */
class PaymentReversalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'reversed_by' => User::factory(),
            'amount' => fake()->randomFloat(2, 20, 500),
            'reason' => fake()->randomElement(['Payment duplicated', 'Recorded in error', 'Student requested a refund']),
        ];
    }
}
