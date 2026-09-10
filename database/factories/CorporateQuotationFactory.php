<?php

namespace Database\Factories;

use App\Models\CorporateCompany;
use App\Models\CorporateQuotation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CorporateQuotation>
 */
class CorporateQuotationFactory extends Factory
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
            'programme_name' => 'Defensive Driving',
            'duration_label' => 'Two Weeks',
            'participant_count' => 5,
            'status' => 'draft',
            'issue_date' => now()->format('Y-m-d'),
            'valid_until' => now()->addDays(30)->format('Y-m-d'),
        ];
    }
}
