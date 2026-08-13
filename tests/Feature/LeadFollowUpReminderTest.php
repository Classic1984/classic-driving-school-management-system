<?php

namespace Tests\Feature;

use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LeadFollowUpReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.termii.api_key' => 'fake-key']);
    }

    public function test_it_texts_an_open_lead_logged_4_or_more_days_ago_with_no_prior_reminder(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);

        Lead::factory()->create([
            'name' => 'Jane Prospect',
            'phone' => '08031234567',
            'course_interested' => 'Standard Driving Course',
            'status' => 'new',
            'created_at' => now()->subDays(5),
            'last_reminded_at' => null,
        ]);

        $this->artisan('app:send-lead-follow-up-reminder')->assertExitCode(0);

        Http::assertSent(fn ($request) => $request['to'] === '2348031234567'
            && str_contains($request['sms'], 'Jane Prospect')
            && str_contains($request['sms'], 'Standard Driving Course'));
    }

    public function test_it_does_not_text_a_lead_logged_less_than_4_days_ago(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);

        Lead::factory()->create([
            'status' => 'new',
            'created_at' => now()->subDays(2),
            'last_reminded_at' => null,
        ]);

        $this->artisan('app:send-lead-follow-up-reminder')->assertExitCode(0);

        Http::assertNothingSent();
    }

    public function test_it_does_not_text_a_lead_reminded_less_than_4_days_ago(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);

        Lead::factory()->create([
            'status' => 'contacted',
            'created_at' => now()->subDays(10),
            'last_reminded_at' => now()->subDays(1),
        ]);

        $this->artisan('app:send-lead-follow-up-reminder')->assertExitCode(0);

        Http::assertNothingSent();
    }

    public function test_it_texts_again_once_4_days_have_passed_since_the_last_reminder(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);

        Lead::factory()->create([
            'phone' => '08035555555',
            'status' => 'contacted',
            'created_at' => now()->subDays(10),
            'last_reminded_at' => now()->subDays(4),
        ]);

        $this->artisan('app:send-lead-follow-up-reminder')->assertExitCode(0);

        Http::assertSent(fn ($request) => $request['to'] === '2348035555555');
    }

    public function test_it_does_not_text_a_converted_or_lost_lead(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);

        Lead::factory()->create(['status' => 'converted', 'created_at' => now()->subDays(10)]);
        Lead::factory()->create(['status' => 'lost', 'created_at' => now()->subDays(10)]);

        $this->artisan('app:send-lead-follow-up-reminder')->assertExitCode(0);

        Http::assertNothingSent();
    }

    public function test_it_updates_last_reminded_at_after_a_successful_send(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);

        $lead = Lead::factory()->create([
            'status' => 'new',
            'created_at' => now()->subDays(5),
            'last_reminded_at' => null,
        ]);

        $this->artisan('app:send-lead-follow-up-reminder');

        $this->assertNotNull($lead->fresh()->last_reminded_at);
    }

    public function test_it_uses_a_generic_message_when_the_lead_has_no_course_interested(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);

        Lead::factory()->create([
            'name' => 'No Course Lead',
            'course_interested' => null,
            'status' => 'new',
            'created_at' => now()->subDays(5),
        ]);

        $this->artisan('app:send-lead-follow-up-reminder')->assertExitCode(0);

        Http::assertSent(fn ($request) => str_contains($request['sms'], 'No Course Lead')
            && str_contains($request['sms'], 'your inquiry'));
    }

    public function test_it_does_nothing_when_no_leads_are_due(): void
    {
        Http::fake();

        $this->artisan('app:send-lead-follow-up-reminder')->assertExitCode(0);

        Http::assertNothingSent();
    }
}
