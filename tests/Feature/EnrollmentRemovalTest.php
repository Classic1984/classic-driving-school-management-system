<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentRemovalTest extends TestCase
{
    use RefreshDatabase;

    protected function enrollStudent(Student $student, Course $course): Enrollment
    {
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => $course->fee]);

        return Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $enrollment = $this->enrollStudent($student, $course);

        $this->delete("/enrollments/{$enrollment->id}")->assertRedirect('/login');
    }

    public function test_a_secretary_cannot_remove_an_enrollment(): void
    {
        $secretary = User::factory()->secretary()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $enrollment = $this->enrollStudent($student, $course);

        $this->actingAs($secretary)->delete("/enrollments/{$enrollment->id}")->assertForbidden();
        $this->assertDatabaseHas('course_student', ['id' => $enrollment->id]);
    }

    public function test_an_admin_cannot_remove_an_enrollment(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $enrollment = $this->enrollStudent($student, $course);

        $this->actingAs($admin)->delete("/enrollments/{$enrollment->id}")->assertForbidden();
    }

    public function test_a_director_can_remove_an_unpaid_untrained_enrollment(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create(['name' => 'Jane Doe']);
        $course = Course::factory()->create(['name' => 'Duplicate Program']);
        $enrollment = $this->enrollStudent($student, $course);

        $response = $this->actingAs($director)->delete("/enrollments/{$enrollment->id}");

        $response->assertRedirect(route('students.show', $student));
        $this->assertDatabaseMissing('course_student', ['id' => $enrollment->id]);
        $this->assertDatabaseHas('activity_logs', [
            'description' => "Removed Jane Doe's enrollment in Duplicate Program (no payments or training recorded)",
        ]);
    }

    public function test_it_refuses_to_remove_an_enrollment_with_a_payment_recorded(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['fee' => 50000]);
        $enrollment = $this->enrollStudent($student, $course);
        $payment = Payment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id, 'status' => 'paid']);
        PaymentAllocation::factory()->create([
            'payment_id' => $payment->id,
            'allocation_type' => 'training',
            'enrollment_id' => $enrollment->id,
            'amount' => 20000,
        ]);

        $response = $this->actingAs($director)->delete("/enrollments/{$enrollment->id}");

        $response->assertSessionHasErrors('enrollment');
        $this->assertDatabaseHas('course_student', ['id' => $enrollment->id]);
    }

    public function test_it_refuses_to_remove_an_enrollment_with_training_already_logged(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $enrollment = $this->enrollStudent($student, $course);
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'present',
        ]);

        $response = $this->actingAs($director)->delete("/enrollments/{$enrollment->id}");

        $response->assertSessionHasErrors('enrollment');
        $this->assertDatabaseHas('course_student', ['id' => $enrollment->id]);
    }

    public function test_the_remove_link_only_shows_to_directors_for_an_unpaid_enrollment(): void
    {
        $director = User::factory()->director()->create();
        $secretary = User::factory()->secretary()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['fee' => 50000]);
        $this->enrollStudent($student, $course);

        $this->actingAs($director)->get("/students/{$student->id}")->assertSee('Remove');
        $this->actingAs($secretary)->get("/students/{$student->id}")->assertDontSee('Remove');
    }

    public function test_the_remove_link_is_hidden_once_a_payment_has_been_made(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['fee' => 50000]);
        $enrollment = $this->enrollStudent($student, $course);
        $payment = Payment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id, 'status' => 'paid']);
        PaymentAllocation::factory()->create([
            'payment_id' => $payment->id,
            'allocation_type' => 'training',
            'enrollment_id' => $enrollment->id,
            'amount' => 20000,
        ]);

        $this->actingAs($director)->get("/students/{$student->id}")->assertDontSee('Remove');
    }
}
