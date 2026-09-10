<?php

namespace Tests\Feature;

use App\Mail\CorporateReceiptMail;
use App\Models\CorporateCompany;
use App\Models\CorporateInvoice;
use App\Models\CorporateInvoiceSetting;
use App\Models\CorporatePayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CorporatePaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_secretary_cannot_record_a_corporate_payment(): void
    {
        $secretary = User::factory()->secretary()->create();
        $invoice = CorporateInvoice::factory()->create();

        $this->actingAs($secretary)
            ->post("/corporate-invoices/{$invoice->id}/payments", ['amount' => 1000, 'payment_method' => 'cash', 'payment_date' => now()->format('Y-m-d')])
            ->assertForbidden();
    }

    public function test_a_director_can_record_a_partial_payment(): void
    {
        $director = User::factory()->director()->create();
        $invoice = CorporateInvoice::factory()->create();
        $invoice->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 75000, 'sort_order' => 0]);

        $response = $this->actingAs($director)->post("/corporate-invoices/{$invoice->id}/payments", [
            'amount' => 30000,
            'payment_method' => 'bank_transfer',
            'payment_date' => now()->format('Y-m-d'),
            'transaction_reference' => 'TXN-001',
        ]);

        $response->assertRedirect(route('corporate-invoices.show', $invoice));
        $this->assertDatabaseHas('corporate_payments', [
            'corporate_invoice_id' => $invoice->id,
            'amount' => 30000,
            'recorded_by' => $director->id,
        ]);
        $this->assertSame('pending', $invoice->fresh()->status);
        $this->assertSame(45000.0, $invoice->fresh()->balance());
    }

    public function test_the_invoice_is_marked_paid_once_payments_cover_the_total(): void
    {
        $director = User::factory()->director()->create();
        $invoice = CorporateInvoice::factory()->create();
        $invoice->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 75000, 'sort_order' => 0]);

        $this->actingAs($director)->post("/corporate-invoices/{$invoice->id}/payments", [
            'amount' => 75000,
            'payment_method' => 'cash',
            'payment_date' => now()->format('Y-m-d'),
        ]);

        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertSame(0.0, $invoice->fresh()->balance());
    }

    public function test_a_payment_is_assigned_a_sequential_receipt_number(): void
    {
        $director = User::factory()->director()->create();
        $invoice = CorporateInvoice::factory()->create();
        $invoice->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 75000, 'sort_order' => 0]);

        $this->actingAs($director)->post("/corporate-invoices/{$invoice->id}/payments", [
            'amount' => 75000,
            'payment_method' => 'cash',
            'payment_date' => '2026-09-10',
        ]);

        $payment = CorporatePayment::first();
        $this->assertSame('REC-2026-00001', $payment->receipt_number);
    }

    public function test_receipt_numbers_increment_within_a_year_and_reset_the_next(): void
    {
        $director = User::factory()->director()->create();
        $invoice = CorporateInvoice::factory()->create();
        $invoice->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 300000, 'sort_order' => 0]);

        $first = $invoice->payments()->create(['amount' => 50000, 'payment_method' => 'cash', 'payment_date' => '2026-03-01', 'recorded_by' => $director->id]);
        $second = $invoice->payments()->create(['amount' => 50000, 'payment_method' => 'cash', 'payment_date' => '2026-06-01', 'recorded_by' => $director->id]);
        $thirdYear = $invoice->payments()->create(['amount' => 50000, 'payment_method' => 'cash', 'payment_date' => '2027-01-01', 'recorded_by' => $director->id]);

        $this->assertSame('REC-2026-00001', $first->receipt_number);
        $this->assertSame('REC-2026-00002', $second->receipt_number);
        $this->assertSame('REC-2027-00001', $thirdYear->receipt_number);
    }

    public function test_the_record_payment_button_is_hidden_once_an_invoice_is_paid(): void
    {
        $director = User::factory()->director()->create();
        $invoice = CorporateInvoice::factory()->create(['status' => 'paid']);
        $invoice->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 75000, 'sort_order' => 0]);

        $response = $this->actingAs($director)->get("/corporate-invoices/{$invoice->id}");

        $response->assertOk();
        $response->assertDontSee('Record Payment');
    }

    public function test_a_director_can_view_a_payment_receipt(): void
    {
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create(['name' => 'Arco Worldwide']);
        $invoice = CorporateInvoice::factory()->create(['corporate_company_id' => $company->id, 'programme_name' => 'Defensive Driving']);
        $invoice->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 75000, 'sort_order' => 0]);
        $payment = $invoice->payments()->create([
            'amount' => 75000,
            'payment_method' => 'bank_transfer',
            'payment_date' => '2026-09-10',
            'recorded_by' => $director->id,
        ]);

        $response = $this->actingAs($director)->get("/corporate-payments/{$payment->id}/receipt");

        $response->assertOk();
        $response->assertSee('Arco Worldwide');
        $response->assertSee('Defensive Driving');
        $response->assertSee($payment->receipt_number);
        $response->assertSee('₦75,000');
        $response->assertSee('bank transfer');
    }

    public function test_the_receipt_shows_the_school_website(): void
    {
        $director = User::factory()->director()->create();
        CorporateInvoiceSetting::current()->update(['website' => 'classicdriving.com.ng']);
        $invoice = CorporateInvoice::factory()->create();
        $invoice->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 75000, 'sort_order' => 0]);
        $payment = $invoice->payments()->create([
            'amount' => 75000,
            'payment_method' => 'cash',
            'payment_date' => '2026-09-10',
            'recorded_by' => $director->id,
        ]);

        $screenResponse = $this->actingAs($director)->get("/corporate-payments/{$payment->id}/receipt");
        $pdfResponse = $this->actingAs($director)->get("/corporate-payments/{$payment->id}/receipt/pdf");

        $screenResponse->assertOk();
        $screenResponse->assertSee('classicdriving.com.ng');
        $pdfResponse->assertOk();
    }

    public function test_a_director_can_download_the_receipt_as_a_pdf(): void
    {
        $director = User::factory()->director()->create();
        $invoice = CorporateInvoice::factory()->create();
        $invoice->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 75000, 'sort_order' => 0]);
        $payment = $invoice->payments()->create([
            'amount' => 75000,
            'payment_method' => 'cash',
            'payment_date' => '2026-09-10',
            'recorded_by' => $director->id,
        ]);

        $response = $this->actingAs($director)->get("/corporate-payments/{$payment->id}/receipt/pdf");

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_a_director_can_email_the_receipt_to_the_company(): void
    {
        Mail::fake();
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create(['email' => 'accounts@arco.example']);
        $invoice = CorporateInvoice::factory()->create(['corporate_company_id' => $company->id]);
        $invoice->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 75000, 'sort_order' => 0]);
        $payment = $invoice->payments()->create([
            'amount' => 75000,
            'payment_method' => 'cash',
            'payment_date' => '2026-09-10',
            'recorded_by' => $director->id,
        ]);

        $response = $this->actingAs($director)->post("/corporate-payments/{$payment->id}/receipt/email", [
            'recipient_email' => 'accounts@arco.example',
        ]);

        $response->assertRedirect(route('corporate-payments.receipt', $payment));
        Mail::assertSent(CorporateReceiptMail::class, function (CorporateReceiptMail $mail) use ($payment) {
            return $mail->payment->is($payment) && $mail->hasTo('accounts@arco.example');
        });
    }

    public function test_emailing_a_receipt_requires_a_valid_recipient_email(): void
    {
        Mail::fake();
        $director = User::factory()->director()->create();
        $invoice = CorporateInvoice::factory()->create();
        $invoice->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 75000, 'sort_order' => 0]);
        $payment = $invoice->payments()->create([
            'amount' => 75000,
            'payment_method' => 'cash',
            'payment_date' => '2026-09-10',
            'recorded_by' => $director->id,
        ]);

        $this->actingAs($director)
            ->post("/corporate-payments/{$payment->id}/receipt/email", ['recipient_email' => 'not-an-email'])
            ->assertSessionHasErrors('recipient_email');

        Mail::assertNothingSent();
    }

    public function test_a_director_can_send_the_receipt_via_whatsapp(): void
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
        $payment = $invoice->payments()->create([
            'amount' => 75000,
            'payment_method' => 'cash',
            'payment_date' => '2026-09-10',
            'recorded_by' => $director->id,
        ]);

        $response = $this->actingAs($director)->post("/corporate-payments/{$payment->id}/receipt/whatsapp");

        $response->assertRedirect(route('corporate-payments.receipt', $payment));
        $this->assertSame('receipt-whatsapp-sent', session('status'));
        Http::assertSent(fn ($request) => $request['To'] === 'whatsapp:+2348031234567');
    }

    public function test_sending_the_receipt_via_whatsapp_fails_gracefully_when_twilio_is_not_configured(): void
    {
        Storage::fake('public');
        Http::fake();
        $director = User::factory()->director()->create();
        $invoice = CorporateInvoice::factory()->create();
        $invoice->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 75000, 'sort_order' => 0]);
        $payment = $invoice->payments()->create([
            'amount' => 75000,
            'payment_method' => 'cash',
            'payment_date' => '2026-09-10',
            'recorded_by' => $director->id,
        ]);

        $response = $this->actingAs($director)->post("/corporate-payments/{$payment->id}/receipt/whatsapp");

        $response->assertRedirect(route('corporate-payments.receipt', $payment));
        $this->assertSame('receipt-whatsapp-not-configured', session('status'));
        Http::assertNothingSent();
    }

    public function test_sending_the_receipt_via_whatsapp_fails_gracefully_when_the_company_has_no_phone(): void
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
        $payment = $invoice->payments()->create([
            'amount' => 75000,
            'payment_method' => 'cash',
            'payment_date' => '2026-09-10',
            'recorded_by' => $director->id,
        ]);

        $response = $this->actingAs($director)->post("/corporate-payments/{$payment->id}/receipt/whatsapp");

        $response->assertRedirect(route('corporate-payments.receipt', $payment));
        $this->assertSame('receipt-whatsapp-no-phone', session('status'));
        Http::assertNothingSent();
    }
}
