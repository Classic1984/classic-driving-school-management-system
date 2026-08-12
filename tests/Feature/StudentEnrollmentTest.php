<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $student = Student::factory()->create();

        $this->get("/students/{$student->id}/enroll")->assertRedirect('/login');
        $this->post("/students/{$student->id}/enroll", [])->assertRedirect('/login');
    }

    public function test_a_secretary_cannot_enroll_a_student(): void
    {
        $secretary = User::factory()->secretary()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();

        $this->actingAs($secretary)->get("/students/{$student->id}/enroll")->assertForbidden();
        $this->actingAs($secretary)->post("/students/{$student->id}/enroll", ['course_id' => $course->id])->assertForbidden();
    }

    public function test_an_admin_cannot_enroll_a_student(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create();

        $this->actingAs($admin)->get("/students/{$student->id}/enroll")->assertForbidden();
    }

    public function test_a_director_can_view_the_enroll_form(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['name' => 'Beginner Training']);

        $response = $this->actingAs($director)->get("/students/{$student->id}/enroll");

        $response->assertOk();
        $response->assertSee('Beginner Training');
    }

    public function test_the_enroll_form_excludes_courses_the_student_is_already_enrolled_in(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        $enrolledCourse = Course::factory()->create(['name' => 'Already Enrolled Course']);
        $availableCourse = Course::factory()->create(['name' => 'Not Yet Enrolled Course']);
        $student->courses()->attach($enrolledCourse->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 50000]);

        $response = $this->actingAs($director)->get("/students/{$student->id}/enroll");

        $response->assertOk();
        $response->assertDontSee('Already Enrolled Course');
        $response->assertSee('Not Yet Enrolled Course');
    }

    public function test_the_enroll_form_excludes_inactive_courses(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        Course::factory()->create(['name' => 'Retired Course', 'status' => 'inactive']);

        $response = $this->actingAs($director)->get("/students/{$student->id}/enroll");

        $response->assertOk();
        $response->assertDontSee('Retired Course');
    }

    public function test_a_director_can_enroll_an_existing_student_in_a_course(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['fee' => 95000, 'schedule' => 'weekday', 'duration_weeks' => 4]);

        $this->actingAs($director)
            ->post("/students/{$student->id}/enroll", ['course_id' => $course->id])
            ->assertRedirect(route('students.show', $student));

        $enrollment = $student->courses()->first()->pivot;
        $this->assertSame($course->id, $enrollment->course_id);
        $this->assertSame(95000.0, (float) $enrollment->fee);
        $this->assertSame('active', $enrollment->status);
        $this->assertSame(now()->toDateString(), $enrollment->enrolled_at->toDateString());
        $this->assertSame(now()->addDays(4)->toDateString(), $enrollment->due_date->toDateString());
    }

    public function test_starting_double_period_shortens_the_grace_period(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['schedule' => 'weekday', 'duration_weeks' => 4]);

        $this->actingAs($director)->post("/students/{$student->id}/enroll", [
            'course_id' => $course->id,
            'starts_double_period' => '1',
        ])->assertSessionHasNoErrors();

        $enrollment = $student->courses()->first()->pivot;
        $this->assertSame(now()->addDays(2)->toDateString(), $enrollment->due_date->toDateString());
    }

    public function test_enrolling_with_a_discount_logs_it(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $this->actingAs($director)->post("/students/{$student->id}/enroll", [
            'course_id' => $course->id,
            'discount_choice' => '5000',
            'discount_reason' => 'scholarship',
        ])->assertSessionHasNoErrors();

        $enrollment = $student->courses()->first()->pivot;
        $this->assertSame(90000.0, (float) $enrollment->fee);
        $this->assertDatabaseHas('discount_audit_logs', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'discount_amount' => 5000,
        ]);
    }

    public function test_enrolling_with_an_initial_payment_records_it(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $this->actingAs($director)->post("/students/{$student->id}/enroll", [
            'course_id' => $course->id,
            'amount_paid' => 20000,
            'payment_method' => 'cash',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('payments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 20000,
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);
    }

    public function test_a_course_the_student_is_already_enrolled_in_is_rejected(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 50000]);

        $response = $this->actingAs($director)->post("/students/{$student->id}/enroll", ['course_id' => $course->id]);

        $response->assertSessionHasErrors('course_id');
        $this->assertSame(1, $student->courses()->count());
    }

    public function test_the_student_page_shows_an_enroll_link_to_directors_only(): void
    {
        $director = User::factory()->director()->create();
        $secretary = User::factory()->secretary()->create();
        $student = Student::factory()->create();

        $this->actingAs($director)->get("/students/{$student->id}")->assertSee('Enroll in a Course');
        $this->actingAs($secretary)->get("/students/{$student->id}")->assertDontSee('Enroll in a Course');
    }
}
