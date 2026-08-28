<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\TheoryClass;
use App\Models\TheoryClassAttendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class InstructorAttendanceMarkingTest extends TestCase
{
    use RefreshDatabase;

    protected function instructorWithAccess(): Instructor
    {
        $user = User::factory()->create(['role' => 'instructor']);

        $instructor = Instructor::factory()->create();
        $instructor->forceFill(['user_id' => $user->id])->save();

        return $instructor->fresh();
    }

    public function test_an_instructor_can_mark_a_student_present_in_their_own_course(): void
    {
        $this->travelTo(Carbon::parse('next Monday')->setTime(10, 0));

        $instructor = $this->instructorWithAccess();
        $course = Course::factory()->create(['schedule' => 'weekday']);
        $course->instructors()->attach($instructor->id);
        $student = Student::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($instructor->user)->post(route('instructor.attendance.store'), [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'present',
        ]);

        $response->assertRedirect(route('instructor.dashboard'));
        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'status' => 'present',
            'type' => 'practical',
        ]);
    }

    public function test_an_instructor_cannot_mark_attendance_for_a_course_they_do_not_teach(): void
    {
        $instructor = $this->instructorWithAccess();
        $otherCourse = Course::factory()->create();
        $student = Student::factory()->create();
        $student->courses()->attach($otherCourse->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($instructor->user)->post(route('instructor.attendance.store'), [
            'student_id' => $student->id,
            'course_id' => $otherCourse->id,
            'status' => 'present',
        ]);

        $response->assertSessionHasErrors('course_id');
        $this->assertDatabaseMissing('attendances', ['student_id' => $student->id]);
    }

    public function test_marking_the_same_student_twice_in_a_day_is_rejected_as_a_duplicate(): void
    {
        $instructor = $this->instructorWithAccess();
        $course = Course::factory()->create();
        $course->instructors()->attach($instructor->id);
        $student = Student::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => today(),
        ]);

        $response = $this->actingAs($instructor->user)->post(route('instructor.attendance.store'), [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'present',
        ]);

        $response->assertSessionHasErrors('student_id');
    }

    public function test_a_non_instructor_cannot_reach_the_attendance_marking_endpoint(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('instructor.attendance.store'), [
            'student_id' => 1,
            'course_id' => 1,
            'status' => 'present',
        ]);

        $response->assertForbidden();
    }

    public function test_an_instructor_can_mark_theory_attendance_for_their_own_class(): void
    {
        $instructor = $this->instructorWithAccess();
        $theoryClass = TheoryClass::factory()->create(['instructor_id' => $instructor->id, 'class_date' => today()]);
        $student = Student::factory()->create();
        $student->courses()->attach(Course::factory()->create()->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($instructor->user)->post(route('instructor.theory-attendance.store', $theoryClass), [
            'student_id' => $student->id,
            'status' => 'present',
        ]);

        $response->assertRedirect(route('instructor.dashboard'));
        $this->assertDatabaseHas('theory_class_attendances', [
            'theory_class_id' => $theoryClass->id,
            'student_id' => $student->id,
            'status' => 'present',
            'marked_by' => $instructor->user->id,
        ]);
    }

    public function test_an_instructor_cannot_mark_theory_attendance_for_a_class_assigned_to_someone_else(): void
    {
        $instructor = $this->instructorWithAccess();
        $otherInstructor = Instructor::factory()->create();
        $theoryClass = TheoryClass::factory()->create(['instructor_id' => $otherInstructor->id, 'class_date' => today()]);
        $student = Student::factory()->create();

        $response = $this->actingAs($instructor->user)->post(route('instructor.theory-attendance.store', $theoryClass), [
            'student_id' => $student->id,
            'status' => 'present',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('theory_class_attendances', ['theory_class_id' => $theoryClass->id]);
    }

    public function test_marking_theory_attendance_again_corrects_the_existing_record_instead_of_duplicating(): void
    {
        $instructor = $this->instructorWithAccess();
        $theoryClass = TheoryClass::factory()->create(['instructor_id' => $instructor->id, 'class_date' => today()]);
        $student = Student::factory()->create();
        TheoryClassAttendance::factory()->create([
            'theory_class_id' => $theoryClass->id,
            'student_id' => $student->id,
            'status' => 'absent',
        ]);

        $this->actingAs($instructor->user)->post(route('instructor.theory-attendance.store', $theoryClass), [
            'student_id' => $student->id,
            'status' => 'present',
        ]);

        $this->assertSame(1, TheoryClassAttendance::where('theory_class_id', $theoryClass->id)->where('student_id', $student->id)->count());
        $this->assertDatabaseHas('theory_class_attendances', [
            'theory_class_id' => $theoryClass->id,
            'student_id' => $student->id,
            'status' => 'present',
        ]);
    }
}
