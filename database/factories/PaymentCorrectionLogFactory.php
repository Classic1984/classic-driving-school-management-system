<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\PaymentCorrectionLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentCorrectionLog>
 */
class PaymentCorrectionLogFactory extends Factory
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
            'corrected_by' => User::factory(),
            'reason' => fake()->sentence(),
            'original_allocations' => [['label' => 'Training', 'amount' => 30000]],
            'new_allocations' => [['label' => 'Training', 'amount' => 20000]],
        ];
    }
}
