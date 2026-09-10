<?php

namespace Tests\Feature;

use App\Mail\CorporateQuotationMail;
use App\Models\CorporateCompany;
use App\Models\CorporateInvoice;
use App\Models\CorporateQuotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CorporateQuotationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_secretary_cannot_access_corporate_quotations(): void
    {
        $secretary = User::factory()->secretary()->create();

        $this->actingAs($secretary)->get('/corporate-quotations')->assertForbidden();
    }

    public function test_a_director_can_create_a_quotation_with_line_items(): void
    {
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create(['name' => 'Arco Worldwide']);

        $response = $this->actingAs($director)->post('/corporate-quotations', [
            'corporate_company_id' => $company->id,
            'issue_date' => '2026-09-10',
            'programme_name' => 'Defensive Driving',
            'items' => [
                ['description' => 'Defensive Driving — 2 Weeks', 'quantity' => 5, 'unit_price' => 75000],
                ['description' => 'Certification', 'quantity' => 5, 'unit_price' => 10000],
            ],
        ]);

        $quotation = CorporateQuotation::first();
        $response->assertRedirect(route('corporate-quotations.show', $quotation));
        $this->assertDatabaseCount('corporate_quotation_items', 2);
        $this->assertSame(425000.0, $quotation->total());
        $this->assertSame('draft', $quotation->status);
    }

    public function test_a_new_quotation_is_assigned_a_sequential_quotation_number(): void
    {
        $director = User::factory()->director()->create();

        $this->actingAs($director)->post('/corporate-quotations', $this->validPayload());
        $quotation = CorporateQuotation::first();

        $this->assertSame(
            'QUO-'.$quotation->issue_date->format('Y').'-'.str_pad((string) $quotation->id, 5, '0', STR_PAD_LEFT),
            $quotation->quotation_number
        );
    }

    public function test_a_director_can_mark_a_quotation_as_sent(): void
    {
        $director = User::factory()->director()->create();
        $quotation = CorporateQuotation::factory()->create(['status' => 'draft']);

        $response = $this->actingAs($director)->post("/corporate-quotations/{$quotation->id}/send");

        $response->assertRedirect(route('corporate-quotations.show', $quotation));
        $this->assertSame('sent', $quotation->fresh()->status);
        $this->assertSame($director->id, $quotation->fresh()->sent_by);
        $this->assertNotNull($quotation->fresh()->sent_at);
    }

    public function test_converting_a_quotation_creates_an_invoice_carrying_over_its_details(): void
    {
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create();
        $quotation = CorporateQuotation::factory()->create([
            'corporate_company_id' => $company->id,
            'programme_name' => 'Defensive Driving',
            'duration_label' => 'Two Weeks',
            'participant_count' => 5,
            'course_coverage' => "Defensive Driving Principles\nHazard Identification",
        ]);
        $quotation->items()->create(['description' => 'Defensive Driving — 2 Weeks', 'quantity' => 5, 'unit_price' => 75000, 'sort_order' => 0]);

        $response = $this->actingAs($director)->post("/corporate-quotations/{$quotation->id}/convert");

        $invoice = CorporateInvoice::first();
        $response->assertRedirect(route('corporate-invoices.show', $invoice));

        $this->assertSame('converted', $quotation->fresh()->status);
        $this->assertSame($invoice->id, $quotation->fresh()->converted_invoice_id);

        $this->assertSame($company->id, $invoice->corporate_company_id);
        $this->assertSame($quotation->id, $invoice->corporate_quotation_id);
        $this->assertSame('Defensive Driving', $invoice->programme_name);
        $this->assertSame('Two Weeks', $invoice->duration_label);
        $this->assertSame(5, $invoice->participant_count);
        $this->assertSame(375000.0, $invoice->total());
        $this->assertCount(1, $invoice->items);
    }

    public function test_a_quotation_cannot_be_converted_twice(): void
    {
        $director = User::factory()->director()->create();
        $quotation = CorporateQuotation::factory()->create();
        $quotation->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 1000, 'sort_order' => 0]);

        $this->actingAs($director)->post("/corporate-quotations/{$quotation->id}/convert");
        $firstInvoiceId = $quotation->fresh()->converted_invoice_id;

        $this->actingAs($director)->post("/corporate-quotations/{$quotation->id}/convert");

        $this->assertSame(1, CorporateInvoice::count());
        $this->assertSame($firstInvoiceId, $quotation->fresh()->converted_invoice_id);
    }

    public function test_a_director_can_download_the_quotation_as_a_pdf(): void
    {
        $director = User::factory()->director()->create();
        $quotation = CorporateQuotation::factory()->create();
        $quotation->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 1000, 'sort_order' => 0]);

        $response = $this->actingAs($director)->get("/corporate-quotations/{$quotation->id}/pdf");

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_a_director_can_email_the_quotation_to_the_company(): void
    {
        Mail::fake();
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create(['email' => 'accounts@arco.example']);
        $quotation = CorporateQuotation::factory()->create(['corporate_company_id' => $company->id]);
        $quotation->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 1000, 'sort_order' => 0]);

        $response = $this->actingAs($director)->post("/corporate-quotations/{$quotation->id}/email", [
            'recipient_email' => 'accounts@arco.example',
        ]);

        $response->assertRedirect(route('corporate-quotations.show', $quotation));
        Mail::assertSent(CorporateQuotationMail::class, function (CorporateQuotationMail $mail) use ($quotation) {
            return $mail->quotation->is($quotation) && $mail->hasTo('accounts@arco.example');
        });
    }

    public function test_emailing_a_quotation_requires_a_valid_recipient_email(): void
    {
        Mail::fake();
        $director = User::factory()->director()->create();
        $quotation = CorporateQuotation::factory()->create();
        $quotation->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 1000, 'sort_order' => 0]);

        $this->actingAs($director)
            ->post("/corporate-quotations/{$quotation->id}/email", ['recipient_email' => 'not-an-email'])
            ->assertSessionHasErrors('recipient_email');

        Mail::assertNothingSent();
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'corporate_company_id' => CorporateCompany::factory()->create()->id,
            'issue_date' => '2026-09-10',
            'items' => [
                ['description' => 'Training', 'quantity' => 1, 'unit_price' => 1000],
            ],
        ];
    }
}
