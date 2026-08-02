<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentCompletionTest extends TestCase
{
    use RefreshDatabase;

    protected function enroll(Student $student, Course $course): Enrollment
    {
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->toDateString(),
            'due_date' => now()->addDays($course->gracePeriodDays())->toDateString(),
            'status' => 'active',
        ]);

        return Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();
    }

    public function test_guests_are_redirected_to_login_from_the_complete_route(): void
    {
        $course = Course::factory()->create();
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course);

        $this->patch("/enrollments/{$enrollment->id}/complete")->assertRedirect('/login');
    }

    public function test_marking_complete_fails_with_an_outstanding_balance(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 100]);
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course);

        $response = $this->actingAs($user)->patch("/enrollments/{$enrollment->id}/complete");

        $response->assertSessionHasErrors('enrollment');
        $this->assertSame('active', $enrollment->fresh()->status);
    }

    public function test_marking_complete_succeeds_once_balance_is_cleared(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 100]);
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course);
        Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 100,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($user)->patch("/enrollments/{$enrollment->id}/complete");

        $response->assertSessionHasNoErrors();
        $this->assertSame('completed', $enrollment->fresh()->status);
        $this->assertNull($enrollment->fresh()->locked_reason);
    }

    public function test_completed_enrollments_stay_completed_even_when_overdue_conditions_are_recomputed(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 100]);
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course);
        Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 100,
            'status' => 'paid',
        ]);
        $this->actingAs($user)->patch("/enrollments/{$enrollment->id}/complete");

        $enrollment->fresh()->refreshStatus();

        $this->assertSame('completed', $enrollment->fresh()->status);
    }

    public function test_pivot_exposes_the_enrollment_id_for_the_complete_route(): void
    {
        // Regression test: withPivot() must include 'id', or the student/course
        // show pages' "Mark Complete" button (which builds its URL from
        // $enrolledCourse->pivot->id) silently generates a broken route.
        $course = Course::factory()->create();
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course);

        $viaCourse = $course->students()->first()->pivot->id;
        $viaStudent = $student->courses()->first()->pivot->id;

        $this->assertSame($enrollment->id, $viaCourse);
        $this->assertSame($enrollment->id, $viaStudent);
    }

    public function test_staff_can_mark_an_enrollment_complete(): void
    {
        $staff = User::factory()->staff()->create();
        $course = Course::factory()->create(['fee' => 100]);
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course);
        Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 100,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($staff)->patch("/enrollments/{$enrollment->id}/complete");

        $response->assertSessionHasNoErrors();
        $this->assertSame('completed', $enrollment->fresh()->status);
    }
}
