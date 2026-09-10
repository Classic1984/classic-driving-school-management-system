<?php

namespace Database\Factories;

use App\Models\CorporateCompany;
use App\Models\CorporateCompanyDriver;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CorporateCompanyDriver>
 */
class CorporateCompanyDriverFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'corporate_company_id' => CorporateCompany::factory(),
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'license_number' => fake()->bothify('DL-#####'),
        ];
    }
}
