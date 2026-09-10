<?php

namespace Database\Factories;

use App\Models\CorporateInvoice;
use App\Models\CorporateInvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CorporateInvoiceItem>
 */
class CorporateInvoiceItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'corporate_invoice_id' => CorporateInvoice::factory(),
            'description' => 'Two-Week Defensive Driving Course',
            'quantity' => 1,
            'unit_price' => 75000,
            'sort_order' => 0,
        ];
    }
}
