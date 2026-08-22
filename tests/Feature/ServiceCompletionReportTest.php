<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Student;
use App\Models\StudentService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ServiceCompletionReportTest extends TestCase
{
    use RefreshDatabase;

    protected function completedCharge(Service $service, string $studentName, ?Carbon $completedAt = null): StudentService
    {
        $student = Student::factory()->create(['name' => $studentName]);
        $studentService = $student->studentServices()->create(['service_id' => $service->id, 'price' => $service->price, 'processing_status' => 'not_started']);

        $studentService->update(['processing_status' => 'completed']);

        if ($completedAt !== null) {
            $studentService->forceFill(['updated_at' => $completedAt])->save();
        }

        return $studentService->fresh();
    }

    public function test_guests_are_redirected_to_login_from_service_report_routes(): void
    {
        $service = Service::factory()->create();

        $this->get("/service-reports/{$service->id}")->assertRedirect('/login');
        $this->get("/service-reports/{$service->id}/export")->assertRedirect('/login');
        $this->get("/service-reports/{$service->id}/export-pdf")->assertRedirect('/login');
    }

    public function test_it_counts_charges_completed_in_the_period_for_the_given_service(): void
    {
        $user = User::factory()->create();
        $permit = Service::factory()->create(['name' => "Learner's Permit"]);
        $this->completedCharge($permit, 'Amaka Obi', now());
        $this->completedCharge($permit, 'Bola Ade', now());
        // Not counted: obtained last year.
        $this->completedCharge($permit, 'Old Student', now()->subYear());

        $response = $this->actingAs($user)->get("/service-reports/{$permit->id}?period=year");

        $response->assertOk();
        $response->assertSee('Amaka Obi');
        $response->assertSee('Bola Ade');
        $response->assertDontSee('Old Student');
        $response->assertSeeInOrder(["Learner's Permit Completed", '2']);
    }

    public function test_it_only_counts_charges_for_the_requested_service_not_others(): void
    {
        $user = User::factory()->create();
        $permit = Service::factory()->create(['name' => "Learner's Permit"]);
        $license = Service::factory()->create(['name' => "Driver's License Processing"]);
        $this->completedCharge($permit, 'Permit Student', now());
        $this->completedCharge($license, 'License Student', now());

        $response = $this->actingAs($user)->get("/service-reports/{$permit->id}?period=all_time");

        $response->assertOk();
        $response->assertSee('Permit Student');
        $response->assertDontSee('License Student');
    }

    public function test_a_pending_charge_is_not_counted_as_completed(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => "Learner's Permit"]);
        $student = Student::factory()->create(['name' => 'Still Waiting']);
        $student->studentServices()->create(['service_id' => $service->id, 'price' => $service->price, 'processing_status' => 'processing']);

        $response = $this->actingAs($user)->get("/service-reports/{$service->id}?period=all_time");

        $response->assertOk();
        $response->assertDontSee('Still Waiting');
        $response->assertSeeInOrder(['Completed', '0']);
    }

    public function test_all_time_is_the_default_period(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => "Learner's Permit"]);
        $this->completedCharge($service, 'Old Timer', now()->subYears(3));

        $response = $this->actingAs($user)->get("/service-reports/{$service->id}");

        $response->assertOk();
        $response->assertSee('Old Timer');
    }

    public function test_an_invalid_period_falls_back_to_all_time(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => "Learner's Permit"]);
        $this->completedCharge($service, 'Old Timer', now()->subYears(3));

        $response = $this->actingAs($user)->get("/service-reports/{$service->id}?period=not-a-real-period");

        $response->assertOk();
        $response->assertSee('Old Timer');
    }

    public function test_weekly_report_only_includes_charges_completed_this_week(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => "Learner's Permit"]);
        $this->completedCharge($service, 'This Week', now()->startOfWeek());
        $this->completedCharge($service, 'Last Month', now()->subMonth());

        $response = $this->actingAs($user)->get("/service-reports/{$service->id}?period=week");

        $response->assertOk();
        $response->assertSee('This Week');
        $response->assertDontSee('Last Month');
    }

    public function test_a_period_with_none_completed_shows_an_empty_state(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => 'Online Certificate']);

        $response = $this->actingAs($user)->get("/service-reports/{$service->id}?period=today");

        $response->assertOk();
        $response->assertSee('No Online Certificate charges were completed during this period.');
    }

    public function test_authenticated_user_can_export_the_report_as_csv(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => "Learner's Permit"]);
        $this->completedCharge($service, 'Amaka Obi', now());

        $response = $this->actingAs($user)->get("/service-reports/{$service->id}/export?period=all_time");

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Completed Date', $content);
        $this->assertStringContainsString('Amaka Obi', $content);
    }

    public function test_authenticated_user_can_download_the_report_as_a_pdf(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => "Learner's Permit"]);
        $this->completedCharge($service, 'Amaka Obi', now());

        $response = $this->actingAs($user)->get("/service-reports/{$service->id}/export-pdf?period=all_time");

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
