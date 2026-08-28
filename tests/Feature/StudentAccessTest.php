<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_course_manager_can_grant_app_access(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['phone' => '08031234567']);

        $response = $this->actingAs($user)->post(route('students.access.store', $student));

        $response->assertRedirect();
        $student->refresh();
        $this->assertTrue($student->hasAppAccess());
        $this->assertSame('student', $student->user->role);
        $this->assertNull($student->user->pin_set_at);
        $this->assertDatabaseHas('message_logs', [
            'recipient_type' => 'student',
            'recipient_id' => $student->id,
            'purpose' => 'student_access_granted',
        ]);
    }

    public function test_granting_access_twice_does_not_create_a_second_account(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $this->actingAs($user)->post(route('students.access.store', $student));

        $response = $this->actingAs($user)->post(route('students.access.store', $student));

        $response->assertSessionHasErrors('student');
        $this->assertSame(1, User::where('role', 'student')->count());
    }

    public function test_a_course_manager_can_revoke_app_access(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $this->actingAs($user)->post(route('students.access.store', $student));
        $studentUserId = $student->refresh()->user_id;

        $response = $this->actingAs($user)->delete(route('students.access.destroy', $student));

        $response->assertRedirect();
        $this->assertFalse($student->refresh()->hasAppAccess());
        $this->assertDatabaseMissing('users', ['id' => $studentUserId]);
    }

    public function test_revoking_access_that_was_never_granted_is_rejected(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();

        $response = $this->actingAs($user)->delete(route('students.access.destroy', $student));

        $response->assertSessionHasErrors('student');
    }

    public function test_a_non_course_manager_cannot_grant_access(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $student = Student::factory()->create();

        $response = $this->actingAs($user)->post(route('students.access.store', $student));

        $response->assertForbidden();
        $this->assertFalse($student->refresh()->hasAppAccess());
    }

    public function test_a_course_manager_can_resend_login_instructions_before_first_login(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $this->actingAs($user)->post(route('students.access.store', $student));
        $this->assertDatabaseCount('message_logs', 1);

        $response = $this->actingAs($user)->post(route('students.access.resend', $student));

        $response->assertRedirect();
        $response->assertSessionHas('status', 'student-access-resent');
        $this->assertDatabaseCount('message_logs', 2);
    }

    public function test_resending_login_instructions_is_rejected_once_the_student_has_logged_in(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $this->actingAs($user)->post(route('students.access.store', $student));
        $student->refresh()->user->forceFill(['pin' => '1234', 'pin_set_at' => now()])->save();

        $response = $this->actingAs($user)->post(route('students.access.resend', $student));

        $response->assertSessionHasErrors('student');
        $this->assertDatabaseCount('message_logs', 1);
    }

    public function test_resending_login_instructions_is_rejected_when_access_was_never_granted(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();

        $response = $this->actingAs($user)->post(route('students.access.resend', $student));

        $response->assertSessionHasErrors('student');
        $this->assertDatabaseCount('message_logs', 0);
    }

    public function test_a_non_course_manager_cannot_resend_login_instructions(): void
    {
        $courseManager = User::factory()->create();
        $student = Student::factory()->create();
        $this->actingAs($courseManager)->post(route('students.access.store', $student));

        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->post(route('students.access.resend', $student));

        $response->assertForbidden();
        $this->assertDatabaseCount('message_logs', 1);
    }

    public function test_the_student_profile_shows_the_app_access_status(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Grace Adeyemi']);
        $this->actingAs($user)->post(route('students.access.store', $student));

        $response = $this->actingAs($user)->get(route('students.show', $student));

        $response->assertOk();
        $response->assertSee('Pending first login');
    }
}
