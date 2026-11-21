<?php

namespace Database\Factories;

use App\Models\DeskStation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DeskStation>
 */
class DeskStationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Front Desk', 'Secretary Office', 'Reception', 'Enquiries Desk']).' '.fake()->unique()->numberBetween(1, 1000),
            'api_token' => Str::random(48),
            'status' => 'unknown',
        ];
    }
}
