<?php

namespace Tests\Feature;

use App\Models\CorporateCompany;
use App\Models\CorporateInvoice;
use App\Models\CorporateInvoiceSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorporateInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_secretary_cannot_access_corporate_invoices(): void
    {
        $secretary = User::factory()->secretary()->create();

        $this->actingAs($secretary)->get('/corporate-invoices')->assertForbidden();
    }

    public function test_a_director_can_create_an_invoice_with_line_items(): void
    {
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create(['name' => 'Arco Worldwide']);

        $response = $this->actingAs($director)->post('/corporate-invoices', [
            'corporate_company_id' => $company->id,
            'invoice_date' => '2026-09-09',
            'due_date' => '2026-09-16',
            'programme_name' => 'Defensive Driving',
            'duration_label' => 'One Week',
            'participant_count' => 1,
            'course_coverage' => "Defensive Driving Principles\nHazard Identification & Risk Management",
            'items' => [
                ['description' => 'One-Week Defensive Driving Course', 'quantity' => 1, 'unit_price' => 65000],
                ['description' => 'Defensive Driving Certification', 'quantity' => 1, 'unit_price' => 10000],
            ],
        ]);

        $invoice = CorporateInvoice::first();
        $response->assertRedirect(route('corporate-invoices.show', $invoice));
        $this->assertDatabaseHas('corporate_invoices', [
            'corporate_company_id' => $company->id,
            'programme_name' => 'Defensive Driving',
            'created_by' => $director->id,
        ]);
        $this->assertDatabaseCount('corporate_invoice_items', 2);
        $this->assertSame(75000.0, $invoice->total());
    }

    public function test_a_new_invoice_is_assigned_a_sequential_invoice_number(): void
    {
        $director = User::factory()->director()->create();

        $this->actingAs($director)->post('/corporate-invoices', $this->validInvoicePayload());
        $invoice = CorporateInvoice::first();

        $this->assertSame(
            'INV-'.$invoice->invoice_date->format('Y').'-'.str_pad((string) $invoice->id, 5, '0', STR_PAD_LEFT),
            $invoice->invoice_number
        );
    }

    public function test_the_invoice_document_shows_the_company_items_and_total_in_words(): void
    {
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create(['name' => 'Arco Worldwide', 'city' => 'Port Harcourt']);
        $invoice = CorporateInvoice::factory()->create(['corporate_company_id' => $company->id]);
        $invoice->items()->create(['description' => 'Two-Week Defensive Driving Course', 'quantity' => 1, 'unit_price' => 75000, 'sort_order' => 0]);

        $response = $this->actingAs($director)->get("/corporate-invoices/{$invoice->id}");

        $response->assertOk();
        $response->assertSee('Arco Worldwide');
        $response->assertSee('Port Harcourt');
        $response->assertSee($invoice->invoice_number);
        $response->assertSee('Two-Week Defensive Driving Course');
        $response->assertSee('Seventy-Five Thousand Naira Only.');
    }

    public function test_the_invoice_document_shows_settings_bank_details_and_payment_terms(): void
    {
        $director = User::factory()->director()->create();
        CorporateInvoiceSetting::current()->update([
            'bank_name' => 'Access Bank',
            'bank_account_name' => 'Classic Driving School & Son Nigeria Limited',
            'bank_account_number' => '1436990473',
            'payment_terms' => "Payment is required before commencement of training.\nThis invoice is valid for 30 days from the date of issue.",
        ]);
        $invoice = CorporateInvoice::factory()->create();
        $invoice->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 1000, 'sort_order' => 0]);

        $response = $this->actingAs($director)->get("/corporate-invoices/{$invoice->id}");

        $response->assertOk();
        $response->assertSee('Access Bank');
        $response->assertSee('1436990473');
        $response->assertSee('Payment is required before commencement of training.');
    }

    public function test_course_coverage_is_hidden_when_left_blank(): void
    {
        $director = User::factory()->director()->create();
        $invoice = CorporateInvoice::factory()->create(['course_coverage' => null]);
        $invoice->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 1000, 'sort_order' => 0]);

        $response = $this->actingAs($director)->get("/corporate-invoices/{$invoice->id}");

        $response->assertOk();
        $response->assertDontSee('COURSE COVERAGE');
    }

    /**
     * @return array<string, mixed>
     */
    private function validInvoicePayload(): array
    {
        return [
            'corporate_company_id' => CorporateCompany::factory()->create()->id,
            'invoice_date' => '2026-09-09',
            'due_date' => '2026-09-16',
            'items' => [
                ['description' => 'Training', 'quantity' => 1, 'unit_price' => 1000],
            ],
        ];
    }
}
