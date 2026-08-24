<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_attendance_routes(): void
    {
        $attendance = Attendance::factory()->create();

        $this->get('/attendances')->assertRedirect('/login');
        $this->get('/attendances/create')->assertRedirect('/login');
        $this->get("/attendances/{$attendance->id}")->assertRedirect('/login');
        $this->get("/attendances/{$attendance->id}/edit")->assertRedirect('/login');
        $this->post('/attendances', [])->assertRedirect('/login');
        $this->put("/attendances/{$attendance->id}", [])->assertRedirect('/login');
        $this->delete("/attendances/{$attendance->id}")->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_attendance_index(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Jane Learner']);
        Attendance::factory()->create(['student_id' => $student->id]);

        $response = $this->actingAs($user)->get('/attendances');

        $response->assertOk();
        $response->assertSee('Jane Learner');
    }

    public function test_authenticated_user_can_view_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendances/create');

        $response->assertOk();
    }

    public function test_authenticated_user_can_store_an_attendance_record(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $instructor = Instructor::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $data = [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'date' => '2026-01-15',
            'status' => 'present',
            'type' => 'practical',
            'duration' => 2,
            'notes' => 'Practiced parallel parking.',
        ];

        $response = $this->actingAs($user)->post('/attendances', $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/attendances');

        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => '2026-01-15',
            'status' => 'present',
            'type' => 'practical',
            'duration' => 2,
            'logged_by' => $user->id,
        ]);
    }

    public function test_storing_an_attendance_record_requires_the_student_to_be_enrolled_in_the_course(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();

        $response = $this->actingAs($user)->post('/attendances', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
            'status' => 'present',
        ]);

        $response->assertSessionHasErrors('student_id');
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_storing_an_attendance_record_is_rejected_for_a_locked_enrollment(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['fee' => 100]);
        $student->courses()->attach($course->id, [
            'enrolled_at' => now()->subDays(10)->toDateString(),
            'due_date' => now()->subDays(6)->toDateString(),
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post('/attendances', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
            'status' => 'present',
        ]);

        $response->assertSessionHasErrors('student_id');
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_logging_training_from_the_student_profile_redirects_back_to_the_profile(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($user)->post('/attendances', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
            'status' => 'present',
            'type' => 'classroom',
            'redirect_to_student' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('students.show', $student));
        $this->assertDatabaseHas('attendances', ['student_id' => $student->id, 'type' => 'classroom']);
    }

    public function test_storing_an_attendance_record_requires_valid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/attendances', [
            'student_id' => '',
            'course_id' => '',
            'date' => '',
            'status' => 'invalid-status',
        ]);

        $response->assertSessionHasErrors(['student_id', 'course_id', 'date', 'status']);
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_storing_an_attendance_record_rejects_a_duration_outside_the_allowed_weekend_values(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($user)->post('/attendances', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
            'status' => 'present',
            'duration' => 4,
        ]);

        $response->assertSessionHasErrors('duration');
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_storing_a_duplicate_attendance_record_for_the_same_student_course_and_date_fails(): void
    {
        $user = User::factory()->create();
        $existing = Attendance::factory()->create(['date' => '2026-01-15']);

        $response = $this->actingAs($user)->post('/attendances', [
            'student_id' => $existing->student_id,
            'course_id' => $existing->course_id,
            'date' => '2026-01-15',
            'status' => 'absent',
        ]);

        $response->assertSessionHasErrors([
            'student_id' => 'An attendance record already exists for this student, course, and date.',
        ]);
        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_storing_a_duplicate_attendance_record_for_today_shows_a_friendly_named_warning(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'John Doe']);
        $existing = Attendance::factory()->create([
            'student_id' => $student->id,
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->post('/attendances', [
            'student_id' => $existing->student_id,
            'course_id' => $existing->course_id,
            'date' => now()->toDateString(),
            'status' => 'present',
        ]);

        $response->assertSessionHasErrors([
            'student_id' => 'John Doe has already logged training today.',
        ]);
        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_storing_an_attendance_record_captures_the_vehicle_used(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($user)->post('/attendances', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'vehicle_id' => $vehicle->id,
            'date' => now()->toDateString(),
            'status' => 'present',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'vehicle_id' => $vehicle->id,
        ]);
    }

    public function test_authenticated_user_can_view_an_attendance_record(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create();

        $response = $this->actingAs($user)->get("/attendances/{$attendance->id}");

        $response->assertOk();
        $response->assertSee($attendance->student->name);
    }

    public function test_authenticated_user_can_update_an_attendance_record(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['status' => 'present']);

        $response = $this->actingAs($user)->put("/attendances/{$attendance->id}", [
            'student_id' => $attendance->student_id,
            'course_id' => $attendance->course_id,
            'instructor_id' => $attendance->instructor_id,
            'date' => $attendance->date->format('Y-m-d'),
            'status' => 'absent',
            'notes' => 'Rescheduled.',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/attendances');

        $this->assertSame('absent', $attendance->fresh()->status);
    }

    public function test_editing_an_attendance_record_from_a_students_profile_redirects_back_there(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['status' => 'present']);

        $response = $this->actingAs($user)->put("/attendances/{$attendance->id}", [
            'student_id' => $attendance->student_id,
            'course_id' => $attendance->course_id,
            'instructor_id' => $attendance->instructor_id,
            'date' => $attendance->date->format('Y-m-d'),
            'status' => 'absent',
            'redirect_to' => 'student',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('students.show', $attendance->student_id));
        $response->assertSessionHas('status', 'attendance-updated');
    }

    public function test_editing_an_attendance_record_from_the_training_record_page_redirects_back_there(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['status' => 'present']);

        $response = $this->actingAs($user)->put("/attendances/{$attendance->id}", [
            'student_id' => $attendance->student_id,
            'course_id' => $attendance->course_id,
            'instructor_id' => $attendance->instructor_id,
            'date' => $attendance->date->format('Y-m-d'),
            'status' => 'absent',
            'redirect_to' => 'training_record',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('students.training-record', $attendance->student_id));
        $response->assertSessionHas('status', 'attendance-updated');
    }

    public function test_editing_an_attendance_record_can_change_the_vehicle_used(): void
    {
        $user = User::factory()->create();
        $originalVehicle = Vehicle::factory()->create(['status' => 'active']);
        $newVehicle = Vehicle::factory()->create(['status' => 'active']);
        $attendance = Attendance::factory()->create(['vehicle_id' => $originalVehicle->id]);

        $response = $this->actingAs($user)->put("/attendances/{$attendance->id}", [
            'student_id' => $attendance->student_id,
            'course_id' => $attendance->course_id,
            'date' => $attendance->date->format('Y-m-d'),
            'status' => $attendance->status,
            'vehicle_id' => $newVehicle->id,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame($newVehicle->id, $attendance->fresh()->vehicle_id);
    }

    public function test_authenticated_user_can_delete_an_attendance_record(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create();

        $response = $this->actingAs($user)->delete("/attendances/{$attendance->id}");

        $response->assertRedirect('/attendances');
        $this->assertDatabaseMissing('attendances', ['id' => $attendance->id]);
    }
}
