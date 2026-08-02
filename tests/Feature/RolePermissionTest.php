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

    public function test_staff_cannot_manage_courses(): void
    {
        $staff = User::factory()->staff()->create();
        $course = Course::factory()->create();

        $this->actingAs($staff)->get('/courses/create')->assertForbidden();
        $this->actingAs($staff)->post('/courses', [])->assertForbidden();
        $this->actingAs($staff)->get("/courses/{$course->id}/edit")->assertForbidden();
        $this->actingAs($staff)->put("/courses/{$course->id}", [])->assertForbidden();
        $this->actingAs($staff)->delete("/courses/{$course->id}")->assertForbidden();
    }

    public function test_staff_can_view_courses(): void
    {
        $staff = User::factory()->staff()->create();
        $course = Course::factory()->create();

        $this->actingAs($staff)->get('/courses')->assertOk();
        $this->actingAs($staff)->get("/courses/{$course->id}")->assertOk();
    }

    public function test_staff_cannot_manage_instructors(): void
    {
        $staff = User::factory()->staff()->create();
        $instructor = Instructor::factory()->create();

        $this->actingAs($staff)->get('/instructors/create')->assertForbidden();
        $this->actingAs($staff)->post('/instructors', [])->assertForbidden();
        $this->actingAs($staff)->get("/instructors/{$instructor->id}/edit")->assertForbidden();
        $this->actingAs($staff)->put("/instructors/{$instructor->id}", [])->assertForbidden();
        $this->actingAs($staff)->delete("/instructors/{$instructor->id}")->assertForbidden();
    }

    public function test_staff_can_view_instructors(): void
    {
        $staff = User::factory()->staff()->create();
        $instructor = Instructor::factory()->create();

        $this->actingAs($staff)->get('/instructors')->assertOk();
        $this->actingAs($staff)->get("/instructors/{$instructor->id}")->assertOk();
    }

    public function test_staff_cannot_delete_students_but_can_manage_them(): void
    {
        $staff = User::factory()->staff()->create();
        $student = Student::factory()->create();

        $this->actingAs($staff)->delete("/students/{$student->id}")->assertForbidden();
        $this->assertDatabaseHas('students', ['id' => $student->id]);

        $this->actingAs($staff)->get('/students/create')->assertOk();
        $this->actingAs($staff)->get("/students/{$student->id}/edit")->assertOk();
    }

    public function test_staff_cannot_delete_attendance_but_can_manage_it(): void
    {
        $staff = User::factory()->staff()->create();
        $attendance = Attendance::factory()->create();

        $this->actingAs($staff)->delete("/attendances/{$attendance->id}")->assertForbidden();
        $this->assertDatabaseHas('attendances', ['id' => $attendance->id]);

        $this->actingAs($staff)->get('/attendances/create')->assertOk();
        $this->actingAs($staff)->get("/attendances/{$attendance->id}/edit")->assertOk();
    }

    public function test_staff_cannot_delete_payments_but_can_manage_them(): void
    {
        $staff = User::factory()->staff()->create();
        $payment = Payment::factory()->create();

        $this->actingAs($staff)->delete("/payments/{$payment->id}")->assertForbidden();
        $this->assertDatabaseHas('payments', ['id' => $payment->id]);

        $this->actingAs($staff)->get('/payments/create')->assertOk();
        $this->actingAs($staff)->get("/payments/{$payment->id}/edit")->assertOk();
    }

    public function test_admin_can_manage_courses_instructors_and_delete_everything(): void
    {
        $admin = User::factory()->create();
        $course = Course::factory()->create();
        $instructor = Instructor::factory()->create();
        $student = Student::factory()->create();
        $attendance = Attendance::factory()->create();
        $payment = Payment::factory()->create();

        $this->actingAs($admin)->get('/courses/create')->assertOk();
        $this->actingAs($admin)->get('/instructors/create')->assertOk();
        $this->actingAs($admin)->delete("/courses/{$course->id}")->assertRedirect('/courses');
        $this->actingAs($admin)->delete("/instructors/{$instructor->id}")->assertRedirect('/instructors');
        $this->actingAs($admin)->delete("/students/{$student->id}")->assertRedirect('/students');
        $this->actingAs($admin)->delete("/attendances/{$attendance->id}")->assertRedirect('/attendances');
        $this->actingAs($admin)->delete("/payments/{$payment->id}")->assertRedirect('/payments');
    }

    public function test_guests_are_redirected_to_login_from_finance_routes(): void
    {
        $this->get('/expenses')->assertRedirect('/login');
        $this->get('/finance')->assertRedirect('/login');
    }

    public function test_staff_cannot_access_finance_section(): void
    {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)->get('/expenses')->assertForbidden();
        $this->actingAs($staff)->get('/expenses/create')->assertForbidden();
        $this->actingAs($staff)->get('/finance')->assertForbidden();
    }

    public function test_admin_cannot_access_finance_section(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->get('/expenses')->assertForbidden();
        $this->actingAs($admin)->get('/expenses/create')->assertForbidden();
        $this->actingAs($admin)->get('/finance')->assertForbidden();
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

    public function test_newly_registered_users_default_to_the_staff_role(): void
    {
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/dashboard');

        $user = User::where('email', 'newuser@example.com')->firstOrFail();
        $this->assertFalse($user->isAdmin());
        $this->assertSame('staff', $user->role);
    }
}
