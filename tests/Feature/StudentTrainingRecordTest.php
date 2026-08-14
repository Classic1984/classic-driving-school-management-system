<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentTrainingRecordTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $student = Student::factory()->create();

        $this->get("/students/{$student->id}/training-record")->assertRedirect('/login');
    }

    public function test_it_shows_the_students_own_training_history_without_payment_details(): void
    {
        $user = User::factory()->create(['name' => 'Secretary Ade']);
        $course = Course::factory()->create(['fee' => 95000, 'name' => 'Defensive Driving Course']);
        $student = Student::factory()->create(['name' => 'Chidinma Eze']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        Payment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id, 'amount' => 40000, 'status' => 'paid']);
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now(),
            'status' => 'present',
            'type' => 'practical',
            'duration' => 2,
            'logged_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get("/students/{$student->id}/training-record");

        $response->assertOk();
        $response->assertSee('Chidinma Eze');
        $response->assertSee('Defensive Driving Course');
        $response->assertSee('practical');
        $response->assertSee('Secretary Ade');
        // No balance, payment, or fee details on this page.
        $response->assertDontSee('Balance');
        $response->assertDontSee('95,000');
        $response->assertDontSee('40,000');
    }

    public function test_the_training_log_forms_course_and_type_fields_default_automatically(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['name' => 'Weekend Program']);
        $student = Student::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($user)->get("/students/{$student->id}/training-record");

        $response->assertOk();
        $response->assertSee("<option value=\"{$course->id}\" selected>Weekend Program</option>", false);
        $response->assertSee('<option value="practical" selected>Practical</option>', false);
    }

    public function test_the_training_log_form_offers_active_vehicles_to_choose_from(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $student = Student::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);
        Vehicle::factory()->create(['name' => 'Toyota Corolla', 'plate_number' => 'ABC-123XY', 'status' => 'active']);
        Vehicle::factory()->create(['name' => 'Retired Van', 'plate_number' => 'OLD-999ZZ', 'status' => 'inactive']);

        $response = $this->actingAs($user)->get("/students/{$student->id}/training-record");

        $response->assertOk();
        $response->assertSee('Toyota Corolla (ABC-123XY)');
        $response->assertDontSee('Retired Van');
    }

    public function test_logging_a_second_training_session_for_the_same_student_today_shows_a_friendly_warning(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $student = Student::factory()->create(['name' => 'John Doe']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
        ]);

        $this->actingAs($user)->get("/students/{$student->id}/training-record");

        $response = $this->actingAs($user)->post('/attendances', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
            'status' => 'present',
            'redirect_to_training_record' => '1',
        ]);

        $response->assertSessionHasErrors([
            'student_id' => 'John Doe has already logged training today.',
        ]);
        $response->assertRedirect(route('students.training-record', $student));
    }

    public function test_logging_training_from_the_record_page_records_who_logged_it_and_redirects_back(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $student = Student::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($user)->post('/attendances', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
            'status' => 'present',
            'type' => 'classroom',
            'redirect_to_training_record' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('students.training-record', $student));

        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'type' => 'classroom',
            'logged_by' => $user->id,
        ]);
    }
}
