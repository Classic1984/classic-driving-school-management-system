<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorActivityReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_instructor_activity_report_routes(): void
    {
        $this->get('/instructor-activity-report')->assertRedirect('/login');
        $this->get('/instructor-activity-report/export')->assertRedirect('/login');
        $this->get('/instructor-activity-report/export-pdf')->assertRedirect('/login');
    }

    public function test_todays_report_counts_sessions_conducted_today_per_instructor(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $instructor = Instructor::factory()->create(['name' => 'Mr. Adebayo']);
        $studentA = Student::factory()->create();
        $studentB = Student::factory()->create();
        $absentStudent = Student::factory()->create();
        $yesterdayStudent = Student::factory()->create();
        $noInstructorStudent = Student::factory()->create();

        Attendance::factory()->create([
            'student_id' => $studentA->id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'date' => now()->toDateString(),
            'status' => 'present',
            'duration' => 2,
        ]);
        Attendance::factory()->create([
            'student_id' => $studentB->id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'date' => now()->toDateString(),
            'status' => 'present',
            'duration' => 1,
        ]);
        // Not counted: absent status, an attendance from yesterday, and one with no instructor.
        Attendance::factory()->create([
            'student_id' => $absentStudent->id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'date' => now()->toDateString(),
            'status' => 'absent',
        ]);
        Attendance::factory()->create([
            'student_id' => $yesterdayStudent->id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'date' => now()->subDay()->toDateString(),
            'status' => 'present',
        ]);
        Attendance::factory()->create([
            'student_id' => $noInstructorStudent->id,
            'course_id' => $course->id,
            'instructor_id' => null,
            'date' => now()->toDateString(),
            'status' => 'present',
        ]);

        $response = $this->actingAs($user)->get('/instructor-activity-report?period=today');

        $response->assertOk();
        $response->assertSee('Mr. Adebayo');
        // 2 sessions, 3 training days (2 + 1), 2 distinct students.
        $response->assertSeeInOrder(['Mr. Adebayo', '2', '3', '2']);
    }

    public function test_an_instructor_with_no_activity_in_the_period_is_not_listed(): void
    {
        $user = User::factory()->create();
        Instructor::factory()->create(['name' => 'Idle Instructor']);

        $response = $this->actingAs($user)->get('/instructor-activity-report?period=today');

        $response->assertOk();
        $response->assertDontSee('Idle Instructor');
        $response->assertSee('No training was logged by an instructor during this period.');
    }

    public function test_weekly_report_includes_sessions_conducted_earlier_in_the_week(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $instructor = Instructor::factory()->create(['name' => 'Weekly Instructor']);
        $student = Student::factory()->create();

        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'date' => now()->startOfWeek()->toDateString(),
            'status' => 'present',
        ]);

        $response = $this->actingAs($user)->get('/instructor-activity-report?period=week');

        $response->assertOk();
        $response->assertSee('Weekly Instructor');
    }

    public function test_an_invalid_period_falls_back_to_today(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $instructor = Instructor::factory()->create(['name' => 'Fallback Instructor']);
        $student = Student::factory()->create();
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'date' => now()->toDateString(),
            'status' => 'present',
        ]);

        $response = $this->actingAs($user)->get('/instructor-activity-report?period=not-a-real-period');

        $response->assertOk();
        $response->assertSee('Fallback Instructor');
    }

    public function test_authenticated_user_can_export_the_report_as_csv(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $instructor = Instructor::factory()->create(['name' => 'CSV Instructor']);
        $student = Student::factory()->create();
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'date' => now()->toDateString(),
            'status' => 'present',
        ]);

        $response = $this->actingAs($user)->get('/instructor-activity-report/export?period=today');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Sessions Conducted', $content);
        $this->assertStringContainsString('CSV Instructor', $content);
    }

    public function test_authenticated_user_can_download_the_report_as_a_pdf(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $instructor = Instructor::factory()->create();
        $student = Student::factory()->create();
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'date' => now()->toDateString(),
            'status' => 'present',
        ]);

        $response = $this->actingAs($user)->get('/instructor-activity-report/export-pdf?period=today');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
