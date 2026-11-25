<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingCheckInTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_student_is_assigned_a_training_scan_token_on_creation(): void
    {
        $student = Student::factory()->create();

        $this->assertNotEmpty($student->training_scan_token);
    }

    public function test_scanning_a_students_code_logs_todays_training(): void
    {
        $course = Course::factory()->create();
        $student = Student::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 50000]);

        $response = $this->get($student->trainingCheckInUrl());

        $response->assertOk();
        $response->assertSee('Training Logged');
        $response->assertSee($student->name);
        $response->assertSee($course->name);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
            'status' => 'present',
        ]);
    }

    public function test_scanning_again_the_same_day_shows_already_logged_instead_of_a_duplicate(): void
    {
        $course = Course::factory()->create();
        $student = Student::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 50000]);

        $this->get($student->trainingCheckInUrl())->assertSee('Training Logged');
        $response = $this->get($student->trainingCheckInUrl());

        $response->assertOk();
        $response->assertSee('Already Logged');

        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_an_unrecognized_token_shows_invalid_code_instead_of_a_bare_404(): void
    {
        $response = $this->get('/public/training-check-in/not-a-real-token');

        $response->assertOk();
        $response->assertSee('Invalid Code');
    }

    public function test_a_student_with_no_active_enrollment_cannot_check_in(): void
    {
        $student = Student::factory()->create();

        $response = $this->get($student->trainingCheckInUrl());

        $response->assertOk();
        $response->assertSee('Cannot Log Training');
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_a_locked_students_check_in_explains_why(): void
    {
        $course = Course::factory()->create();
        $student = Student::factory()->create();
        $student->courses()->attach($course->id, [
            'enrolled_at' => now(),
            'status' => 'locked',
            'locked_reason' => 'overdue_balance',
            'fee' => 50000,
        ]);

        $response = $this->get($student->trainingCheckInUrl());

        $response->assertOk();
        $response->assertSee('Cannot Log Training');
        $response->assertSee('Overdue Balance');
    }

    public function test_a_student_enrolled_in_two_active_courses_is_asked_to_choose(): void
    {
        $courseA = Course::factory()->create(['name' => 'Course A']);
        $courseB = Course::factory()->create(['name' => 'Course B']);
        $student = Student::factory()->create();
        $student->courses()->attach($courseA->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 50000]);
        $student->courses()->attach($courseB->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 50000]);

        $response = $this->get($student->trainingCheckInUrl());

        $response->assertOk();
        $response->assertSee('Which course?');
        $response->assertSee('Course A');
        $response->assertSee('Course B');

        $this->assertDatabaseCount('attendances', 0);

        $this->get($student->trainingCheckInUrl().'?course_id='.$courseA->id)
            ->assertSee('Training Logged');

        $this->assertDatabaseHas('attendances', ['student_id' => $student->id, 'course_id' => $courseA->id]);
        $this->assertDatabaseMissing('attendances', ['student_id' => $student->id, 'course_id' => $courseB->id]);
    }

    public function test_checking_in_recalculates_the_enrollments_status(): void
    {
        // A course whose training period has already run out before today's
        // scan - logging training is still allowed to happen (it already
        // happened at the school), but reconcile() should immediately lock
        // the enrollment afterward rather than leaving it stale as active
        // until the next scheduled refresh.
        $course = Course::factory()->create();
        $student = Student::factory()->create(['enrollment_date' => now()->subMonths(3)]);
        $student->courses()->attach($course->id, [
            'enrolled_at' => now()->subMonths(3),
            'status' => 'active',
            'fee' => 50000,
        ]);

        $this->get($student->trainingCheckInUrl())->assertSee('Training Logged');

        $enrollment = $student->courses()->first()->pivot;
        $this->assertSame('locked', $enrollment->status);
        $this->assertSame('training_period_expired', $enrollment->locked_reason);
    }

    public function test_a_director_can_regenerate_a_students_scan_code(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        $oldToken = $student->training_scan_token;

        $this->actingAs($director)->post(route('students.scan-code.regenerate-token', $student))
            ->assertRedirect();

        $student->refresh();
        $this->assertNotSame($oldToken, $student->training_scan_token);

        // The old code is dead on arrival.
        $this->get('/public/training-check-in/'.$oldToken)->assertSee('Invalid Code');
    }

    public function test_a_secretary_cannot_regenerate_a_students_scan_code(): void
    {
        $secretary = User::factory()->secretary()->create();
        $student = Student::factory()->create();

        $this->actingAs($secretary)->post(route('students.scan-code.regenerate-token', $student))
            ->assertForbidden();
    }

    public function test_any_staff_role_can_view_and_download_the_scan_card(): void
    {
        $secretary = User::factory()->secretary()->create();
        $student = Student::factory()->create();

        $this->actingAs($secretary)->get(route('students.scan-code', $student))
            ->assertOk()
            ->assertSee($student->name);

        $this->actingAs($secretary)->get(route('students.scan-code.pdf', $student))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_guests_cannot_view_the_scan_card(): void
    {
        $student = Student::factory()->create();

        $this->get(route('students.scan-code', $student))->assertRedirect('/login');
    }
}
