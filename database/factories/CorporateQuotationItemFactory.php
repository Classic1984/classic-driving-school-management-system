<?php

namespace Database\Factories;

use App\Models\CorporateQuotation;
use App\Models\CorporateQuotationItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CorporateQuotationItem>
 */
class CorporateQuotationItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'corporate_quotation_id' => CorporateQuotation::factory(),
            'description' => 'Defensive Driving — 2 Weeks',
            'quantity' => 5,
            'unit_price' => 75000,
            'sort_order' => 0,
        ];
    }
}
