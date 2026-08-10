<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_manage_courses(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        $this->actingAs($admin)->get('/courses/create')->assertForbidden();
        $this->actingAs($admin)->post('/courses', [])->assertForbidden();
        $this->actingAs($admin)->get("/courses/{$course->id}/edit")->assertForbidden();
        $this->actingAs($admin)->put("/courses/{$course->id}", [])->assertForbidden();
        $this->actingAs($admin)->delete("/courses/{$course->id}")->assertForbidden();
    }

    public function test_admin_can_view_courses(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        $this->actingAs($admin)->get('/courses')->assertOk();
        $this->actingAs($admin)->get("/courses/{$course->id}")->assertOk();
    }

    public function test_admin_cannot_manage_instructors(): void
    {
        $admin = User::factory()->admin()->create();
        $instructor = Instructor::factory()->create();

        $this->actingAs($admin)->get('/instructors/create')->assertForbidden();
        $this->actingAs($admin)->post('/instructors', [])->assertForbidden();
        $this->actingAs($admin)->get("/instructors/{$instructor->id}/edit")->assertForbidden();
        $this->actingAs($admin)->put("/instructors/{$instructor->id}", [])->assertForbidden();
        $this->actingAs($admin)->delete("/instructors/{$instructor->id}")->assertForbidden();
    }

    public function test_admin_can_view_instructors(): void
    {
        $admin = User::factory()->admin()->create();
        $instructor = Instructor::factory()->create();

        $this->actingAs($admin)->get('/instructors')->assertOk();
        $this->actingAs($admin)->get("/instructors/{$instructor->id}")->assertOk();
    }

    public function test_admin_cannot_delete_students_but_can_manage_them(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create();

        $this->actingAs($admin)->delete("/students/{$student->id}")->assertForbidden();
        $this->assertDatabaseHas('students', ['id' => $student->id]);

        $this->actingAs($admin)->get('/students/create')->assertOk();
        $this->actingAs($admin)->get("/students/{$student->id}/edit")->assertOk();
    }

    public function test_admin_cannot_delete_attendance_but_can_manage_it(): void
    {
        $admin = User::factory()->admin()->create();
        $attendance = Attendance::factory()->create();

        $this->actingAs($admin)->delete("/attendances/{$attendance->id}")->assertForbidden();
        $this->assertDatabaseHas('attendances', ['id' => $attendance->id]);

        $this->actingAs($admin)->get('/attendances/create')->assertOk();
        $this->actingAs($admin)->get("/attendances/{$attendance->id}/edit")->assertOk();
    }

    public function test_admin_cannot_delete_payments_but_can_manage_them(): void
    {
        $admin = User::factory()->admin()->create();
        $payment = Payment::factory()->create();

        $this->actingAs($admin)->delete("/payments/{$payment->id}")->assertForbidden();
        $this->assertDatabaseHas('payments', ['id' => $payment->id]);

        $this->actingAs($admin)->get('/payments/create')->assertOk();
        $this->actingAs($admin)->get("/payments/{$payment->id}/edit")->assertOk();
    }

    public function test_secretary_can_manage_courses_but_not_delete_them(): void
    {
        $secretary = User::factory()->secretary()->create();
        $course = Course::factory()->create();

        $this->actingAs($secretary)->get('/courses/create')->assertOk();
        $this->actingAs($secretary)->get("/courses/{$course->id}/edit")->assertOk();
        $this->actingAs($secretary)->put("/courses/{$course->id}", [
            'name' => $course->name,
            'description' => $course->description,
            'course_type' => $course->course_type,
            'schedule' => $course->schedule,
            'duration_hours' => $course->duration_hours,
            'duration_weeks' => $course->duration_weeks,
            'fee' => $course->fee,
            'status' => $course->status,
        ])->assertRedirect('/courses');
        $this->actingAs($secretary)->delete("/courses/{$course->id}")->assertForbidden();
        $this->assertDatabaseHas('courses', ['id' => $course->id]);
    }

    public function test_secretary_can_view_courses(): void
    {
        $secretary = User::factory()->secretary()->create();
        $course = Course::factory()->create();

        $this->actingAs($secretary)->get('/courses')->assertOk();
        $this->actingAs($secretary)->get("/courses/{$course->id}")->assertOk();
    }

    public function test_secretary_can_manage_instructors_but_not_delete_them(): void
    {
        $secretary = User::factory()->secretary()->create();
        $instructor = Instructor::factory()->create();

        $this->actingAs($secretary)->get('/instructors/create')->assertOk();
        $this->actingAs($secretary)->get("/instructors/{$instructor->id}/edit")->assertOk();
        $this->actingAs($secretary)->put("/instructors/{$instructor->id}", [
            'name' => $instructor->name,
            'email' => $instructor->email,
            'phone' => $instructor->phone,
            'specialization' => $instructor->specialization,
            'hire_date' => $instructor->hire_date->format('Y-m-d'),
            'status' => $instructor->status,
        ])->assertRedirect('/instructors');
        $this->actingAs($secretary)->delete("/instructors/{$instructor->id}")->assertForbidden();
        $this->assertDatabaseHas('instructors', ['id' => $instructor->id]);
    }

    public function test_secretary_can_view_instructors(): void
    {
        $secretary = User::factory()->secretary()->create();
        $instructor = Instructor::factory()->create();

        $this->actingAs($secretary)->get('/instructors')->assertOk();
        $this->actingAs($secretary)->get("/instructors/{$instructor->id}")->assertOk();
    }

    public function test_secretary_cannot_delete_students_but_can_manage_them(): void
    {
        $secretary = User::factory()->secretary()->create();
        $student = Student::factory()->create();

        $this->actingAs($secretary)->delete("/students/{$student->id}")->assertForbidden();
        $this->assertDatabaseHas('students', ['id' => $student->id]);

        $this->actingAs($secretary)->get('/students/create')->assertOk();
        $this->actingAs($secretary)->get("/students/{$student->id}/edit")->assertOk();
    }

    public function test_secretary_cannot_delete_attendance_but_can_manage_it(): void
    {
        $secretary = User::factory()->secretary()->create();
        $attendance = Attendance::factory()->create();

        $this->actingAs($secretary)->delete("/attendances/{$attendance->id}")->assertForbidden();
        $this->assertDatabaseHas('attendances', ['id' => $attendance->id]);

        $this->actingAs($secretary)->get('/attendances/create')->assertOk();
        $this->actingAs($secretary)->get("/attendances/{$attendance->id}/edit")->assertOk();
    }

    public function test_secretary_cannot_delete_payments_but_can_manage_them(): void
    {
        $secretary = User::factory()->secretary()->create();
        $payment = Payment::factory()->create();

        $this->actingAs($secretary)->delete("/payments/{$payment->id}")->assertForbidden();
        $this->assertDatabaseHas('payments', ['id' => $payment->id]);

        $this->actingAs($secretary)->get('/payments/create')->assertOk();
        $this->actingAs($secretary)->get("/payments/{$payment->id}/edit")->assertOk();
    }

    public function test_director_can_manage_courses_instructors_and_delete_everything(): void
    {
        $director = User::factory()->director()->create();
        $course = Course::factory()->create();
        $instructor = Instructor::factory()->create();
        $student = Student::factory()->create();
        $attendance = Attendance::factory()->create();
        $payment = Payment::factory()->create();

        $this->actingAs($director)->get('/courses/create')->assertOk();
        $this->actingAs($director)->get('/instructors/create')->assertOk();
        $this->actingAs($director)->delete("/courses/{$course->id}")->assertRedirect('/courses');
        $this->actingAs($director)->delete("/instructors/{$instructor->id}")->assertRedirect('/instructors');
        $this->actingAs($director)->delete("/students/{$student->id}")->assertRedirect('/students');
        $this->actingAs($director)->delete("/attendances/{$attendance->id}")->assertRedirect('/attendances');
        $this->actingAs($director)->delete("/payments/{$payment->id}")->assertRedirect('/payments');
    }

    public function test_guests_are_redirected_to_login_from_finance_routes(): void
    {
        $this->get('/expenses')->assertRedirect('/login');
        $this->get('/finance')->assertRedirect('/login');
    }

    public function test_admin_cannot_access_finance_section(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/expenses')->assertForbidden();
        $this->actingAs($admin)->get('/expenses/create')->assertForbidden();
        $this->actingAs($admin)->get('/finance')->assertForbidden();
    }

    public function test_secretary_cannot_access_finance_section(): void
    {
        $secretary = User::factory()->secretary()->create();

        $this->actingAs($secretary)->get('/expenses')->assertForbidden();
        $this->actingAs($secretary)->get('/expenses/create')->assertForbidden();
        $this->actingAs($secretary)->get('/finance')->assertForbidden();
    }

    public function test_director_can_access_finance_section(): void
    {
        $director = User::factory()->director()->create();

        $this->actingAs($director)->get('/expenses')->assertOk();
        $this->actingAs($director)->get('/expenses/create')->assertOk();
        $this->actingAs($director)->get('/finance')->assertOk();
    }

    public function test_director_also_has_full_admin_capabilities(): void
    {
        $director = User::factory()->director()->create();
        $course = Course::factory()->create();

        $this->actingAs($director)->get('/courses/create')->assertOk();
        $this->actingAs($director)->delete("/courses/{$course->id}")->assertRedirect('/courses');
    }

    public function test_the_users_table_still_defaults_new_rows_to_the_restricted_admin_role(): void
    {
        // Public self-registration is gone (see Tests\Feature\Auth\RegistrationTest
        // and Tests\Feature\UserManagementTest) - this locks in the column
        // default itself, so any row created without an explicit role
        // (e.g. a future script or migration) still lands least-privileged.
        $user = User::forceCreate([
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertSame('admin', $user->fresh()->role);
        $this->assertFalse($user->fresh()->isAdmin());
        $this->assertFalse($user->fresh()->isDirector());
    }
}
