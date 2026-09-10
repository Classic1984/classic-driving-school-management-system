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
