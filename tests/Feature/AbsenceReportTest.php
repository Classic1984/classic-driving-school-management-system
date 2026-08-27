<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsenceReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_absence_report_routes(): void
    {
        $this->get('/absence-report')->assertRedirect('/login');
        $this->get('/absence-report/export')->assertRedirect('/login');
        $this->get('/absence-report/export-pdf')->assertRedirect('/login');
    }

    public function test_todays_report_only_shows_students_absent_today(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['name' => 'Beginner Driving']);
        $today = Student::factory()->create(['name' => 'Absent Today']);
        $yesterday = Student::factory()->create(['name' => 'Absent Yesterday']);
        $present = Student::factory()->create(['name' => 'Present Today']);

        Attendance::factory()->create([
            'student_id' => $today->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
            'status' => 'absent',
        ]);
        Attendance::factory()->create([
            'student_id' => $yesterday->id,
            'course_id' => $course->id,
            'date' => now()->subDay()->toDateString(),
            'status' => 'absent',
        ]);
        Attendance::factory()->create([
            'student_id' => $present->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
            'status' => 'present',
        ]);

        $response = $this->actingAs($user)->get('/absence-report?period=today');

        $response->assertOk();
        $response->assertSee('Absent Today');
        $response->assertSee('Beginner Driving');
        $response->assertDontSee('Absent Yesterday');
        $response->assertDontSee('Present Today');
    }

    public function test_weekly_report_includes_students_absent_earlier_in_the_week(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $student = Student::factory()->create(['name' => 'Weekly Absentee']);

        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->startOfWeek()->toDateString(),
            'status' => 'absent',
        ]);

        $response = $this->actingAs($user)->get('/absence-report?period=week');

        $response->assertOk();
        $response->assertSee('Weekly Absentee');
    }

    public function test_students_absent_on_different_days_are_grouped_under_separate_date_headings(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $mondayStudent = Student::factory()->create(['name' => 'Monday Absentee']);
        $tuesdayStudent = Student::factory()->create(['name' => 'Tuesday Absentee']);

        $monday = now()->startOfWeek();
        $tuesday = $monday->copy()->addDay();

        Attendance::factory()->create([
            'student_id' => $mondayStudent->id,
            'course_id' => $course->id,
            'date' => $monday->toDateString(),
            'status' => 'absent',
        ]);
        Attendance::factory()->create([
            'student_id' => $tuesdayStudent->id,
            'course_id' => $course->id,
            'date' => $tuesday->toDateString(),
            'status' => 'absent',
        ]);

        $response = $this->actingAs($user)->get('/absence-report?period=week');

        $response->assertOk();
        $response->assertSee($monday->format('l, j F Y'));
        $response->assertSee($tuesday->format('l, j F Y'));
        $response->assertSee('Monday Absentee');
        $response->assertSee('Tuesday Absentee');
    }

    public function test_an_invalid_period_falls_back_to_today(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $student = Student::factory()->create(['name' => 'Fallback Absentee']);
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
            'status' => 'absent',
        ]);

        $response = $this->actingAs($user)->get('/absence-report?period=not-a-real-period');

        $response->assertOk();
        $response->assertSee('Fallback Absentee');
    }

    public function test_authenticated_user_can_export_the_report_as_csv(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $student = Student::factory()->create(['name' => 'CSV Absentee']);
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
            'status' => 'absent',
        ]);

        $response = $this->actingAs($user)->get('/absence-report/export?period=today');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Student ID', $content);
        $this->assertStringContainsString('CSV Absentee', $content);
    }

    public function test_authenticated_user_can_download_the_report_as_a_pdf(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $student = Student::factory()->create(['name' => 'PDF Absentee']);
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
            'status' => 'absent',
        ]);

        $response = $this->actingAs($user)->get('/absence-report/export-pdf?period=today');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
