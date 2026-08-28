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

    public function test_a_course_manager_can_resend_login_instructions_before_first_login(): void
    {
        $user = User::factory()->create();
        $instructor = Instructor::factory()->create();
        $this->actingAs($user)->post(route('instructors.access.store', $instructor));
        $this->assertDatabaseCount('message_logs', 1);

        $response = $this->actingAs($user)->post(route('instructors.access.resend', $instructor));

        $response->assertRedirect();
        $response->assertSessionHas('status', 'instructor-access-resent');
        $this->assertDatabaseCount('message_logs', 2);
    }

    public function test_resending_login_instructions_is_rejected_once_the_instructor_has_logged_in(): void
    {
        $user = User::factory()->create();
        $instructor = Instructor::factory()->create();
        $this->actingAs($user)->post(route('instructors.access.store', $instructor));
        $instructor->refresh()->user->forceFill(['pin' => '1234', 'pin_set_at' => now()])->save();

        $response = $this->actingAs($user)->post(route('instructors.access.resend', $instructor));

        $response->assertSessionHasErrors('instructor');
        $this->assertDatabaseCount('message_logs', 1);
    }

    public function test_resending_login_instructions_is_rejected_when_access_was_never_granted(): void
    {
        $user = User::factory()->create();
        $instructor = Instructor::factory()->create();

        $response = $this->actingAs($user)->post(route('instructors.access.resend', $instructor));

        $response->assertSessionHasErrors('instructor');
        $this->assertDatabaseCount('message_logs', 0);
    }

    public function test_a_non_course_manager_cannot_resend_login_instructions(): void
    {
        $courseManager = User::factory()->create();
        $instructor = Instructor::factory()->create();
        $this->actingAs($courseManager)->post(route('instructors.access.store', $instructor));

        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->post(route('instructors.access.resend', $instructor));

        $response->assertForbidden();
        $this->assertDatabaseCount('message_logs', 1);
    }

    public function test_the_instructors_index_shows_the_app_access_status(): void
    {
        $user = User::factory()->create();
        $instructor = Instructor::factory()->create(['name' => 'Grace Adeyemi']);
        $this->actingAs($user)->post(route('instructors.access.store', $instructor));

        $response = $this->actingAs($user)->get(route('instructors.index'));

        $response->assertOk();
        $response->assertSee('Grace Adeyemi');
        $response->assertSee('Pending first login');
    }
}
