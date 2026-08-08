<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
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
            'session' => 'morning',
            'vehicle' => 'Toyota Corolla',
            'logged_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get("/students/{$student->id}/training-record");

        $response->assertOk();
        $response->assertSee('Chidinma Eze');
        $response->assertSee('Defensive Driving Course');
        $response->assertSee('morning');
        $response->assertSee('Toyota Corolla');
        $response->assertSee('Secretary Ade');
        // No balance, payment, or fee details on this page.
        $response->assertDontSee('Balance');
        $response->assertDontSee('95,000');
        $response->assertDontSee('40,000');
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
            'session' => 'evening',
            'redirect_to_training_record' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('students.training-record', $student));

        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'session' => 'evening',
            'logged_by' => $user->id,
        ]);
    }
}
