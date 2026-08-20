<?php

namespace Tests\Feature;

use App\Models\DiscountRequest;
use App\Models\StudentCorrectionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalCentreTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/approvals')->assertRedirect('/login');
    }

    public function test_a_secretary_cannot_view_the_approval_centre(): void
    {
        $secretary = User::factory()->secretary()->create();

        $this->actingAs($secretary)->get('/approvals')->assertForbidden();
    }

    public function test_a_director_sees_pending_discount_and_correction_requests_together(): void
    {
        $director = User::factory()->director()->create();
        $discountRequest = DiscountRequest::factory()->create();
        $correctionRequest = StudentCorrectionRequest::factory()->create();

        $response = $this->actingAs($director)->get('/approvals');

        $response->assertOk();
        $response->assertSee($discountRequest->student->name);
        $response->assertSee($discountRequest->course->name);
        $response->assertSee($correctionRequest->student->name);
        $response->assertSee($correctionRequest->requested_value);
    }

    public function test_the_pending_count_only_includes_pending_requests(): void
    {
        $director = User::factory()->director()->create();
        DiscountRequest::factory()->count(2)->create();
        DiscountRequest::factory()->approved()->create();
        StudentCorrectionRequest::factory()->create();
        StudentCorrectionRequest::factory()->resolved()->create();
        StudentCorrectionRequest::factory()->rejected()->create();

        $response = $this->actingAs($director)->get('/approvals');

        $response->assertOk();
        $response->assertSee('Pending Approvals');
        $response->assertSee('— 3');
    }

    public function test_resolved_and_rejected_requests_do_not_appear(): void
    {
        $director = User::factory()->director()->create();
        $approved = DiscountRequest::factory()->approved()->create();
        $rejected = StudentCorrectionRequest::factory()->rejected()->create();

        $response = $this->actingAs($director)->get('/approvals');

        $response->assertOk();
        $response->assertDontSee($approved->student->name);
        $response->assertDontSee($rejected->student->name);
    }

    public function test_the_empty_state_is_shown_when_nothing_is_pending(): void
    {
        $director = User::factory()->director()->create();

        $response = $this->actingAs($director)->get('/approvals');

        $response->assertOk();
        $response->assertSee('all caught up');
    }

    public function test_approving_a_discount_request_from_the_approval_centre_still_works(): void
    {
        $director = User::factory()->director()->create();
        $discountRequest = DiscountRequest::factory()->create();

        $response = $this->actingAs($director)->patch("/discount-requests/{$discountRequest->id}/approve");

        $response->assertRedirect(route('approvals.index'));
        $this->assertDatabaseHas('discount_requests', ['id' => $discountRequest->id, 'status' => 'approved']);
    }

    public function test_resolving_a_correction_request_from_the_approval_centre_still_works(): void
    {
        $director = User::factory()->director()->create();
        $correctionRequest = StudentCorrectionRequest::factory()->create();

        $response = $this->actingAs($director)->patch("/correction-requests/{$correctionRequest->id}/resolve");

        $response->assertRedirect(route('approvals.index'));
        $this->assertDatabaseHas('student_correction_requests', ['id' => $correctionRequest->id, 'status' => 'resolved']);
    }
}
