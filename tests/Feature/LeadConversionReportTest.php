<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadConversionReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_lead_conversion_report_routes(): void
    {
        $this->get('/lead-conversion-report')->assertRedirect('/login');
        $this->get('/lead-conversion-report/export')->assertRedirect('/login');
        $this->get('/lead-conversion-report/export-pdf')->assertRedirect('/login');
    }

    public function test_it_summarizes_leads_logged_today_by_status(): void
    {
        $user = User::factory()->create();
        Lead::factory()->create(['status' => 'new', 'created_at' => now()]);
        Lead::factory()->create(['status' => 'contacted', 'created_at' => now()]);
        Lead::factory()->create(['status' => 'converted', 'created_at' => now()]);
        Lead::factory()->create(['status' => 'converted', 'created_at' => now()]);
        Lead::factory()->create(['status' => 'lost', 'created_at' => now()]);
        // Not counted: logged yesterday.
        Lead::factory()->create(['status' => 'converted', 'created_at' => now()->subDay()]);

        $response = $this->actingAs($user)->get('/lead-conversion-report?period=today');

        $response->assertOk();
        // 5 total, 1 new, 1 contacted, 2 converted, 1 lost, 40% conversion rate.
        $response->assertSee('5');
        $response->assertSee('40%');
    }

    public function test_it_breaks_leads_down_by_source_with_a_conversion_rate_per_source(): void
    {
        $user = User::factory()->create();
        Lead::factory()->create(['source' => 'Referral', 'status' => 'converted', 'created_at' => now()]);
        Lead::factory()->create(['source' => 'Referral', 'status' => 'lost', 'created_at' => now()]);
        Lead::factory()->create(['source' => 'Walk-in', 'status' => 'new', 'created_at' => now()]);
        Lead::factory()->create(['source' => null, 'status' => 'new', 'created_at' => now()]);

        $response = $this->actingAs($user)->get('/lead-conversion-report?period=today');

        $response->assertOk();
        $response->assertSee('Referral');
        $response->assertSee('Walk-in');
        $response->assertSee('Not Specified');
        // Referral: 2 total, 1 converted -> 50%.
        $response->assertSee('50%');
    }

    public function test_a_period_with_no_leads_shows_an_empty_state(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/lead-conversion-report?period=today');

        $response->assertOk();
        $response->assertSee('No inquiries were logged during this period.');
    }

    public function test_weekly_report_includes_leads_logged_earlier_in_the_week(): void
    {
        $user = User::factory()->create();
        Lead::factory()->create(['source' => 'Website', 'status' => 'new', 'created_at' => now()->startOfWeek()]);

        $response = $this->actingAs($user)->get('/lead-conversion-report?period=week');

        $response->assertOk();
        $response->assertSee('Website');
    }

    public function test_an_invalid_period_falls_back_to_today(): void
    {
        $user = User::factory()->create();
        Lead::factory()->create(['source' => 'Website', 'status' => 'new', 'created_at' => now()]);

        $response = $this->actingAs($user)->get('/lead-conversion-report?period=not-a-real-period');

        $response->assertOk();
        $response->assertSee('Website');
    }

    public function test_authenticated_user_can_export_the_report_as_csv(): void
    {
        $user = User::factory()->create();
        Lead::factory()->create(['source' => 'Referral', 'status' => 'converted', 'created_at' => now()]);

        $response = $this->actingAs($user)->get('/lead-conversion-report/export?period=today');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Conversion Rate', $content);
        $this->assertStringContainsString('Referral', $content);
    }

    public function test_authenticated_user_can_download_the_report_as_a_pdf(): void
    {
        $user = User::factory()->create();
        Lead::factory()->create(['source' => 'Referral', 'status' => 'converted', 'created_at' => now()]);

        $response = $this->actingAs($user)->get('/lead-conversion-report/export-pdf?period=today');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
