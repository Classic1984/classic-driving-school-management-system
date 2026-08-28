<?php

namespace Tests\Feature;

use App\Models\Instructor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_course_manager_can_grant_app_access(): void
    {
        $user = User::factory()->create();
        $instructor = Instructor::factory()->create(['phone' => '08031234567']);

        $response = $this->actingAs($user)->post(route('instructors.access.store', $instructor));

        $response->assertRedirect();
        $instructor->refresh();
        $this->assertTrue($instructor->hasAppAccess());
        $this->assertSame('instructor', $instructor->user->role);
        $this->assertNull($instructor->user->pin_set_at);
        $this->assertDatabaseHas('message_logs', [
            'recipient_type' => 'instructor',
            'recipient_id' => $instructor->id,
            'purpose' => 'instructor_access_granted',
        ]);
    }

    public function test_granting_access_twice_does_not_create_a_second_account(): void
    {
        $user = User::factory()->create();
        $instructor = Instructor::factory()->create();
        $this->actingAs($user)->post(route('instructors.access.store', $instructor));

        $response = $this->actingAs($user)->post(route('instructors.access.store', $instructor));

        $response->assertSessionHasErrors('instructor');
        $this->assertSame(1, User::where('role', 'instructor')->count());
    }

    public function test_a_course_manager_can_revoke_app_access(): void
    {
        $user = User::factory()->create();
        $instructor = Instructor::factory()->create();
        $this->actingAs($user)->post(route('instructors.access.store', $instructor));
        $instructorUserId = $instructor->refresh()->user_id;

        $response = $this->actingAs($user)->delete(route('instructors.access.destroy', $instructor));

        $response->assertRedirect();
        $this->assertFalse($instructor->refresh()->hasAppAccess());
        $this->assertDatabaseMissing('users', ['id' => $instructorUserId]);
    }

    public function test_revoking_access_that_was_never_granted_is_rejected(): void
    {
        $user = User::factory()->create();
        $instructor = Instructor::factory()->create();

        $response = $this->actingAs($user)->delete(route('instructors.access.destroy', $instructor));

        $response->assertSessionHasErrors('instructor');
    }

    public function test_a_non_course_manager_cannot_grant_access(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $instructor = Instructor::factory()->create();

        $response = $this->actingAs($user)->post(route('instructors.access.store', $instructor));

        $response->assertForbidden();
        $this->assertFalse($instructor->refresh()->hasAppAccess());
    }
}
