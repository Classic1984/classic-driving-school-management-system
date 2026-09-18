<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\EnrollmentUpgradeRequest;
use App\Models\Student;
use App\Models\User;
use App\Notifications\EnrollmentUpgradeRequestedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EnrollmentUpgradeRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function enrollStudent(Course $course, array $pivot = []): array
    {
        $student = Student::factory()->create();
        $student->courses()->attach($course->id, array_merge([
            'enrolled_at' => now(),
            'status' => 'active',
            'fee' => $course->fee,
            'original_fee' => $course->fee,
        ], $pivot));

        $enrollment = $student->courses()->where('course_id', $course->id)->first()->pivot;

        return [$student, $enrollment];
    }

    public function test_a_secretarys_duration_upgrade_submission_creates_a_pending_request_instead_of_upgrading(): void
    {
        Notification::fake();
        $secretary = User::factory()->secretary()->create();
        $director = User::factory()->director()->create();
        $twoWeek = Course::factory()->create(['duration_weeks' => 2, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 60000, 'status' => 'active']);
        $fourWeek = Course::factory()->create(['duration_weeks' => 4, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 90000, 'status' => 'active']);
        [$student, $enrollment] = $this->enrollStudent($twoWeek);

        $response = $this->actingAs($secretary)->post("/enrollments/{$enrollment->id}/upgrade", [
            'course_id' => $fourWeek->id,
            'amount_paid' => 15000,
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect("/students/{$student->id}");

        // The enrollment itself is untouched.
        $this->assertSame($twoWeek->id, $enrollment->fresh()->course_id);
        $this->assertSame(0.0, $enrollment->fresh()->amountPaid());

        $this->assertDatabaseHas('enrollment_upgrade_requests', [
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'from_course_id' => $twoWeek->id,
            'to_course_id' => $fourWeek->id,
            'requested_by' => $secretary->id,
            'upgrade_type' => 'duration',
            'previous_fee' => 60000,
            'new_fee' => 90000,
            'upgrade_cost' => 30000,
            'amount_paid' => 15000,
            'payment_method' => 'cash',
            'status' => 'pending',
        ]);

        Notification::assertSentTo($director, EnrollmentUpgradeRequestedNotification::class);
    }

    public function test_a_secretarys_tier_upgrade_submission_creates_a_pending_request(): void
    {
        $secretary = User::factory()->secretary()->create();
        $standard = Course::factory()->create(['duration_weeks' => 4, 'fee' => 95000, 'status' => 'active']);
        $vip = Course::factory()->create(['tier' => 'vip', 'fee' => 125000, 'status' => 'active']);
        [$student, $enrollment] = $this->enrollStudent($standard);

        $this->actingAs($secretary)->post("/enrollments/{$enrollment->id}/upgrade-tier", [
            'course_id' => $vip->id,
        ])->assertSessionHasNoErrors();

        $this->assertSame($standard->id, $enrollment->fresh()->course_id);
        $this->assertDatabaseHas('enrollment_upgrade_requests', [
            'enrollment_id' => $enrollment->id,
            'to_course_id' => $vip->id,
            'upgrade_type' => 'tier',
            'status' => 'pending',
        ]);
    }

    public function test_resubmitting_updates_the_existing_pending_request_instead_of_duplicating_it(): void
    {
        $secretary = User::factory()->secretary()->create();
        $twoWeek = Course::factory()->create(['duration_weeks' => 2, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 60000, 'status' => 'active']);
        $fourWeek = Course::factory()->create(['duration_weeks' => 4, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 90000, 'status' => 'active']);
        [, $enrollment] = $this->enrollStudent($twoWeek);

        $this->actingAs($secretary)->post("/enrollments/{$enrollment->id}/upgrade", [
            'course_id' => $fourWeek->id,
            'amount_paid' => 10000,
            'payment_method' => 'cash',
        ])->assertSessionHasNoErrors();

        $this->actingAs($secretary)->post("/enrollments/{$enrollment->id}/upgrade", [
            'course_id' => $fourWeek->id,
            'amount_paid' => 20000,
            'payment_method' => 'card',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('enrollment_upgrade_requests', 1);
        $this->assertDatabaseHas('enrollment_upgrade_requests', [
            'enrollment_id' => $enrollment->id,
            'amount_paid' => 20000,
            'payment_method' => 'card',
            'status' => 'pending',
        ]);
    }

    public function test_a_directors_own_submission_still_executes_the_upgrade_immediately(): void
    {
        $director = User::factory()->director()->create();
        $twoWeek = Course::factory()->create(['duration_weeks' => 2, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 60000, 'status' => 'active']);
        $fourWeek = Course::factory()->create(['duration_weeks' => 4, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 90000, 'status' => 'active']);
        [$student, $enrollment] = $this->enrollStudent($twoWeek);

        $this->actingAs($director)->post("/enrollments/{$enrollment->id}/upgrade", [
            'course_id' => $fourWeek->id,
        ])->assertSessionHasNoErrors();

        $this->assertSame($fourWeek->id, $enrollment->fresh()->course_id);
        $this->assertDatabaseCount('enrollment_upgrade_requests', 0);
    }

    public function test_a_directors_direct_upgrade_resolves_a_pending_request_raised_by_someone_else(): void
    {
        $secretary = User::factory()->secretary()->create();
        $director = User::factory()->director()->create();
        $twoWeek = Course::factory()->create(['duration_weeks' => 2, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 60000, 'status' => 'active']);
        $fourWeek = Course::factory()->create(['duration_weeks' => 4, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 90000, 'status' => 'active']);
        [, $enrollment] = $this->enrollStudent($twoWeek);

        $this->actingAs($secretary)->post("/enrollments/{$enrollment->id}/upgrade", [
            'course_id' => $fourWeek->id,
        ])->assertSessionHasNoErrors();

        $this->actingAs($director)->post("/enrollments/{$enrollment->id}/upgrade", [
            'course_id' => $fourWeek->id,
        ])->assertSessionHasNoErrors();

        $this->assertSame($fourWeek->id, $enrollment->fresh()->course_id);
        $this->assertDatabaseHas('enrollment_upgrade_requests', ['enrollment_id' => $enrollment->id, 'status' => 'approved']);
    }

    public function test_a_director_can_approve_a_pending_upgrade_request(): void
    {
        $secretary = User::factory()->secretary()->create();
        $director = User::factory()->director()->create();
        $twoWeek = Course::factory()->create(['duration_weeks' => 2, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 60000, 'status' => 'active']);
        $fourWeek = Course::factory()->create(['duration_weeks' => 4, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 90000, 'status' => 'active']);
        [$student, $enrollment] = $this->enrollStudent($twoWeek);

        $this->actingAs($secretary)->post("/enrollments/{$enrollment->id}/upgrade", [
            'course_id' => $fourWeek->id,
            'amount_paid' => 30000,
            'payment_method' => 'cash',
        ])->assertSessionHasNoErrors();

        $upgradeRequest = EnrollmentUpgradeRequest::where('enrollment_id', $enrollment->id)->firstOrFail();

        $response = $this->actingAs($director)->patch("/enrollment-upgrade-requests/{$upgradeRequest->id}/approve");

        $response->assertRedirect('/approvals');
        $this->assertSame('approved', $upgradeRequest->fresh()->status);
        $this->assertSame($director->id, $upgradeRequest->fresh()->resolved_by);

        $enrollment->refresh();
        $this->assertSame($fourWeek->id, $enrollment->course_id);
        $this->assertSame(90000.0, $enrollment->fee());
        $this->assertSame(30000.0, $enrollment->amountPaid());

        $this->assertDatabaseHas('programme_upgrade_logs', [
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'from_course_id' => $twoWeek->id,
            'to_course_id' => $fourWeek->id,
            'upgraded_by' => $director->id,
            'amount_charged' => 30000,
        ]);
    }

    public function test_a_director_can_reject_a_pending_upgrade_request(): void
    {
        $secretary = User::factory()->secretary()->create();
        $director = User::factory()->director()->create();
        $twoWeek = Course::factory()->create(['duration_weeks' => 2, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 60000, 'status' => 'active']);
        $fourWeek = Course::factory()->create(['duration_weeks' => 4, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 90000, 'status' => 'active']);
        [, $enrollment] = $this->enrollStudent($twoWeek);

        $this->actingAs($secretary)->post("/enrollments/{$enrollment->id}/upgrade", [
            'course_id' => $fourWeek->id,
        ])->assertSessionHasNoErrors();

        $upgradeRequest = EnrollmentUpgradeRequest::where('enrollment_id', $enrollment->id)->firstOrFail();

        $this->actingAs($director)->patch("/enrollment-upgrade-requests/{$upgradeRequest->id}/reject")
            ->assertRedirect('/approvals');

        $this->assertSame('rejected', $upgradeRequest->fresh()->status);
        $this->assertSame($twoWeek->id, $enrollment->fresh()->course_id);
        $this->assertSame(0.0, $enrollment->fresh()->amountPaid());
    }

    public function test_a_secretary_cannot_approve_or_reject_an_upgrade_request(): void
    {
        $secretary = User::factory()->secretary()->create();
        $twoWeek = Course::factory()->create(['duration_weeks' => 2, 'status' => 'active']);
        $fourWeek = Course::factory()->create(['duration_weeks' => 4, 'course_type' => $twoWeek->course_type, 'schedule' => $twoWeek->schedule, 'status' => 'active']);
        [, $enrollment] = $this->enrollStudent($twoWeek);

        $this->actingAs($secretary)->post("/enrollments/{$enrollment->id}/upgrade", [
            'course_id' => $fourWeek->id,
        ])->assertSessionHasNoErrors();

        $upgradeRequest = EnrollmentUpgradeRequest::where('enrollment_id', $enrollment->id)->firstOrFail();

        $this->actingAs($secretary)->patch("/enrollment-upgrade-requests/{$upgradeRequest->id}/approve")->assertForbidden();
        $this->actingAs($secretary)->patch("/enrollment-upgrade-requests/{$upgradeRequest->id}/reject")->assertForbidden();
    }

    public function test_approving_fails_gracefully_when_the_student_already_holds_a_separate_enrollment_in_the_target_course(): void
    {
        $secretary = User::factory()->secretary()->create();
        $director = User::factory()->director()->create();
        $twoWeek = Course::factory()->create(['duration_weeks' => 2, 'course_type' => 'manual', 'schedule' => 'weekday', 'status' => 'active']);
        $fourWeek = Course::factory()->create(['duration_weeks' => 4, 'course_type' => 'manual', 'schedule' => 'weekday', 'status' => 'active']);
        [$student, $enrollment] = $this->enrollStudent($twoWeek);

        $this->actingAs($secretary)->post("/enrollments/{$enrollment->id}/upgrade", [
            'course_id' => $fourWeek->id,
        ])->assertSessionHasNoErrors();

        $upgradeRequest = EnrollmentUpgradeRequest::where('enrollment_id', $enrollment->id)->firstOrFail();

        // The student was separately enrolled into the target course after
        // the request was raised.
        $student->courses()->attach($fourWeek->id, [
            'enrolled_at' => now(), 'status' => 'active', 'fee' => $fourWeek->fee, 'original_fee' => $fourWeek->fee,
        ]);

        $response = $this->actingAs($director)->patch("/enrollment-upgrade-requests/{$upgradeRequest->id}/approve");

        $response->assertSessionHasErrors('enrollmentUpgradeRequest');
        $this->assertSame('pending', $upgradeRequest->fresh()->status);
        $this->assertSame($twoWeek->id, $enrollment->fresh()->course_id);
    }

    public function test_the_approval_centre_shows_a_pending_upgrade_request(): void
    {
        $secretary = User::factory()->secretary()->create();
        $director = User::factory()->director()->create();
        $standard = Course::factory()->create(['duration_weeks' => 4, 'fee' => 95000, 'status' => 'active']);
        $vip = Course::factory()->create(['tier' => 'vip', 'fee' => 125000, 'status' => 'active']);
        [$student, $enrollment] = $this->enrollStudent($standard);

        $this->actingAs($secretary)->post("/enrollments/{$enrollment->id}/upgrade-tier", [
            'course_id' => $vip->id,
        ])->assertSessionHasNoErrors();

        $response = $this->actingAs($director)->get('/approvals');

        $response->assertOk();
        $response->assertSee($student->name);
        $response->assertSee($standard->name);
        $response->assertSee($vip->name);
        $response->assertSee('Tier Upgrade Request');
    }

    public function test_the_dashboard_pending_approvals_count_includes_upgrade_requests(): void
    {
        $secretary = User::factory()->secretary()->create();
        $director = User::factory()->director()->create();
        $twoWeek = Course::factory()->create(['duration_weeks' => 2, 'status' => 'active']);
        $fourWeek = Course::factory()->create(['duration_weeks' => 4, 'course_type' => $twoWeek->course_type, 'schedule' => $twoWeek->schedule, 'status' => 'active']);
        [, $enrollment] = $this->enrollStudent($twoWeek);

        $this->actingAs($secretary)->post("/enrollments/{$enrollment->id}/upgrade", [
            'course_id' => $fourWeek->id,
        ])->assertSessionHasNoErrors();

        $response = $this->actingAs($director)->get('/dashboard');

        $response->assertOk();
        $response->assertViewHas('todaysOperations', fn (array $ops) => $ops['pending_approvals'] === 1);
    }
}
