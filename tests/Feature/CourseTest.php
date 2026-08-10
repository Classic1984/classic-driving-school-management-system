<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_course_routes(): void
    {
        $course = Course::factory()->create();

        $this->get('/courses')->assertRedirect('/login');
        $this->get('/courses/create')->assertRedirect('/login');
        $this->get("/courses/{$course->id}")->assertRedirect('/login');
        $this->get("/courses/{$course->id}/edit")->assertRedirect('/login');
        $this->post('/courses', [])->assertRedirect('/login');
        $this->put("/courses/{$course->id}", [])->assertRedirect('/login');
        $this->delete("/courses/{$course->id}")->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_course_index(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['name' => 'Highway Mastery']);

        $response = $this->actingAs($user)->get('/courses');

        $response->assertOk();
        $response->assertSee('Highway Mastery');
    }

    public function test_authenticated_user_can_view_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/courses/create');

        $response->assertOk();
    }

    public function test_authenticated_user_can_store_a_course_with_instructors(): void
    {
        $user = User::factory()->create();
        $instructor = Instructor::factory()->create();

        $data = [
            'name' => 'Beginner Driving',
            'description' => 'An introductory course.',
            'course_type' => 'manual',
            'schedule' => 'weekday',
            'duration_hours' => 20,
            'duration_weeks' => 4,
            'fee' => 199.99,
            'status' => 'active',
            'instructors' => [$instructor->id],
        ];

        $response = $this->actingAs($user)->post('/courses', $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/courses');

        $this->assertDatabaseHas('courses', ['name' => 'Beginner Driving']);

        $course = Course::where('name', 'Beginner Driving')->firstOrFail();
        $this->assertTrue($course->instructors->contains($instructor));
    }

    public function test_storing_a_course_requires_valid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/courses', [
            'name' => '',
            'course_type' => 'invalid-type',
            'schedule' => 'invalid-schedule',
            'duration_hours' => 0,
            'duration_weeks' => 0,
            'fee' => -10,
            'status' => 'invalid-status',
        ]);

        $response->assertSessionHasErrors([
            'name', 'course_type', 'schedule', 'duration_hours', 'duration_weeks', 'fee', 'status',
        ]);

        $this->assertDatabaseCount('courses', 0);
    }

    public function test_authenticated_user_can_view_a_course(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $response = $this->actingAs($user)->get("/courses/{$course->id}");

        $response->assertOk();
        $response->assertSee($course->name);
    }

    public function test_course_page_shows_why_a_locked_enrollment_is_locked(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['fee' => 100]);
        $student->courses()->attach($course->id, [
            'enrolled_at' => now()->subDays(10),
            'due_date' => now()->subDays(6),
            'status' => 'locked',
            'locked_reason' => 'overdue_balance',
        ]);

        $response = $this->actingAs($user)->get("/courses/{$course->id}");

        $response->assertOk();
        $response->assertSee('Overdue Balance');
    }

    public function test_authenticated_user_can_update_a_course_and_its_instructors(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['name' => 'Old Course Name']);
        $oldInstructor = Instructor::factory()->create();
        $newInstructor = Instructor::factory()->create();
        $course->instructors()->attach($oldInstructor);

        $response = $this->actingAs($user)->put("/courses/{$course->id}", [
            'name' => 'New Course Name',
            'description' => $course->description,
            'course_type' => $course->course_type,
            'schedule' => $course->schedule,
            'duration_hours' => $course->duration_hours,
            'duration_weeks' => $course->duration_weeks,
            'fee' => $course->fee,
            'status' => 'inactive',
            'instructors' => [$newInstructor->id],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/courses');

        $course->refresh();
        $this->assertSame('New Course Name', $course->name);
        $this->assertSame('inactive', $course->status);
        $this->assertTrue($course->instructors->contains($newInstructor));
        $this->assertFalse($course->instructors->contains($oldInstructor));
    }

    public function test_authenticated_user_can_store_a_course_with_students(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();

        $data = [
            'name' => 'Beginner Driving',
            'description' => 'An introductory course.',
            'course_type' => 'manual',
            'schedule' => 'weekday',
            'duration_hours' => 20,
            'duration_weeks' => 4,
            'fee' => 199.99,
            'status' => 'active',
            'students' => [$student->id],
        ];

        $response = $this->actingAs($user)->post('/courses', $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/courses');

        $course = Course::where('name', 'Beginner Driving')->firstOrFail();
        $this->assertTrue($course->students->contains($student));
    }

    public function test_enrolling_a_student_from_the_course_roster_sets_the_due_date_from_the_grace_period(): void
    {
        // Grace period is baked into due_date once, at enrollment time, the
        // same way the fee is - not re-derived from the course on every
        // overdue check - so this confirms the roster-attach path (as
        // opposed to the student registration form) applies it correctly.
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['schedule' => 'weekend', 'duration_weeks' => 4]);

        $response = $this->actingAs($user)->put("/courses/{$course->id}", [
            'name' => $course->name,
            'description' => $course->description,
            'course_type' => $course->course_type,
            'schedule' => $course->schedule,
            'duration_hours' => $course->duration_hours,
            'duration_weeks' => $course->duration_weeks,
            'fee' => $course->fee,
            'status' => $course->status,
            'students' => [$student->id],
        ]);

        $response->assertSessionHasNoErrors();

        $enrollment = Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();
        $this->assertSame($course->gracePeriodDays(), 7);
        $this->assertSame(now()->addDays(7)->toDateString(), $enrollment->due_date->toDateString());
    }

    public function test_authenticated_user_can_update_a_course_and_its_students(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $oldStudent = Student::factory()->create();
        $newStudent = Student::factory()->create();
        $course->students()->attach($oldStudent);

        $response = $this->actingAs($user)->put("/courses/{$course->id}", [
            'name' => $course->name,
            'description' => $course->description,
            'course_type' => $course->course_type,
            'schedule' => $course->schedule,
            'duration_hours' => $course->duration_hours,
            'duration_weeks' => $course->duration_weeks,
            'fee' => $course->fee,
            'status' => $course->status,
            'students' => [$newStudent->id],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/courses');

        $course->refresh();
        $this->assertTrue($course->students->contains($newStudent));
        $this->assertFalse($course->students->contains($oldStudent));
    }

    public function test_raising_a_courses_fee_does_not_affect_students_already_enrolled(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 50000]);
        $existingStudent = Student::factory()->create();
        $course->students()->attach($existingStudent->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 50000]);

        $this->actingAs($user)->put("/courses/{$course->id}", [
            'name' => $course->name,
            'description' => $course->description,
            'course_type' => $course->course_type,
            'schedule' => $course->schedule,
            'duration_hours' => $course->duration_hours,
            'duration_weeks' => $course->duration_weeks,
            'fee' => 75000,
            'status' => $course->status,
            'students' => [$existingStudent->id],
        ])->assertSessionHasNoErrors();

        $existingEnrollment = $existingStudent->courses()->first()->pivot;
        $this->assertSame(50000.0, $existingEnrollment->fee());
        $this->assertSame(50000.0, $existingEnrollment->balance());

        $newStudent = Student::factory()->create();
        $course->students()->attach($newStudent->id, [
            'enrolled_at' => now(),
            'status' => 'active',
            'fee' => $course->fresh()->fee,
        ]);

        $newEnrollment = $newStudent->courses()->first()->pivot;
        $this->assertSame(75000.0, $newEnrollment->fee());
    }

    public function test_authenticated_user_can_delete_a_course(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $response = $this->actingAs($user)->delete("/courses/{$course->id}");

        $response->assertRedirect('/courses');
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }

    public function test_increasing_a_courses_required_days_reverts_an_enrollment_that_no_longer_qualifies(): void
    {
        // A student completed a 3-week (15-day) programme in full. The
        // Director then edits the course to 4 weeks (20 days) - the
        // student's 15 attended days no longer meet the new requirement,
        // so their "Completed" status must be reverted, not left stale.
        $user = User::factory()->create();
        $course = Course::factory()->create(['duration_weeks' => 3, 'fee' => 100]);
        $student = Student::factory()->create();
        $course->students()->attach($student->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 100]);
        Payment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id, 'amount' => 100, 'status' => 'paid']);
        for ($day = 1; $day <= 15; $day++) {
            Attendance::factory()->create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'date' => now()->addDays($day)->toDateString(),
                'status' => 'present',
                'duration' => 1,
            ]);
        }
        $enrollment = Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();
        $enrollment->reconcile();
        $this->assertSame('completed', $enrollment->fresh()->status);

        $this->actingAs($user)->put("/courses/{$course->id}", [
            'name' => $course->name,
            'description' => $course->description,
            'course_type' => $course->course_type,
            'schedule' => $course->schedule,
            'duration_hours' => $course->duration_hours,
            'duration_weeks' => 4,
            'fee' => $course->fee,
            'status' => $course->status,
            'students' => [$student->id],
        ])->assertSessionHasNoErrors();

        $this->assertNotSame('completed', $enrollment->fresh()->status);
        $this->assertSame(15, $enrollment->fresh()->attendedDays());
        $this->assertSame(20, $course->fresh()->totalTrainingDays());
    }
}
