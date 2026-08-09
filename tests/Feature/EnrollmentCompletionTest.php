<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Certificate;
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

    public function test_marking_complete_manually_automatically_issues_a_certificate(): void
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

        $this->actingAs($user)->patch("/enrollments/{$enrollment->id}/complete")->assertSessionHasNoErrors();

        $this->assertDatabaseHas('certificates', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'certificate_number' => Certificate::numberFor($student, $course),
        ]);
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

    public function test_secretary_can_mark_an_enrollment_complete(): void
    {
        $secretary = User::factory()->secretary()->create();
        $course = Course::factory()->create(['fee' => 100]);
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course);
        Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 100,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($secretary)->patch("/enrollments/{$enrollment->id}/complete");

        $response->assertSessionHasNoErrors();
        $this->assertSame('completed', $enrollment->fresh()->status);
    }

    public function test_enrollment_auto_completes_once_attendance_and_balance_are_both_cleared(): void
    {
        $course = Course::factory()->create(['fee' => 100, 'duration_weeks' => 1]);
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course);
        Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 100,
            'status' => 'paid',
        ]);

        // A 1-week course requires 5 present training days.
        for ($day = 1; $day <= 5; $day++) {
            Attendance::factory()->create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'date' => now()->addDays($day)->toDateString(),
                'status' => 'present',
                'duration' => 1,
            ]);
        }

        $enrollment->refreshStatus();

        $this->assertSame('completed', $enrollment->fresh()->status);
        $this->assertNull($enrollment->fresh()->locked_reason);
        $this->assertDatabaseHas('certificates', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'certificate_number' => Certificate::numberFor($student, $course),
        ]);
    }

    public function test_auto_completing_an_enrollment_twice_does_not_duplicate_the_certificate(): void
    {
        $course = Course::factory()->create(['fee' => 100, 'duration_weeks' => 1]);
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course);
        Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 100,
            'status' => 'paid',
        ]);

        for ($day = 1; $day <= 5; $day++) {
            Attendance::factory()->create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'date' => now()->addDays($day)->toDateString(),
                'status' => 'present',
                'duration' => 1,
            ]);
        }

        $enrollment->refreshStatus();
        $enrollment->fresh()->refreshStatus();

        $this->assertDatabaseCount('certificates', 1);
    }

    public function test_enrollment_does_not_auto_complete_with_an_outstanding_balance_even_if_attendance_is_done(): void
    {
        $course = Course::factory()->create(['fee' => 100, 'duration_weeks' => 1]);
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course);

        for ($day = 1; $day <= 5; $day++) {
            Attendance::factory()->create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'date' => now()->addDays($day)->toDateString(),
                'status' => 'present',
                'duration' => 1,
            ]);
        }

        $enrollment->refreshStatus();

        $this->assertNotSame('completed', $enrollment->fresh()->status);
    }

    public function test_enrollment_does_not_auto_complete_until_attendance_is_exhausted(): void
    {
        $course = Course::factory()->create(['fee' => 100, 'duration_weeks' => 1]);
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course);
        Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 100,
            'status' => 'paid',
        ]);

        // Only 3 of the 5 required training days attended.
        for ($day = 1; $day <= 3; $day++) {
            Attendance::factory()->create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'date' => now()->addDays($day)->toDateString(),
                'status' => 'present',
                'duration' => 1,
            ]);
        }

        $enrollment->refreshStatus();

        $this->assertNotSame('completed', $enrollment->fresh()->status);
    }

    public function test_a_weekend_students_saturday_and_sunday_logins_count_for_five_days(): void
    {
        // A weekend-only student trains Saturday and Sunday, but each of
        // those single logins counts for multiple training days (2 and 3
        // respectively) so a full training week is covered in one weekend.
        $course = Course::factory()->create(['fee' => 100, 'duration_weeks' => 1]);
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course);
        Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 100,
            'status' => 'paid',
        ]);

        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->addDays(1)->toDateString(),
            'status' => 'present',
            'duration' => 2,
        ]);

        $enrollment->refreshStatus();
        $this->assertSame(2, $enrollment->fresh()->attendedDays());
        $this->assertNotSame('completed', $enrollment->fresh()->status);

        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->addDays(2)->toDateString(),
            'status' => 'present',
            'duration' => 3,
        ]);

        $enrollment->refreshStatus();

        $this->assertSame(5, $enrollment->fresh()->attendedDays());
        $this->assertSame('completed', $enrollment->fresh()->status);
    }

    public function test_logging_the_final_training_day_auto_completes_the_enrollment(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 100, 'duration_weeks' => 1]);
        $student = Student::factory()->create();
        $enrollment = $this->enroll($student, $course);
        Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 100,
            'status' => 'paid',
        ]);

        for ($day = 1; $day <= 4; $day++) {
            Attendance::factory()->create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'date' => now()->addDays($day)->toDateString(),
                'status' => 'present',
                'duration' => 1,
            ]);
        }

        $this->actingAs($user)->post('/attendances', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->addDays(5)->toDateString(),
            'status' => 'present',
        ])->assertSessionHasNoErrors();

        $this->assertSame('completed', $enrollment->fresh()->status);
    }

    public function test_the_students_own_status_automatically_becomes_completed_once_their_only_enrollment_completes(): void
    {
        $course = Course::factory()->create(['fee' => 100, 'duration_weeks' => 1]);
        $student = Student::factory()->create(['status' => 'active']);
        $enrollment = $this->enroll($student, $course);
        Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 100,
            'status' => 'paid',
        ]);

        for ($day = 1; $day <= 5; $day++) {
            Attendance::factory()->create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'date' => now()->addDays($day)->toDateString(),
                'status' => 'present',
                'duration' => 1,
            ]);
        }

        $enrollment->refreshStatus();

        $this->assertSame('completed', $student->fresh()->status);
    }

    public function test_a_students_status_stays_active_while_any_enrollment_is_incomplete(): void
    {
        $courseA = Course::factory()->create(['fee' => 100, 'duration_weeks' => 1]);
        $courseB = Course::factory()->create(['fee' => 100, 'duration_weeks' => 1]);
        $student = Student::factory()->create(['status' => 'active']);
        $enrollmentA = $this->enroll($student, $courseA);
        $this->enroll($student, $courseB);
        Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $courseA->id,
            'amount' => 100,
            'status' => 'paid',
        ]);

        for ($day = 1; $day <= 5; $day++) {
            Attendance::factory()->create([
                'student_id' => $student->id,
                'course_id' => $courseA->id,
                'date' => now()->addDays($day)->toDateString(),
                'status' => 'present',
                'duration' => 1,
            ]);
        }

        $enrollmentA->refreshStatus();

        $this->assertSame('completed', $enrollmentA->fresh()->status);
        $this->assertSame('active', $student->fresh()->status);
    }

    public function test_a_previously_completed_students_status_reopens_when_enrolled_in_a_new_course(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['status' => 'completed']);
        $newCourse = Course::factory()->create(['fee' => 100]);

        $this->actingAs($user)->put("/courses/{$newCourse->id}", [
            'name' => $newCourse->name,
            'description' => $newCourse->description,
            'course_type' => $newCourse->course_type,
            'schedule' => $newCourse->schedule,
            'duration_hours' => $newCourse->duration_hours,
            'duration_weeks' => $newCourse->duration_weeks,
            'fee' => $newCourse->fee,
            'status' => $newCourse->status,
            'students' => [$student->id],
        ])->assertSessionHasNoErrors();

        $this->assertSame('active', $student->fresh()->status);
    }

    public function test_a_withdrawn_students_status_is_never_overridden_by_completion_automation(): void
    {
        $course = Course::factory()->create(['fee' => 100, 'duration_weeks' => 1]);
        $student = Student::factory()->create(['status' => 'withdrawn']);
        $enrollment = $this->enroll($student, $course);
        Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 100,
            'status' => 'paid',
        ]);

        for ($day = 1; $day <= 5; $day++) {
            Attendance::factory()->create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'date' => now()->addDays($day)->toDateString(),
                'status' => 'present',
                'duration' => 1,
            ]);
        }

        $enrollment->refreshStatus();

        $this->assertSame('completed', $enrollment->fresh()->status);
        $this->assertSame('withdrawn', $student->fresh()->status);
    }
}
