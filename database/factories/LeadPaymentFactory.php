<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\LeadPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadPayment>
 */
class LeadPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'reference' => fake()->unique()->uuid(),
            'gateway' => 'paystack',
            'amount' => fake()->randomFloat(2, 5000, 50000),
            'currency' => 'NGN',
            'status' => 'success',
            'paid_at' => now(),
            'raw_payload' => null,
        ];
    }
}
