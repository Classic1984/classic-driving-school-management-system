<?php

namespace Tests\Feature;

use App\Mail\CorporateQuotationMail;
use App\Models\CorporateCompany;
use App\Models\CorporateInvoice;
use App\Models\CorporateInvoiceSetting;
use App\Models\CorporateQuotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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

        $this->assertSame('QUO-'.$quotation->issue_date->format('Y').'-00001', $quotation->quotation_number);
    }

    public function test_quotation_numbers_increment_within_a_year_and_reset_the_next(): void
    {
        $first = CorporateQuotation::factory()->create(['issue_date' => '2026-03-01']);
        $second = CorporateQuotation::factory()->create(['issue_date' => '2026-06-01']);
        $thirdYear = CorporateQuotation::factory()->create(['issue_date' => '2027-01-01']);

        $this->assertSame('QUO-2026-00001', $first->quotation_number);
        $this->assertSame('QUO-2026-00002', $second->quotation_number);
        $this->assertSame('QUO-2027-00001', $thirdYear->quotation_number);
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

    public function test_the_create_form_preselects_the_company_given_in_the_query_string(): void
    {
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create(['name' => 'Arco Worldwide']);

        $response = $this->actingAs($director)->get("/corporate-quotations/create?corporate_company_id={$company->id}");

        $response->assertOk();
        $response->assertSee('value="'.$company->id.'" selected', false);
    }

    public function test_the_create_form_offers_the_configured_training_detail_presets(): void
    {
        $director = User::factory()->director()->create();
        CorporateInvoiceSetting::current()->update([
            'programme_options' => "Defensive Driving\nBasic Driving",
            'duration_options' => "One Week\nTwo Weeks",
            'driver_count_options' => "1\n5",
        ]);

        $response = $this->actingAs($director)->get('/corporate-quotations/create');

        $response->assertOk();
        $response->assertSee('Driving\u0022,\u0022Basic Driving', false);
        $response->assertSee('Week\u0022,\u0022Two Weeks', false);
        $response->assertSee('\u00221\u0022,\u00225\u0022', false);
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

    public function test_a_director_can_send_the_quotation_via_whatsapp(): void
    {
        Storage::fake('public');
        config([
            'services.twilio.account_sid' => 'AC-fake-sid',
            'services.twilio.auth_token' => 'fake-token',
            'services.twilio.whatsapp_from' => '+15550001111',
        ]);
        Http::fake(['api.twilio.com/*' => Http::response(['sid' => 'SM123'], 201)]);
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create(['phone' => '08031234567']);
        $quotation = CorporateQuotation::factory()->create(['corporate_company_id' => $company->id]);
        $quotation->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 1000, 'sort_order' => 0]);

        $response = $this->actingAs($director)->post("/corporate-quotations/{$quotation->id}/whatsapp");

        $response->assertRedirect(route('corporate-quotations.show', $quotation));
        $this->assertSame('quotation-whatsapp-sent', session('status'));
        Http::assertSent(fn ($request) => $request['To'] === 'whatsapp:+2348031234567');
    }

    public function test_sending_the_quotation_via_whatsapp_fails_gracefully_when_twilio_is_not_configured(): void
    {
        Storage::fake('public');
        Http::fake();
        $director = User::factory()->director()->create();
        $quotation = CorporateQuotation::factory()->create();
        $quotation->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 1000, 'sort_order' => 0]);

        $response = $this->actingAs($director)->post("/corporate-quotations/{$quotation->id}/whatsapp");

        $response->assertRedirect(route('corporate-quotations.show', $quotation));
        $this->assertSame('quotation-whatsapp-not-configured', session('status'));
        Http::assertNothingSent();
    }

    public function test_sending_the_quotation_via_whatsapp_fails_gracefully_when_the_company_has_no_phone(): void
    {
        Storage::fake('public');
        config([
            'services.twilio.account_sid' => 'AC-fake-sid',
            'services.twilio.auth_token' => 'fake-token',
            'services.twilio.whatsapp_from' => '+15550001111',
        ]);
        Http::fake();
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create(['phone' => null]);
        $quotation = CorporateQuotation::factory()->create(['corporate_company_id' => $company->id]);
        $quotation->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 1000, 'sort_order' => 0]);

        $response = $this->actingAs($director)->post("/corporate-quotations/{$quotation->id}/whatsapp");

        $response->assertRedirect(route('corporate-quotations.show', $quotation));
        $this->assertSame('quotation-whatsapp-no-phone', session('status'));
        Http::assertNothingSent();
    }

    public function test_a_director_can_delete_a_quotation(): void
    {
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create();
        $quotation = CorporateQuotation::factory()->create(['corporate_company_id' => $company->id]);
        $quotation->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 1000, 'sort_order' => 0]);

        $response = $this->actingAs($director)->delete("/corporate-quotations/{$quotation->id}");

        $response->assertRedirect(route('corporate-companies.show', $company));
        $this->assertDatabaseMissing('corporate_quotations', ['id' => $quotation->id]);
    }

    public function test_a_converted_quotation_cannot_be_deleted(): void
    {
        $director = User::factory()->director()->create();
        $quotation = CorporateQuotation::factory()->create();
        $quotation->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 1000, 'sort_order' => 0]);
        $this->actingAs($director)->post("/corporate-quotations/{$quotation->id}/convert");

        $response = $this->actingAs($director)->delete("/corporate-quotations/{$quotation->id}");

        $response->assertRedirect(route('corporate-quotations.show', $quotation));
        $this->assertSame('quotation-already-converted-cannot-delete', session('status'));
        $this->assertDatabaseHas('corporate_quotations', ['id' => $quotation->id]);
    }

    public function test_the_quotation_page_shows_its_own_activity_timeline(): void
    {
        $director = User::factory()->director()->create();
        $this->actingAs($director)->post('/corporate-quotations', $this->validPayload());
        $quotation = CorporateQuotation::first();
        $this->actingAs($director)->post("/corporate-quotations/{$quotation->id}/send");

        $response = $this->actingAs($director)->get("/corporate-quotations/{$quotation->id}");

        $response->assertOk();
        $response->assertSeeInOrder([
            "Sent corporate quotation {$quotation->quotation_number}",
            "Created corporate quotation {$quotation->quotation_number}",
        ]);
    }

    public function test_converting_a_quotation_shows_up_in_both_the_quotation_and_invoice_timelines(): void
    {
        $director = User::factory()->director()->create();
        $quotation = CorporateQuotation::factory()->create();
        $quotation->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 1000, 'sort_order' => 0]);

        $this->actingAs($director)->post("/corporate-quotations/{$quotation->id}/convert");
        $invoice = CorporateInvoice::first();

        $expected = "Converted corporate quotation {$quotation->quotation_number} to invoice {$invoice->invoice_number}";

        $this->actingAs($director)->get("/corporate-quotations/{$quotation->id}")->assertSee($expected);
        $this->actingAs($director)->get("/corporate-invoices/{$invoice->id}")->assertSee($expected);
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
