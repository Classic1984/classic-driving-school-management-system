<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_certificate_report_routes(): void
    {
        $this->get('/certificate-report')->assertRedirect('/login');
        $this->get('/certificate-report/export')->assertRedirect('/login');
        $this->get('/certificate-report/export-pdf')->assertRedirect('/login');
    }

    public function test_todays_report_only_shows_certificates_issued_today(): void
    {
        $user = User::factory()->create();
        $today = Certificate::factory()->create(['issue_date' => now()->toDateString()]);
        $lastWeek = Certificate::factory()->create(['issue_date' => now()->subWeek()->toDateString()]);

        $response = $this->actingAs($user)->get('/certificate-report?period=today');

        $response->assertOk();
        $response->assertSee($today->certificate_number);
        $response->assertDontSee($lastWeek->certificate_number);
    }

    public function test_weekly_report_includes_certificates_issued_earlier_in_the_week(): void
    {
        $user = User::factory()->create();
        $certificate = Certificate::factory()->create(['issue_date' => now()->startOfWeek()->toDateString()]);

        $response = $this->actingAs($user)->get('/certificate-report?period=week');

        $response->assertOk();
        $response->assertSee($certificate->certificate_number);
    }

    public function test_monthly_and_yearly_reports_include_certificates_issued_earlier_in_the_period(): void
    {
        $user = User::factory()->create();
        $monthly = Certificate::factory()->create(['issue_date' => now()->startOfMonth()->toDateString()]);
        $yearly = Certificate::factory()->create(['issue_date' => now()->startOfYear()->toDateString()]);

        $monthResponse = $this->actingAs($user)->get('/certificate-report?period=month');
        $monthResponse->assertOk();
        $monthResponse->assertSee($monthly->certificate_number);

        $yearResponse = $this->actingAs($user)->get('/certificate-report?period=year');
        $yearResponse->assertOk();
        $yearResponse->assertSee($yearly->certificate_number);
    }

    public function test_an_invalid_period_falls_back_to_today(): void
    {
        $user = User::factory()->create();
        $certificate = Certificate::factory()->create(['issue_date' => now()->toDateString()]);

        $response = $this->actingAs($user)->get('/certificate-report?period=not-a-real-period');

        $response->assertOk();
        $response->assertSee($certificate->certificate_number);
    }

    public function test_authenticated_user_can_export_the_report_as_csv(): void
    {
        $user = User::factory()->create();
        $certificate = Certificate::factory()->create(['issue_date' => now()->toDateString()]);

        $response = $this->actingAs($user)->get('/certificate-report/export?period=today');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Certificate #', $content);
        $this->assertStringContainsString($certificate->certificate_number, $content);
    }

    public function test_authenticated_user_can_download_the_report_as_a_pdf(): void
    {
        $user = User::factory()->create();
        Certificate::factory()->create(['issue_date' => now()->toDateString()]);

        $response = $this->actingAs($user)->get('/certificate-report/export-pdf?period=today');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
