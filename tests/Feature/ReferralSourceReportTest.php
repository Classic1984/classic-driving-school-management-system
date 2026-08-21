<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralSourceReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_referral_source_report_routes(): void
    {
        $this->get('/referral-source-report')->assertRedirect('/login');
        $this->get('/referral-source-report/export')->assertRedirect('/login');
        $this->get('/referral-source-report/export-pdf')->assertRedirect('/login');
    }

    public function test_it_breaks_students_down_by_referral_source(): void
    {
        $user = User::factory()->create();
        Student::factory()->create(['referral_source' => 'facebook', 'status' => 'active']);
        Student::factory()->create(['referral_source' => 'referral', 'status' => 'completed']);
        Student::factory()->create(['referral_source' => null, 'status' => 'active']);

        $response = $this->actingAs($user)->get('/referral-source-report?period=all_time');

        $response->assertOk();
        $response->assertSee('Facebook');
        $response->assertSee('Referral');
        $response->assertSee('Not Specified');
    }

    public function test_revenue_is_summed_per_source_from_paid_payments_only(): void
    {
        $user = User::factory()->create();
        $facebookStudent = Student::factory()->create(['referral_source' => 'facebook']);
        Payment::factory()->create(['student_id' => $facebookStudent->id, 'amount' => 50000, 'status' => 'paid']);
        Payment::factory()->create(['student_id' => $facebookStudent->id, 'amount' => 20000, 'status' => 'pending']);

        $response = $this->actingAs($user)->get('/referral-source-report?period=all_time');

        $response->assertOk();
        $response->assertSeeInOrder(['Facebook', '50,000.00']);
        $response->assertDontSee('70,000.00');
    }

    public function test_completion_rate_is_calculated_per_source(): void
    {
        $user = User::factory()->create();
        Student::factory()->create(['referral_source' => 'flyer', 'status' => 'completed']);
        Student::factory()->create(['referral_source' => 'flyer', 'status' => 'active']);

        $response = $this->actingAs($user)->get('/referral-source-report?period=all_time');

        $response->assertOk();
        // 1 of 2 completed -> 50%.
        $response->assertSeeInOrder(['Flyer', '2', '1', '1', '0', '50%']);
    }

    public function test_rows_are_ordered_by_revenue_collected_not_by_headcount(): void
    {
        $user = User::factory()->create();

        $highVolumeLowRevenue = Student::factory()->create(['referral_source' => 'facebook']);
        Student::factory()->create(['referral_source' => 'facebook']);
        Student::factory()->create(['referral_source' => 'facebook']);
        Payment::factory()->create(['student_id' => $highVolumeLowRevenue->id, 'amount' => 1000, 'status' => 'paid']);

        $lowVolumeHighRevenue = Student::factory()->create(['referral_source' => 'referral']);
        Payment::factory()->create(['student_id' => $lowVolumeHighRevenue->id, 'amount' => 200000, 'status' => 'paid']);

        $response = $this->actingAs($user)->get('/referral-source-report?period=all_time');

        $response->assertOk();
        $response->assertSeeInOrder(['Referral', 'Facebook']);
    }

    public function test_a_period_with_no_students_shows_an_empty_state(): void
    {
        $user = User::factory()->create();
        Student::factory()->create(['referral_source' => 'facebook', 'enrollment_date' => now()->subYears(2)]);

        $response = $this->actingAs($user)->get('/referral-source-report?period=week');

        $response->assertOk();
        $response->assertSee('No students registered during this period.');
    }

    public function test_all_time_is_the_default_period(): void
    {
        $user = User::factory()->create();
        Student::factory()->create(['referral_source' => 'facebook', 'enrollment_date' => now()->subYears(2)]);

        $response = $this->actingAs($user)->get('/referral-source-report');

        $response->assertOk();
        $response->assertSee('Facebook');
    }

    public function test_an_invalid_period_falls_back_to_all_time(): void
    {
        $user = User::factory()->create();
        Student::factory()->create(['referral_source' => 'facebook', 'enrollment_date' => now()->subYears(2)]);

        $response = $this->actingAs($user)->get('/referral-source-report?period=not-a-real-period');

        $response->assertOk();
        $response->assertSee('Facebook');
    }

    public function test_weekly_report_only_includes_students_registered_this_week(): void
    {
        $user = User::factory()->create();
        Student::factory()->create(['referral_source' => 'facebook', 'enrollment_date' => now()->startOfWeek()]);
        Student::factory()->create(['referral_source' => 'flyer', 'enrollment_date' => now()->subWeeks(2)]);

        $response = $this->actingAs($user)->get('/referral-source-report?period=week');

        $response->assertOk();
        $response->assertSee('Facebook');
        $response->assertDontSee('Flyer');
    }

    public function test_outstanding_balance_reflects_unpaid_course_fees(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['referral_source' => 'other']);
        $course = Course::factory()->create(['fee' => 95000]);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);

        $response = $this->actingAs($user)->get('/referral-source-report?period=all_time');

        $response->assertOk();
        $response->assertSeeInOrder(['Other', '95,000.00']);
    }

    public function test_authenticated_user_can_export_the_report_as_csv(): void
    {
        $user = User::factory()->create();
        Student::factory()->create(['referral_source' => 'facebook']);

        $response = $this->actingAs($user)->get('/referral-source-report/export?period=all_time');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Completion Rate', $content);
        $this->assertStringContainsString('Facebook', $content);
    }

    public function test_authenticated_user_can_download_the_report_as_a_pdf(): void
    {
        $user = User::factory()->create();
        Student::factory()->create(['referral_source' => 'facebook']);

        $response = $this->actingAs($user)->get('/referral-source-report/export-pdf?period=all_time');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_the_students_index_links_to_the_report(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/students');

        $response->assertOk();
        $response->assertSee('View Referral Source Report');
    }
}
