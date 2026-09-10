<?php

namespace Database\Factories;

use App\Models\CorporateCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CorporateCompany>
 */
class CorporateCompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'contact_person' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'notes' => null,
        ];
    }
}
