<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Student;
use App\Models\StudentCorrectionRequest;
use App\Models\User;
use App\Notifications\StudentCorrectionRequestedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StudentCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $student = Student::factory()->create();

        $this->get("/students/{$student->id}/correction-requests/create")->assertRedirect('/login');
        $this->post("/students/{$student->id}/correction-requests", [])->assertRedirect('/login');
        $this->get('/correction-requests')->assertRedirect('/login');
    }

    public function test_a_secretary_can_submit_a_correction_request_and_directors_are_notified(): void
    {
        Notification::fake();

        $secretary = User::factory()->secretary()->create();
        $director = User::factory()->director()->create();
        $student = Student::factory()->create(['phone' => '555-0000']);

        $response = $this->actingAs($secretary)->post("/students/{$student->id}/correction-requests", [
            'field' => 'phone',
            'requested_value' => '555-1234',
            'reason' => 'Student gave us the wrong number at registration.',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect("/students/{$student->id}");

        $this->assertDatabaseHas('student_correction_requests', [
            'student_id' => $student->id,
            'requested_by' => $secretary->id,
            'field' => 'phone',
            'current_value' => '555-0000',
            'requested_value' => '555-1234',
            'status' => 'pending',
        ]);

        Notification::assertSentTo($director, StudentCorrectionRequestedNotification::class);
    }

    public function test_submitting_a_correction_request_never_changes_the_student(): void
    {
        $secretary = User::factory()->secretary()->create();
        $student = Student::factory()->create(['name' => 'Original Name']);

        $this->actingAs($secretary)->post("/students/{$student->id}/correction-requests", [
            'field' => 'name',
            'requested_value' => 'Tampered Name',
        ])->assertSessionHasNoErrors();

        $this->assertSame('Original Name', $student->fresh()->name);
    }

    public function test_a_correction_request_requires_a_valid_field_and_a_requested_value(): void
    {
        $secretary = User::factory()->secretary()->create();
        $student = Student::factory()->create();

        $response = $this->actingAs($secretary)->post("/students/{$student->id}/correction-requests", [
            'field' => 'balance',
            'requested_value' => '',
        ]);

        $response->assertSessionHasErrors(['field', 'requested_value']);
    }

    public function test_a_secretary_cannot_view_the_correction_requests_inbox(): void
    {
        $secretary = User::factory()->secretary()->create();

        $this->actingAs($secretary)->get('/correction-requests')->assertForbidden();
    }

    public function test_a_secretary_cannot_resolve_or_reject_correction_requests(): void
    {
        $secretary = User::factory()->secretary()->create();
        $correctionRequest = StudentCorrectionRequest::factory()->create();

        $this->actingAs($secretary)->patch("/correction-requests/{$correctionRequest->id}/resolve")->assertForbidden();
        $this->actingAs($secretary)->patch("/correction-requests/{$correctionRequest->id}/reject")->assertForbidden();
        $this->assertSame('pending', $correctionRequest->fresh()->status);
    }

    public function test_a_director_can_view_pending_correction_requests(): void
    {
        $director = User::factory()->director()->create();
        $pending = StudentCorrectionRequest::factory()->create();
        $resolved = StudentCorrectionRequest::factory()->resolved()->create();

        $response = $this->actingAs($director)->get('/correction-requests');

        $response->assertOk();
        $response->assertSee($pending->student->name);
        $response->assertDontSee($resolved->student->name);
    }

    public function test_a_director_can_mark_a_correction_request_resolved(): void
    {
        $director = User::factory()->director()->create();
        $correctionRequest = StudentCorrectionRequest::factory()->create();

        $response = $this->actingAs($director)->patch("/correction-requests/{$correctionRequest->id}/resolve");

        $response->assertRedirect('/correction-requests');
        $correctionRequest->refresh();
        $this->assertSame('resolved', $correctionRequest->status);
        $this->assertSame($director->id, $correctionRequest->resolved_by);
        $this->assertNotNull($correctionRequest->resolved_at);
    }

    public function test_a_director_can_reject_a_correction_request(): void
    {
        $director = User::factory()->director()->create();
        $correctionRequest = StudentCorrectionRequest::factory()->create();

        $response = $this->actingAs($director)->patch("/correction-requests/{$correctionRequest->id}/reject");

        $response->assertRedirect('/correction-requests');
        $this->assertSame('rejected', $correctionRequest->fresh()->status);
    }

    public function test_the_current_value_for_a_program_correction_request_reflects_enrolled_courses(): void
    {
        $secretary = User::factory()->secretary()->create();
        $student = Student::factory()->create();
        $student->courses()->attach(Course::factory()->create(['name' => 'Beginner — 4 Weeks']));

        $this->actingAs($secretary)->post("/students/{$student->id}/correction-requests", [
            'field' => 'program',
            'requested_value' => 'Advanced — 3 Weeks',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('student_correction_requests', [
            'student_id' => $student->id,
            'field' => 'program',
            'current_value' => 'Beginner — 4 Weeks',
            'requested_value' => 'Advanced — 3 Weeks',
        ]);
    }
}
