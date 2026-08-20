<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\DiscountRequest;
use App\Models\Student;
use App\Models\User;
use App\Notifications\DiscountRequestedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DiscountApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function registrationData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'phone' => '555-0100',
            'date_of_birth' => '2000-01-15',
            'course_type' => 'manual',
            'enrollment_date' => now()->toDateString(),
            'status' => 'active',
        ], $overrides);
    }

    public function test_directors_are_notified_when_a_secretary_requests_a_discount(): void
    {
        Notification::fake();

        $secretary = User::factory()->secretary()->create();
        $director = User::factory()->director()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $this->actingAs($secretary)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'discount_choice' => '5000',
            'discount_reason' => 'promotional_offer',
        ]))->assertSessionHasNoErrors();

        Notification::assertSentTo($director, DiscountRequestedNotification::class);
    }

    public function test_the_registration_page_pops_up_a_message_when_a_discount_needs_approval(): void
    {
        $secretary = User::factory()->secretary()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $response = $this->actingAs($secretary)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'discount_choice' => '5000',
            'discount_reason' => 'promotional_offer',
        ]));

        $response->assertSessionHasNoErrors();
        $student = Student::where('email', 'jane.doe@example.com')->firstOrFail();

        $this->actingAs($secretary)->get("/students/{$student->id}")
            ->assertSee('<script>alert(', false)
            ->assertSee('Director approval', false);
    }

    public function test_no_popup_is_shown_when_registering_without_a_discount(): void
    {
        $secretary = User::factory()->secretary()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $response = $this->actingAs($secretary)->post('/students', $this->registrationData([
            'course_id' => $course->id,
        ]));

        $response->assertSessionHasNoErrors();
        $student = Student::where('email', 'jane.doe@example.com')->firstOrFail();

        $this->actingAs($secretary)->get("/students/{$student->id}")
            ->assertDontSee('<script>alert(', false);
    }

    public function test_a_director_can_approve_a_pending_discount_request(): void
    {
        $secretary = User::factory()->secretary()->create();
        $director = User::factory()->director()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $this->actingAs($secretary)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'discount_choice' => '5000',
            'discount_reason' => 'promotional_offer',
        ]))->assertSessionHasNoErrors();

        $student = Student::where('email', 'jane.doe@example.com')->firstOrFail();
        $discountRequest = DiscountRequest::where('student_id', $student->id)->firstOrFail();

        $response = $this->actingAs($director)->patch("/discount-requests/{$discountRequest->id}/approve");

        $response->assertRedirect(route('approvals.index'));

        $enrollment = $student->courses->first()->pivot;
        $this->assertSame(90000.0, $enrollment->fee());
        $this->assertTrue($enrollment->hasDiscount());
        $this->assertSame($director->id, $enrollment->discount_approved_by);

        $this->assertDatabaseHas('discount_requests', ['id' => $discountRequest->id, 'status' => 'approved', 'resolved_by' => $director->id]);
        $this->assertDatabaseHas('discount_audit_logs', [
            'student_id' => $student->id,
            'applied_by' => $director->id,
            'discount_amount' => 5000,
        ]);
    }

    public function test_a_director_can_reject_a_pending_discount_request(): void
    {
        $secretary = User::factory()->secretary()->create();
        $director = User::factory()->director()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $this->actingAs($secretary)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'discount_choice' => '5000',
            'discount_reason' => 'promotional_offer',
        ]))->assertSessionHasNoErrors();

        $student = Student::where('email', 'jane.doe@example.com')->firstOrFail();
        $discountRequest = DiscountRequest::where('student_id', $student->id)->firstOrFail();

        $response = $this->actingAs($director)->patch("/discount-requests/{$discountRequest->id}/reject");

        $response->assertRedirect(route('approvals.index'));

        $enrollment = $student->courses->first()->pivot;
        $this->assertSame(95000.0, $enrollment->fee());
        $this->assertFalse($enrollment->hasDiscount());
        $this->assertDatabaseCount('discount_audit_logs', 0);
        $this->assertDatabaseHas('discount_requests', ['id' => $discountRequest->id, 'status' => 'rejected', 'resolved_by' => $director->id]);
    }

    public function test_a_secretary_cannot_approve_a_discount_request(): void
    {
        $secretary = User::factory()->secretary()->create();
        $discountRequest = DiscountRequest::factory()->create();

        $this->actingAs($secretary)->patch("/discount-requests/{$discountRequest->id}/approve")->assertForbidden();
        $this->assertDatabaseHas('discount_requests', ['id' => $discountRequest->id, 'status' => 'pending']);
    }

    public function test_a_directors_own_discount_never_creates_a_pending_request(): void
    {
        $director = User::factory()->director()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $this->actingAs($director)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'discount_choice' => '5000',
            'discount_reason' => 'promotional_offer',
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseCount('discount_requests', 0);

        $student = Student::where('email', 'jane.doe@example.com')->firstOrFail();
        $this->assertSame(90000.0, $student->courses->first()->pivot->fee());
    }
}
