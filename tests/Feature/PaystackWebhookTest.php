<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\LeadPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaystackWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.paystack.secret_key' => 'sk_test_fake_secret']);
    }

    protected function signedPost(array $payload, ?string $secret = null)
    {
        $secret ??= config('services.paystack.secret_key');
        $body = json_encode($payload);
        $signature = hash_hmac('sha512', $body, $secret);

        return $this->call(
            'POST',
            route('public-payments.paystack-webhook'),
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X_PAYSTACK_SIGNATURE' => $signature],
            $body
        );
    }

    protected function chargeSuccessPayload(array $overrides = []): array
    {
        return array_merge([
            'event' => 'charge.success',
            'data' => [
                'reference' => 'ref_'.uniqid(),
                'amount' => 2000000,
                'currency' => 'NGN',
                'status' => 'success',
                'paid_at' => now()->toIso8601String(),
                'metadata' => [
                    'name' => 'Ada Booking',
                    'phone' => '08031234567',
                    'course' => 'Beginner Driving',
                    'email' => 'ada@example.com',
                    'preferred_date' => '2026-09-01',
                    'preferred_time' => '10:00',
                ],
            ],
        ], $overrides);
    }

    public function test_a_valid_charge_success_webhook_creates_a_lead_and_payment(): void
    {
        $payload = $this->chargeSuccessPayload();

        $response = $this->signedPost($payload);

        $response->assertOk();
        $this->assertDatabaseHas('leads', [
            'name' => 'Ada Booking',
            'phone' => '08031234567',
            'course_interested' => 'Beginner Driving',
            'source' => 'Website',
            'status' => 'new',
        ]);
        $this->assertDatabaseHas('lead_payments', [
            'reference' => $payload['data']['reference'],
            'amount' => 20000.00,
            'currency' => 'NGN',
            'status' => 'success',
        ]);
    }

    public function test_it_stores_the_extra_booking_details_in_notes(): void
    {
        $payload = $this->chargeSuccessPayload();

        $this->signedPost($payload);

        $lead = Lead::first();

        $this->assertStringContainsString('Email: ada@example.com', $lead->notes);
        $this->assertStringContainsString('Preferred date: 2026-09-01', $lead->notes);
        $this->assertStringContainsString('Preferred time: 10:00', $lead->notes);
    }

    public function test_an_invalid_signature_is_rejected_and_nothing_is_created(): void
    {
        $payload = $this->chargeSuccessPayload();

        $response = $this->signedPost($payload, 'wrong-secret');

        $response->assertStatus(400);
        $this->assertDatabaseCount('leads', 0);
        $this->assertDatabaseCount('lead_payments', 0);
    }

    public function test_a_missing_signature_header_is_rejected(): void
    {
        $payload = $this->chargeSuccessPayload();
        $body = json_encode($payload);

        $response = $this->call('POST', route('public-payments.paystack-webhook'), [], [], [], ['CONTENT_TYPE' => 'application/json'], $body);

        $response->assertStatus(400);
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_the_same_reference_is_only_processed_once(): void
    {
        $payload = $this->chargeSuccessPayload();

        $this->signedPost($payload)->assertOk();
        $this->signedPost($payload)->assertOk();

        $this->assertDatabaseCount('leads', 1);
        $this->assertSame(1, LeadPayment::where('reference', $payload['data']['reference'])->count());
    }

    public function test_a_non_charge_success_event_is_ignored(): void
    {
        $payload = $this->chargeSuccessPayload(['event' => 'charge.failed']);

        $response = $this->signedPost($payload);

        $response->assertOk();
        $this->assertDatabaseCount('leads', 0);
        $this->assertDatabaseCount('lead_payments', 0);
    }

    public function test_a_payload_with_no_reference_is_rejected(): void
    {
        $payload = $this->chargeSuccessPayload();
        unset($payload['data']['reference']);

        $response = $this->signedPost($payload);

        $response->assertStatus(400);
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_it_returns_400_when_no_secret_key_is_configured(): void
    {
        config(['services.paystack.secret_key' => null]);
        $payload = $this->chargeSuccessPayload();

        $response = $this->signedPost($payload, 'sk_test_fake_secret');

        $response->assertStatus(400);
        $this->assertDatabaseCount('leads', 0);
    }
}
