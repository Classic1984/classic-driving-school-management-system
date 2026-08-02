<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_student_routes(): void
    {
        $student = Student::factory()->create();

        $this->get('/students')->assertRedirect('/login');
        $this->get('/students/create')->assertRedirect('/login');
        $this->get("/students/{$student->id}")->assertRedirect('/login');
        $this->get("/students/{$student->id}/edit")->assertRedirect('/login');
        $this->post('/students', [])->assertRedirect('/login');
        $this->put("/students/{$student->id}", [])->assertRedirect('/login');
        $this->delete("/students/{$student->id}")->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_student_index(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Jane Doe']);

        $response = $this->actingAs($user)->get('/students');

        $response->assertOk();
        $response->assertSee('Jane Doe');
    }

    public function test_student_index_can_be_filtered_by_search_term(): void
    {
        $user = User::factory()->create();
        Student::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
        Student::factory()->create(['name' => 'John Smith', 'email' => 'john@example.com']);

        $response = $this->actingAs($user)->get('/students?search=Jane');

        $response->assertOk();
        $response->assertSee('Jane Doe');
        $response->assertDontSee('John Smith');
    }

    public function test_student_index_can_be_filtered_by_status(): void
    {
        $user = User::factory()->create();
        Student::factory()->create(['name' => 'Active Student', 'status' => 'active']);
        Student::factory()->create(['name' => 'Withdrawn Student', 'status' => 'withdrawn']);

        $response = $this->actingAs($user)->get('/students?status=withdrawn');

        $response->assertOk();
        $response->assertSee('Withdrawn Student');
        $response->assertDontSee('Active Student');
    }

    public function test_student_index_can_be_filtered_by_course(): void
    {
        $user = User::factory()->create();
        $courseA = Course::factory()->create(['name' => 'Course A']);
        $courseB = Course::factory()->create(['name' => 'Course B']);
        $studentA = Student::factory()->create(['name' => 'In Course A']);
        $studentB = Student::factory()->create(['name' => 'In Course B']);
        $studentA->courses()->attach($courseA->id, ['enrolled_at' => now(), 'status' => 'active']);
        $studentB->courses()->attach($courseB->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($user)->get("/students?course_id={$courseA->id}");

        $response->assertOk();
        $response->assertSee('In Course A');
        $response->assertDontSee('In Course B');
    }

    public function test_student_index_can_be_filtered_by_payment_lock_status(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $lockedStudent = Student::factory()->create(['name' => 'Locked Student']);
        $clearStudent = Student::factory()->create(['name' => 'Clear Student']);
        $lockedStudent->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'locked', 'locked_reason' => 'overdue_balance']);
        $clearStudent->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($user)->get('/students?payment=locked');

        $response->assertOk();
        $response->assertSee('Locked Student');
        $response->assertDontSee('Clear Student');
    }

    public function test_authenticated_user_can_view_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/students/create');

        $response->assertOk();
    }

    public function test_authenticated_user_can_store_a_student(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'John Smith',
            'email' => 'john.smith@example.com',
            'phone' => '555-0100',
            'address' => '123 Main St',
            'date_of_birth' => '2000-01-15',
            'license_number' => 'LIC-12345',
            'course_type' => 'manual',
            'enrollment_date' => '2026-01-01',
            'status' => 'active',
        ];

        $response = $this->actingAs($user)->post('/students', $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/students');

        $this->assertDatabaseHas('students', [
            'name' => 'John Smith',
            'email' => 'john.smith@example.com',
        ]);
    }

    public function test_storing_a_student_requires_valid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/students', [
            'name' => '',
            'email' => 'not-an-email',
            'phone' => '',
            'date_of_birth' => '',
            'course_type' => 'invalid-course',
            'enrollment_date' => '',
            'status' => 'invalid-status',
        ]);

        $response->assertSessionHasErrors([
            'name', 'email', 'phone', 'date_of_birth', 'course_type', 'enrollment_date', 'status',
        ]);

        $this->assertDatabaseCount('students', 0);
    }

    public function test_storing_a_student_requires_unique_email(): void
    {
        $user = User::factory()->create();
        $existing = Student::factory()->create(['email' => 'duplicate@example.com']);

        $response = $this->actingAs($user)->post('/students', [
            'name' => 'New Student',
            'email' => 'duplicate@example.com',
            'phone' => '555-0100',
            'date_of_birth' => '2000-01-15',
            'course_type' => 'manual',
            'enrollment_date' => '2026-01-01',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('students', 1);
    }

    public function test_authenticated_user_can_view_a_student(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();

        $response = $this->actingAs($user)->get("/students/{$student->id}");

        $response->assertOk();
        $response->assertSee($student->name);
    }

    public function test_student_page_shows_enrolled_courses(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['name' => 'Defensive Driving 101']);
        $student->courses()->attach($course);

        $response = $this->actingAs($user)->get("/students/{$student->id}");

        $response->assertOk();
        $response->assertSee('Defensive Driving 101');
    }

    public function test_authenticated_user_can_view_edit_form(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();

        $response = $this->actingAs($user)->get("/students/{$student->id}/edit");

        $response->assertOk();
        $response->assertSee($student->name);
    }

    public function test_authenticated_user_can_update_a_student(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)->put("/students/{$student->id}", [
            'name' => 'New Name',
            'email' => $student->email,
            'phone' => $student->phone,
            'date_of_birth' => $student->date_of_birth->format('Y-m-d'),
            'license_number' => $student->license_number,
            'course_type' => $student->course_type,
            'enrollment_date' => $student->enrollment_date->format('Y-m-d'),
            'status' => 'completed',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/students');

        $this->assertSame('New Name', $student->fresh()->name);
        $this->assertSame('completed', $student->fresh()->status);
    }

    public function test_authenticated_user_can_delete_a_student(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();

        $response = $this->actingAs($user)->delete("/students/{$student->id}");

        $response->assertRedirect('/students');
        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }
}
