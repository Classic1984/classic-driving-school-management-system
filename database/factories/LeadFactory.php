<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'course_interested' => fake()->optional()->words(2, true),
            'source' => fake()->optional()->randomElement(['Walk-in', 'Referral', 'Phone Call', 'Social Media']),
            'notes' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(array_keys(Lead::STATUSES)),
        ];
    }
}
