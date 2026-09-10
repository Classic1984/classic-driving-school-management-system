<?php

namespace Database\Factories;

use App\Models\CorporateCompany;
use App\Models\CorporateInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CorporateInvoice>
 */
class CorporateInvoiceFactory extends Factory
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
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addWeek()->format('Y-m-d'),
            'programme_name' => 'Defensive Driving',
            'duration_label' => 'One Week',
            'participant_count' => 1,
            'status' => 'pending',
        ];
    }
}
