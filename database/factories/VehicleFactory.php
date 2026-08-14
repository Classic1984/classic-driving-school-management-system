<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Toyota Corolla', 'Honda Civic', 'Toyota Camry', 'Kia Rio', 'Hyundai Elantra']),
            'plate_number' => fake()->unique()->bothify('???-###??'),
            'status' => 'active',
        ];
    }
}
