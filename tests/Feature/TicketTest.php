<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    protected function enrollStudent(Student $student, Course $course, string $status = 'active'): void
    {
        // Locking is always recomputed live from real conditions (see
        // Enrollment::refreshStatus()), so an actually-overdue enrollment
        // needs a due date in the past rather than just the string 'locked'.
        $isLocked = $status === 'locked';

        $course->students()->attach($student->id, [
            'enrolled_at' => $isLocked ? now()->subDays(10)->toDateString() : now()->toDateString(),
            'due_date' => $isLocked ? now()->subDays(6)->toDateString() : now()->addDays($course->gracePeriodDays())->toDateString(),
            'status' => $status,
            'locked_reason' => $isLocked ? 'overdue_balance' : null,
        ]);
    }

    public function test_guests_are_redirected_to_login_from_ticket_routes(): void
    {
        $ticket = Ticket::factory()->create();

        $this->get('/tickets')->assertRedirect('/login');
        $this->get('/tickets/create')->assertRedirect('/login');
        $this->get("/tickets/{$ticket->id}")->assertRedirect('/login');
        $this->get("/tickets/{$ticket->id}/edit")->assertRedirect('/login');
        $this->post('/tickets', [])->assertRedirect('/login');
        $this->put("/tickets/{$ticket->id}", [])->assertRedirect('/login');
        $this->delete("/tickets/{$ticket->id}")->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_ticket_index(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create();

        $response = $this->actingAs($user)->get('/tickets');

        $response->assertOk();
        $response->assertSee($ticket->ticket_number);
    }

    public function test_authenticated_user_can_view_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/tickets/create');

        $response->assertOk();
    }

    public function test_ticket_can_be_issued_for_an_active_enrollment(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $instructor = Instructor::factory()->create();
        $this->enrollStudent($student, $course);

        $response = $this->actingAs($user)->post('/tickets', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'date' => now()->toDateString(),
            'vehicle' => 'Toyota Corolla',
            'vehicle_number' => 'AK-234-XY',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/tickets');

        $ticket = Ticket::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();
        $this->assertStringStartsWith($student->fresh()->student_id_number, $ticket->ticket_number);
        $this->assertSame('cleared', $ticket->payment_status);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'date' => now()->toDateString(),
            'status' => 'present',
        ]);
    }

    public function test_issuing_a_ticket_does_not_duplicate_an_existing_attendance_record(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $this->enrollStudent($student, $course);
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
            'status' => 'late',
        ]);

        $this->actingAs($user)->post('/tickets', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('attendances', 1);
        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'late',
        ]);
    }

    public function test_ticket_cannot_be_issued_for_a_locked_enrollment(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $this->enrollStudent($student, $course, 'locked');

        $response = $this->actingAs($user)->post('/tickets', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('student_id');
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_ticket_cannot_be_issued_for_a_student_not_enrolled_in_the_course(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();

        $response = $this->actingAs($user)->post('/tickets', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('student_id');
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_only_one_ticket_per_student_course_and_day_is_allowed(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $this->enrollStudent($student, $course);

        Ticket::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->post('/tickets', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('student_id');
        $this->assertDatabaseCount('tickets', 1);
    }

    public function test_authenticated_user_can_view_a_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['vehicle_number' => 'AK-234-XY']);

        $response = $this->actingAs($user)->get("/tickets/{$ticket->id}");

        $response->assertOk();
        $response->assertSee($ticket->ticket_number);
        $response->assertSee($ticket->student->student_id_number);
        $response->assertSee('AK-234-XY');
    }

    public function test_create_form_shows_each_students_id_number(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Mr. Wellington']);

        $response = $this->actingAs($user)->get('/tickets/create');

        $response->assertOk();
        $response->assertSee($student->student_id_number);
    }

    public function test_admin_and_secretary_cannot_delete_a_ticket_but_director_can(): void
    {
        $admin = User::factory()->admin()->create();
        $secretary = User::factory()->secretary()->create();
        $director = User::factory()->director()->create();
        $ticket = Ticket::factory()->create();

        $this->actingAs($admin)->delete("/tickets/{$ticket->id}")->assertForbidden();
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id]);

        $this->actingAs($secretary)->delete("/tickets/{$ticket->id}")->assertForbidden();
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id]);

        $this->actingAs($director)->delete("/tickets/{$ticket->id}")->assertRedirect('/tickets');
        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
    }
}
