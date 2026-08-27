<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FinalizeDailyAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_marks_an_active_student_who_never_checked_in_as_absent(): void
    {
        $this->travelTo(Carbon::parse('next Monday')->setTime(18, 0));

        $student = Student::factory()->create();
        $course = Course::factory()->create(['schedule' => 'weekday']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $this->artisan('app:finalize-daily-attendance')->assertExitCode(0);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => today()->toDateString(),
            'status' => 'absent',
        ]);
    }

    public function test_it_does_not_touch_a_student_who_already_checked_in(): void
    {
        $this->travelTo(Carbon::parse('next Monday')->setTime(18, 0));

        $student = Student::factory()->create();
        $course = Course::factory()->create(['schedule' => 'weekday']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => today()->toDateString(),
            'status' => 'present',
        ]);

        $this->artisan('app:finalize-daily-attendance')->assertExitCode(0);

        $this->assertDatabaseCount('attendances', 1);
        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'present',
        ]);
    }

    public function test_it_ignores_a_student_whose_course_does_not_meet_today(): void
    {
        $this->travelTo(Carbon::parse('next Monday')->setTime(18, 0));

        $student = Student::factory()->create();
        $course = Course::factory()->create(['schedule' => 'weekend']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $this->artisan('app:finalize-daily-attendance')->assertExitCode(0);

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_it_ignores_a_non_active_enrollment(): void
    {
        $this->travelTo(Carbon::parse('next Monday')->setTime(18, 0));

        $student = Student::factory()->create();
        $course = Course::factory()->create(['schedule' => 'weekday']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'completed']);

        $this->artisan('app:finalize-daily-attendance')->assertExitCode(0);

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_it_does_nothing_on_a_sunday(): void
    {
        $this->travelTo(Carbon::parse('next Sunday')->setTime(18, 0));

        $student = Student::factory()->create();
        $course = Course::factory()->create(['schedule' => 'weekday']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $this->artisan('app:finalize-daily-attendance')->assertExitCode(0);

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_a_weekend_schedule_student_is_marked_absent_on_saturday(): void
    {
        $this->travelTo(Carbon::parse('next Saturday')->setTime(18, 0));

        $student = Student::factory()->create();
        $course = Course::factory()->create(['schedule' => 'weekend']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $this->artisan('app:finalize-daily-attendance')->assertExitCode(0);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'absent',
        ]);
    }
}
