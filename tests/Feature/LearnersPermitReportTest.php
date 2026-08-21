<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LearnersPermitReportTest extends TestCase
{
    use RefreshDatabase;

    protected function obtainedPermit(string $studentName, ?Carbon $obtainedAt = null): void
    {
        $student = Student::factory()->create(['name' => $studentName]);
        $service = Service::factory()->create(['name' => "Learner's Permit", 'price' => 6000]);
        $studentService = $student->studentServices()->create(['service_id' => $service->id, 'price' => 6000, 'processing_status' => 'not_started']);

        $studentService->update(['processing_status' => 'completed']);

        if ($obtainedAt !== null) {
            $studentService->forceFill(['updated_at' => $obtainedAt])->save();
        }
    }

    public function test_guests_are_redirected_to_login_from_learners_permit_report_routes(): void
    {
        $this->get('/learners-permit-report')->assertRedirect('/login');
        $this->get('/learners-permit-report/export')->assertRedirect('/login');
        $this->get('/learners-permit-report/export-pdf')->assertRedirect('/login');
    }

    public function test_it_counts_permits_obtained_in_the_period(): void
    {
        $user = User::factory()->create();
        $this->obtainedPermit('Amaka Obi', now());
        $this->obtainedPermit('Bola Ade', now());
        // Not counted: obtained last year.
        $this->obtainedPermit('Old Student', now()->subYear());

        $response = $this->actingAs($user)->get('/learners-permit-report?period=year');

        $response->assertOk();
        $response->assertSee('Amaka Obi');
        $response->assertSee('Bola Ade');
        $response->assertDontSee('Old Student');
        $response->assertSeeInOrder(["Learner's Permits Obtained", '2']);
    }

    public function test_a_pending_permit_is_not_counted_as_obtained(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Still Waiting']);
        $service = Service::factory()->create(['name' => "Learner's Permit", 'price' => 6000]);
        $student->studentServices()->create(['service_id' => $service->id, 'price' => 6000, 'processing_status' => 'processing']);

        $response = $this->actingAs($user)->get('/learners-permit-report?period=all_time');

        $response->assertOk();
        $response->assertDontSee('Still Waiting');
        $response->assertSeeInOrder(["Learner's Permits Obtained", '0']);
    }

    public function test_all_time_is_the_default_period(): void
    {
        $user = User::factory()->create();
        $this->obtainedPermit('Old Timer', now()->subYears(3));

        $response = $this->actingAs($user)->get('/learners-permit-report');

        $response->assertOk();
        $response->assertSee('Old Timer');
    }

    public function test_an_invalid_period_falls_back_to_all_time(): void
    {
        $user = User::factory()->create();
        $this->obtainedPermit('Old Timer', now()->subYears(3));

        $response = $this->actingAs($user)->get('/learners-permit-report?period=not-a-real-period');

        $response->assertOk();
        $response->assertSee('Old Timer');
    }

    public function test_weekly_report_only_includes_permits_obtained_this_week(): void
    {
        $user = User::factory()->create();
        $this->obtainedPermit('This Week', now()->startOfWeek());
        $this->obtainedPermit('Last Month', now()->subMonth());

        $response = $this->actingAs($user)->get('/learners-permit-report?period=week');

        $response->assertOk();
        $response->assertSee('This Week');
        $response->assertDontSee('Last Month');
    }

    public function test_a_period_with_none_obtained_shows_an_empty_state(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/learners-permit-report?period=today');

        $response->assertOk();
        $response->assertSee("No Learner's Permits were obtained during this period.");
    }

    public function test_authenticated_user_can_export_the_report_as_csv(): void
    {
        $user = User::factory()->create();
        $this->obtainedPermit('Amaka Obi', now());

        $response = $this->actingAs($user)->get('/learners-permit-report/export?period=all_time');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Obtained Date', $content);
        $this->assertStringContainsString('Amaka Obi', $content);
    }

    public function test_authenticated_user_can_download_the_report_as_a_pdf(): void
    {
        $user = User::factory()->create();
        $this->obtainedPermit('Amaka Obi', now());

        $response = $this->actingAs($user)->get('/learners-permit-report/export-pdf?period=all_time');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
