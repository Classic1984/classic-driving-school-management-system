<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentRegistrationReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_student_registration_report_routes(): void
    {
        $this->get('/student-registration-report')->assertRedirect('/login');
        $this->get('/student-registration-report/export')->assertRedirect('/login');
        $this->get('/student-registration-report/export-pdf')->assertRedirect('/login');
    }

    public function test_todays_report_only_shows_students_registered_today(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $today = Student::factory()->create(['name' => 'Registered Today', 'enrollment_date' => now()->toDateString()]);
        $lastWeek = Student::factory()->create(['name' => 'Registered Last Week', 'enrollment_date' => now()->subWeek()->toDateString()]);
        $today->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($user)->get('/student-registration-report?period=today');

        $response->assertOk();
        $response->assertSee('Registered Today');
        $response->assertSee($course->name);
        $response->assertDontSee('Registered Last Week');
    }

    public function test_weekly_report_includes_students_registered_earlier_in_the_week(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Weekly Student', 'enrollment_date' => now()->startOfWeek()->toDateString()]);

        $response = $this->actingAs($user)->get('/student-registration-report?period=week');

        $response->assertOk();
        $response->assertSee('Weekly Student');
    }

    public function test_monthly_and_yearly_reports_include_students_registered_earlier_in_the_period(): void
    {
        $user = User::factory()->create();
        $monthly = Student::factory()->create(['name' => 'Monthly Student', 'enrollment_date' => now()->startOfMonth()->toDateString()]);
        $yearly = Student::factory()->create(['name' => 'Yearly Student', 'enrollment_date' => now()->startOfYear()->toDateString()]);

        $monthResponse = $this->actingAs($user)->get('/student-registration-report?period=month');
        $monthResponse->assertOk();
        $monthResponse->assertSee('Monthly Student');

        $yearResponse = $this->actingAs($user)->get('/student-registration-report?period=year');
        $yearResponse->assertOk();
        $yearResponse->assertSee('Yearly Student');
    }

    public function test_an_invalid_period_falls_back_to_today(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Fallback Student', 'enrollment_date' => now()->toDateString()]);

        $response = $this->actingAs($user)->get('/student-registration-report?period=not-a-real-period');

        $response->assertOk();
        $response->assertSee('Fallback Student');
    }

    public function test_authenticated_user_can_export_the_report_as_csv(): void
    {
        $user = User::factory()->create();
        Student::factory()->create(['name' => 'CSV Student', 'enrollment_date' => now()->toDateString()]);

        $response = $this->actingAs($user)->get('/student-registration-report/export?period=today');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Student ID', $content);
        $this->assertStringContainsString('CSV Student', $content);
    }

    public function test_authenticated_user_can_download_the_report_as_a_pdf(): void
    {
        $user = User::factory()->create();
        Student::factory()->create(['name' => 'PDF Student', 'enrollment_date' => now()->toDateString()]);

        $response = $this->actingAs($user)->get('/student-registration-report/export-pdf?period=today');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
