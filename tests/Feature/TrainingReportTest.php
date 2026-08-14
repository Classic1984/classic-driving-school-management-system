<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_training_report_routes(): void
    {
        $this->get('/training-report')->assertRedirect('/login');
        $this->get('/training-report/export')->assertRedirect('/login');
        $this->get('/training-report/export-pdf')->assertRedirect('/login');
    }

    public function test_todays_report_only_shows_students_trained_today(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $instructor = Instructor::factory()->create(['name' => 'Mr. Adebayo']);
        $today = Student::factory()->create(['name' => 'Trained Today']);
        $yesterday = Student::factory()->create(['name' => 'Trained Yesterday']);
        $absent = Student::factory()->create(['name' => 'Marked Absent']);

        Attendance::factory()->create([
            'student_id' => $today->id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'date' => now()->toDateString(),
            'status' => 'present',
            'type' => 'practical',
            'duration' => 2,
        ]);
        Attendance::factory()->create([
            'student_id' => $yesterday->id,
            'course_id' => $course->id,
            'date' => now()->subDay()->toDateString(),
            'status' => 'present',
        ]);
        Attendance::factory()->create([
            'student_id' => $absent->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
            'status' => 'absent',
        ]);

        $response = $this->actingAs($user)->get('/training-report?period=today');

        $response->assertOk();
        $response->assertSee('Trained Today');
        $response->assertSee('Mr. Adebayo');
        $response->assertSee('practical');
        $response->assertDontSee('Trained Yesterday');
        $response->assertDontSee('Marked Absent');
    }

    public function test_weekly_report_includes_students_trained_earlier_in_the_week(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $student = Student::factory()->create(['name' => 'Weekly Student']);

        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->startOfWeek()->toDateString(),
            'status' => 'present',
        ]);

        $response = $this->actingAs($user)->get('/training-report?period=week');

        $response->assertOk();
        $response->assertSee('Weekly Student');
    }

    public function test_report_shows_the_students_current_training_status(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['duration_weeks' => 1]);
        $student = Student::factory()->create(['name' => 'Completed Student']);
        $student->courses()->attach($course->id, [
            'enrolled_at' => now(),
            'due_date' => now()->addDays(2),
            'status' => 'completed',
        ]);
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
            'status' => 'present',
        ]);

        $response = $this->actingAs($user)->get('/training-report?period=today');

        $response->assertOk();
        $response->assertSee('Completed Student');
        $response->assertSee('Completed');
    }

    public function test_students_trained_on_different_days_are_grouped_under_separate_date_headings(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $mondayStudent = Student::factory()->create(['name' => 'Monday Trainee']);
        $tuesdayStudent = Student::factory()->create(['name' => 'Tuesday Trainee']);

        $monday = now()->startOfWeek();
        $tuesday = $monday->copy()->addDay();

        Attendance::factory()->create([
            'student_id' => $mondayStudent->id,
            'course_id' => $course->id,
            'date' => $monday->toDateString(),
            'status' => 'present',
        ]);
        Attendance::factory()->create([
            'student_id' => $tuesdayStudent->id,
            'course_id' => $course->id,
            'date' => $tuesday->toDateString(),
            'status' => 'present',
        ]);

        $response = $this->actingAs($user)->get('/training-report?period=week');

        $response->assertOk();
        $response->assertSee($monday->format('l, j F Y'));
        $response->assertSee($tuesday->format('l, j F Y'));
        $response->assertSee('Monday Trainee');
        $response->assertSee('Tuesday Trainee');
    }

    public function test_an_invalid_period_falls_back_to_today(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $student = Student::factory()->create(['name' => 'Fallback Student']);
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
            'status' => 'present',
        ]);

        $response = $this->actingAs($user)->get('/training-report?period=not-a-real-period');

        $response->assertOk();
        $response->assertSee('Fallback Student');
    }

    public function test_authenticated_user_can_export_the_report_as_csv(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $student = Student::factory()->create(['name' => 'CSV Student']);
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
            'status' => 'present',
        ]);

        $response = $this->actingAs($user)->get('/training-report/export?period=today');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Student ID', $content);
        $this->assertStringContainsString('CSV Student', $content);
    }

    public function test_authenticated_user_can_download_the_report_as_a_pdf(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $student = Student::factory()->create(['name' => 'PDF Student']);
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
            'status' => 'present',
        ]);

        $response = $this->actingAs($user)->get('/training-report/export-pdf?period=today');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
