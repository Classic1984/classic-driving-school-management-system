<?php

namespace Tests\Feature;

use App\Mail\CorporateInvoiceMail;
use App\Models\ActivityLog;
use App\Models\CorporateCompany;
use App\Models\CorporateInvoice;
use App\Models\CorporateInvoiceSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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

        $this->assertSame('INV-'.$invoice->invoice_date->format('Y').'-00001', $invoice->invoice_number);
    }

    public function test_invoice_numbers_increment_within_a_year_and_reset_the_next(): void
    {
        $director = User::factory()->director()->create();

        $first = CorporateInvoice::factory()->create(['invoice_date' => '2026-03-01']);
        $second = CorporateInvoice::factory()->create(['invoice_date' => '2026-06-01']);
        $thirdYear = CorporateInvoice::factory()->create(['invoice_date' => '2027-01-01']);

        $this->assertSame('INV-2026-00001', $first->invoice_number);
        $this->assertSame('INV-2026-00002', $second->invoice_number);
        $this->assertSame('INV-2027-00001', $thirdYear->invoice_number);
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

    /**
     * totalInWords() previously used PHP's intl NumberFormatter, which
     * crashed this exact page with a 500 in production because ext-intl
     * isn't declared in composer.json and wasn't actually installed there -
     * it just happened to be present in local/CI environments. Rewritten
     * in plain PHP; these cases pin the wording so a future change can't
     * silently reintroduce a dependency on an extension that might not be
     * present everywhere this app runs.
     */
    public function test_total_in_words_spells_out_a_range_of_amounts(): void
    {
        // Pairs, not an associative array keyed by amount - PHP silently
        // truncates float array keys to integers, which would drop the
        // ".35" from 101219.35 before the test ever saw it.
        $cases = [
            [0.0, 'Zero Naira Only.'],
            [1.0, 'One Naira Only.'],
            [75000.0, 'Seventy-Five Thousand Naira Only.'],
            [101219.35, 'One Hundred One Thousand Two Hundred Nineteen Naira, Thirty-Five Kobo Only.'],
            [1000000.0, 'One Million Naira Only.'],
        ];

        foreach ($cases as [$amount, $expected]) {
            $invoice = CorporateInvoice::factory()->create();
            $invoice->items()->create(['description' => 'Item', 'quantity' => 1, 'unit_price' => $amount, 'sort_order' => 0]);

            $this->assertSame($expected, $invoice->totalInWords(), "Amount {$amount} spelled incorrectly.");
        }
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

    public function test_the_invoice_document_shows_the_school_website(): void
    {
        $director = User::factory()->director()->create();
        CorporateInvoiceSetting::current()->update(['website' => 'classicdriving.com.ng']);
        $invoice = CorporateInvoice::factory()->create();
        $invoice->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 1000, 'sort_order' => 0]);

        $response = $this->actingAs($director)->get("/corporate-invoices/{$invoice->id}");

        $response->assertOk();
        $response->assertSee('classicdriving.com.ng');
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

    public function test_a_director_can_mark_a_pending_invoice_as_sent(): void
    {
        $director = User::factory()->director()->create();
        $invoice = CorporateInvoice::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($director)->post("/corporate-invoices/{$invoice->id}/send");

        $response->assertRedirect(route('corporate-invoices.show', $invoice));
        $this->assertSame('sent', $invoice->fresh()->status);
        $this->assertSame($director->id, $invoice->fresh()->sent_by);
        $this->assertNotNull($invoice->fresh()->sent_at);
    }

    public function test_an_already_sent_invoice_cannot_be_marked_sent_again(): void
    {
        $director = User::factory()->director()->create();
        $invoice = CorporateInvoice::factory()->create(['status' => 'paid']);

        $this->actingAs($director)->post("/corporate-invoices/{$invoice->id}/send");

        $this->assertSame('paid', $invoice->fresh()->status);
    }

    public function test_a_director_can_cancel_an_invoice_with_a_reason(): void
    {
        $director = User::factory()->director()->create();
        $invoice = CorporateInvoice::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($director)->post("/corporate-invoices/{$invoice->id}/cancel", [
            'cancellation_reason' => 'Client postponed the training indefinitely',
        ]);

        $response->assertRedirect(route('corporate-invoices.show', $invoice));
        $invoice->refresh();
        $this->assertSame('cancelled', $invoice->status);
        $this->assertSame($director->id, $invoice->cancelled_by);
        $this->assertNotNull($invoice->cancelled_at);
        $this->assertSame('Client postponed the training indefinitely', $invoice->cancellation_reason);
    }

    public function test_cancelling_an_invoice_requires_a_reason(): void
    {
        $director = User::factory()->director()->create();
        $invoice = CorporateInvoice::factory()->create(['status' => 'pending']);

        $this->actingAs($director)
            ->post("/corporate-invoices/{$invoice->id}/cancel", [])
            ->assertSessionHasErrors('cancellation_reason');

        $this->assertSame('pending', $invoice->fresh()->status);
    }

    public function test_a_fully_paid_invoice_cannot_be_cancelled(): void
    {
        $director = User::factory()->director()->create();
        $invoice = CorporateInvoice::factory()->create(['status' => 'paid']);

        $this->actingAs($director)->post("/corporate-invoices/{$invoice->id}/cancel", [
            'cancellation_reason' => 'Attempted cancellation',
        ]);

        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertNull($invoice->fresh()->cancellation_reason);
    }

    public function test_the_create_form_preselects_the_company_given_in_the_query_string(): void
    {
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create(['name' => 'Arco Worldwide']);

        $response = $this->actingAs($director)->get("/corporate-invoices/create?corporate_company_id={$company->id}");

        $response->assertOk();
        $response->assertSee('value="'.$company->id.'" selected', false);
    }

    public function test_the_create_form_offers_the_configured_service_options(): void
    {
        $director = User::factory()->director()->create();
        CorporateInvoiceSetting::current()->update([
            'service_options' => "Certificate of Completion\nRegistration Fee",
        ]);

        $response = $this->actingAs($director)->get('/corporate-invoices/create');

        $response->assertOk();
        $response->assertSee('Certificate of Completion');
        $response->assertSee('Registration Fee');
    }

    public function test_the_create_form_offers_the_configured_training_detail_presets(): void
    {
        $director = User::factory()->director()->create();
        CorporateInvoiceSetting::current()->update([
            'programme_options' => "Defensive Driving\nBasic Driving",
            'duration_options' => "One Week\nTwo Weeks",
            'driver_count_options' => "1\n5",
        ]);

        $response = $this->actingAs($director)->get('/corporate-invoices/create');

        $response->assertOk();
        $response->assertSee('Driving\u0022,\u0022Basic Driving', false);
        $response->assertSee('Week\u0022,\u0022Two Weeks', false);
        $response->assertSee('\u00221\u0022,\u00225\u0022', false);
    }

    public function test_the_create_form_has_seeded_training_detail_presets_the_first_time(): void
    {
        $director = User::factory()->director()->create();

        $response = $this->actingAs($director)->get('/corporate-invoices/create');

        $response->assertOk();
        $response->assertSee('Driving\u0022,', false);
        $response->assertSee('One Week\u0022,', false);
        $response->assertSee('\u00221\u0022,', false);
    }

    public function test_the_create_form_shows_a_hint_when_training_detail_presets_are_left_blank(): void
    {
        $director = User::factory()->director()->create();
        CorporateInvoiceSetting::current()->update([
            'programme_options' => '',
            'duration_options' => '',
            'driver_count_options' => '',
        ]);

        $response = $this->actingAs($director)->get('/corporate-invoices/create');

        $response->assertOk();
        $response->assertSee('options: []', false);
        $response->assertSee('No suggestions yet.');
        $response->assertSee(route('corporate-invoice-settings.edit'), false);
    }

    public function test_a_director_can_download_the_invoice_as_a_pdf(): void
    {
        $director = User::factory()->director()->create();
        $invoice = CorporateInvoice::factory()->create();
        $invoice->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 75000, 'sort_order' => 0]);

        $response = $this->actingAs($director)->get("/corporate-invoices/{$invoice->id}/pdf");

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_the_invoice_pdf_still_renders_when_a_signature_is_uploaded(): void
    {
        Storage::fake('public');
        $director = User::factory()->director()->create();
        CorporateInvoiceSetting::current()->update([
            'signature_path' => UploadedFile::fake()->image('signature.png')->store('corporate/signatures', 'public'),
        ]);
        $invoice = CorporateInvoice::factory()->create();
        $invoice->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 75000, 'sort_order' => 0]);

        $response = $this->actingAs($director)->get("/corporate-invoices/{$invoice->id}/pdf");

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_a_director_can_email_the_invoice_to_the_company(): void
    {
        Mail::fake();
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create(['email' => 'accounts@arco.example']);
        $invoice = CorporateInvoice::factory()->create(['corporate_company_id' => $company->id]);
        $invoice->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 75000, 'sort_order' => 0]);

        $response = $this->actingAs($director)->post("/corporate-invoices/{$invoice->id}/email", [
            'recipient_email' => 'accounts@arco.example',
        ]);

        $response->assertRedirect(route('corporate-invoices.show', $invoice));
        Mail::assertSent(CorporateInvoiceMail::class, function (CorporateInvoiceMail $mail) use ($invoice) {
            return $mail->invoice->is($invoice) && $mail->hasTo('accounts@arco.example');
        });
    }

    public function test_emailing_an_invoice_requires_a_valid_recipient_email(): void
    {
        Mail::fake();
        $director = User::factory()->director()->create();
        $invoice = CorporateInvoice::factory()->create();
        $invoice->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 75000, 'sort_order' => 0]);

        $this->actingAs($director)
            ->post("/corporate-invoices/{$invoice->id}/email", ['recipient_email' => 'not-an-email'])
            ->assertSessionHasErrors('recipient_email');

        Mail::assertNothingSent();
    }

    public function test_a_director_can_send_the_invoice_via_whatsapp(): void
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
        $invoice = CorporateInvoice::factory()->create(['corporate_company_id' => $company->id]);
        $invoice->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 75000, 'sort_order' => 0]);

        $response = $this->actingAs($director)->post("/corporate-invoices/{$invoice->id}/whatsapp");

        $response->assertRedirect(route('corporate-invoices.show', $invoice));
        $this->assertSame('invoice-whatsapp-sent', session('status'));
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.twilio.com')
                && $request['To'] === 'whatsapp:+2348031234567'
                && str_contains($request['MediaUrl'], '.pdf');
        });
    }

    public function test_sending_the_invoice_via_whatsapp_fails_gracefully_when_twilio_is_not_configured(): void
    {
        Storage::fake('public');
        Http::fake();
        $director = User::factory()->director()->create();
        $invoice = CorporateInvoice::factory()->create();
        $invoice->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 75000, 'sort_order' => 0]);

        $response = $this->actingAs($director)->post("/corporate-invoices/{$invoice->id}/whatsapp");

        $response->assertRedirect(route('corporate-invoices.show', $invoice));
        $this->assertSame('invoice-whatsapp-not-configured', session('status'));
        Http::assertNothingSent();
    }

    public function test_sending_the_invoice_via_whatsapp_fails_gracefully_when_the_company_has_no_phone(): void
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
        $invoice = CorporateInvoice::factory()->create(['corporate_company_id' => $company->id]);
        $invoice->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 75000, 'sort_order' => 0]);

        $response = $this->actingAs($director)->post("/corporate-invoices/{$invoice->id}/whatsapp");

        $response->assertRedirect(route('corporate-invoices.show', $invoice));
        $this->assertSame('invoice-whatsapp-no-phone', session('status'));
        Http::assertNothingSent();
    }

    public function test_sending_the_invoice_via_whatsapp_fails_gracefully_when_twilio_rejects_it(): void
    {
        Storage::fake('public');
        config([
            'services.twilio.account_sid' => 'AC-fake-sid',
            'services.twilio.auth_token' => 'fake-token',
            'services.twilio.whatsapp_from' => '+15550001111',
        ]);
        Http::fake(['api.twilio.com/*' => Http::response(['message' => 'invalid number'], 400)]);
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create(['phone' => '08031234567']);
        $invoice = CorporateInvoice::factory()->create(['corporate_company_id' => $company->id]);
        $invoice->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 75000, 'sort_order' => 0]);

        $response = $this->actingAs($director)->post("/corporate-invoices/{$invoice->id}/whatsapp");

        $response->assertRedirect(route('corporate-invoices.show', $invoice));
        $this->assertSame('invoice-whatsapp-failed', session('status'));
    }

    public function test_a_director_can_delete_an_invoice_with_no_payments(): void
    {
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create();
        $invoice = CorporateInvoice::factory()->create(['corporate_company_id' => $company->id]);
        $invoice->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 75000, 'sort_order' => 0]);

        $response = $this->actingAs($director)->delete("/corporate-invoices/{$invoice->id}");

        $response->assertRedirect(route('corporate-companies.show', $company));
        $this->assertDatabaseMissing('corporate_invoices', ['id' => $invoice->id]);
        $this->assertDatabaseMissing('corporate_invoice_items', ['corporate_invoice_id' => $invoice->id]);
    }

    public function test_an_invoice_with_payments_cannot_be_deleted(): void
    {
        $director = User::factory()->director()->create();
        $invoice = CorporateInvoice::factory()->create();
        $invoice->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 75000, 'sort_order' => 0]);
        $invoice->payments()->create([
            'amount' => 30000,
            'payment_method' => 'cash',
            'payment_date' => now(),
            'recorded_by' => $director->id,
        ]);

        $response = $this->actingAs($director)->delete("/corporate-invoices/{$invoice->id}");

        $response->assertRedirect(route('corporate-invoices.show', $invoice));
        $this->assertSame('invoice-has-payments', session('status'));
        $this->assertDatabaseHas('corporate_invoices', ['id' => $invoice->id]);
    }

    public function test_the_invoice_page_shows_its_own_activity_timeline(): void
    {
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create();

        $response = $this->actingAs($director)->post('/corporate-invoices', [
            'corporate_company_id' => $company->id,
            'invoice_date' => '2026-09-09',
            'due_date' => '2026-09-16',
            'items' => [
                ['description' => 'Training', 'quantity' => 1, 'unit_price' => 75000],
            ],
        ]);
        $invoice = CorporateInvoice::first();
        $this->actingAs($director)->post("/corporate-invoices/{$invoice->id}/send");

        $response = $this->actingAs($director)->get("/corporate-invoices/{$invoice->id}");

        $response->assertOk();
        $response->assertSeeInOrder([
            "Sent corporate invoice {$invoice->invoice_number}",
            "Created corporate invoice {$invoice->invoice_number}",
        ]);
    }

    public function test_the_invoice_activity_timeline_does_not_leak_another_invoices_entries(): void
    {
        $director = User::factory()->director()->create();
        $invoiceOne = CorporateInvoice::factory()->create();
        $invoiceOne->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 1000, 'sort_order' => 0]);
        $invoiceTwo = CorporateInvoice::factory()->create();
        $invoiceTwo->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 1000, 'sort_order' => 0]);
        ActivityLog::record("Created corporate invoice {$invoiceOne->invoice_number} for a company");
        ActivityLog::record("Created corporate invoice {$invoiceTwo->invoice_number} for a company");

        $response = $this->actingAs($director)->get("/corporate-invoices/{$invoiceOne->id}");

        $response->assertOk();
        $response->assertSee($invoiceOne->invoice_number);
        $response->assertDontSee("Created corporate invoice {$invoiceTwo->invoice_number}");
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
