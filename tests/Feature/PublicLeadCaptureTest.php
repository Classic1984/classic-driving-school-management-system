<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicLeadCaptureTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_booking_inquiry_creates_a_lead_without_authentication(): void
    {
        $response = $this->postJson('/public/leads', [
            'name' => 'Amaka Obi',
            'phone' => '08012345678',
            'email' => 'amaka@example.com',
            'course' => 'Non-Experience (Auto & Manual) — 4 Weeks — ₦95,000',
            'transmission' => 'Automatic',
            'preferred_date' => '2026-09-01',
            'preferred_time' => '10:00',
            'message' => 'I work weekdays, prefer mornings.',
        ]);

        $response->assertCreated();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('leads', [
            'name' => 'Amaka Obi',
            'phone' => '08012345678',
            'course_interested' => 'Non-Experience (Auto & Manual) — 4 Weeks — ₦95,000',
            'source' => 'Website',
            'status' => 'new',
        ]);

        $lead = Lead::first();
        $this->assertStringContainsString('Email: amaka@example.com', $lead->notes);
        $this->assertStringContainsString('Transmission: Automatic', $lead->notes);
        $this->assertStringContainsString('Preferred date: 2026-09-01', $lead->notes);
        $this->assertStringContainsString('Preferred time: 10:00', $lead->notes);
        $this->assertStringContainsString('Message: I work weekdays, prefer mornings.', $lead->notes);

        $this->assertTrue(ActivityLog::query()->where('description', 'New website booking inquiry from Amaka Obi')->exists());
    }

    public function test_it_requires_a_name_and_phone(): void
    {
        $response = $this->postJson('/public/leads', []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name', 'phone']);
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_optional_fields_are_not_required(): void
    {
        $response = $this->postJson('/public/leads', [
            'name' => 'Bola Ade',
            'phone' => '08087654321',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('leads', [
            'name' => 'Bola Ade',
            'phone' => '08087654321',
            'notes' => null,
        ]);
    }

    public function test_a_filled_honeypot_field_is_silently_dropped(): void
    {
        $response = $this->postJson('/public/leads', [
            'name' => 'Spambot',
            'phone' => '0000000000',
            'botcheck' => 'this field should stay empty',
        ]);

        // Reports success so a bot gets no signal its submission was singled
        // out, but nothing is actually persisted.
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_the_marketing_site_origin_is_allowed_by_cors(): void
    {
        // The marketing site has no session with this app, so it can only
        // reach this endpoint at all if the CORS preflight the browser
        // sends first is answered - config/cors.php is what answers it.
        $response = $this->withHeaders([
            'Origin' => 'https://classicdriving.com.ng',
            'Access-Control-Request-Method' => 'POST',
        ])->options('/public/leads');

        $response->assertNoContent();
        $response->assertHeader('Access-Control-Allow-Origin', 'https://classicdriving.com.ng');
    }
}
