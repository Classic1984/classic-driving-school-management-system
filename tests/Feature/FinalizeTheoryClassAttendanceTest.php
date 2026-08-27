<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Student;
use App\Models\TheoryClass;
use App\Models\TheoryClassAttendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinalizeTheoryClassAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_marks_an_active_student_who_never_checked_in_as_absent(): void
    {
        $theoryClass = TheoryClass::factory()->create(['class_date' => today()]);
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $this->artisan('app:finalize-theory-class-attendance')->assertExitCode(0);

        $this->assertDatabaseHas('theory_class_attendances', [
            'theory_class_id' => $theoryClass->id,
            'student_id' => $student->id,
            'status' => 'absent',
        ]);
    }

    public function test_it_does_not_touch_a_student_who_already_checked_in(): void
    {
        $theoryClass = TheoryClass::factory()->create(['class_date' => today()]);
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);
        TheoryClassAttendance::factory()->create([
            'theory_class_id' => $theoryClass->id,
            'student_id' => $student->id,
            'status' => 'present',
        ]);

        $this->artisan('app:finalize-theory-class-attendance')->assertExitCode(0);

        $this->assertDatabaseCount('theory_class_attendances', 1);
        $this->assertDatabaseHas('theory_class_attendances', [
            'theory_class_id' => $theoryClass->id,
            'student_id' => $student->id,
            'status' => 'present',
        ]);
    }

    public function test_it_ignores_a_non_active_enrollment(): void
    {
        $theoryClass = TheoryClass::factory()->create(['class_date' => today()]);
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'completed']);

        $this->artisan('app:finalize-theory-class-attendance')->assertExitCode(0);

        $this->assertDatabaseCount('theory_class_attendances', 0);
    }

    public function test_it_does_nothing_when_no_class_was_scheduled_today(): void
    {
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $this->artisan('app:finalize-theory-class-attendance')->assertExitCode(0);

        $this->assertDatabaseCount('theory_class_attendances', 0);
    }
}
