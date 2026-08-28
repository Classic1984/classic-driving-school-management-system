<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\TheoryClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class InstructorDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function instructorWithAccess(array $overrides = []): Instructor
    {
        $user = User::factory()->create(['role' => 'instructor']);

        $instructor = Instructor::factory()->create($overrides);
        $instructor->forceFill(['user_id' => $user->id])->save();

        return $instructor->fresh();
    }

    public function test_an_instructor_only_sees_present_students_from_their_own_courses(): void
    {
        $this->travelTo(Carbon::parse('next Monday')->setTime(10, 0));

        $instructor = $this->instructorWithAccess();
        $myCourse = Course::factory()->create(['schedule' => 'weekday']);
        $myCourse->instructors()->attach($instructor->id);

        $otherInstructor = Instructor::factory()->create();
        $otherCourse = Course::factory()->create(['schedule' => 'weekday']);
        $otherCourse->instructors()->attach($otherInstructor->id);

        $myStudent = Student::factory()->create(['name' => 'My Student']);
        Attendance::factory()->create([
            'student_id' => $myStudent->id,
            'course_id' => $myCourse->id,
            'instructor_id' => $instructor->id,
            'date' => today(),
            'status' => 'present',
        ]);

        $otherStudent = Student::factory()->create(['name' => 'Other Student']);
        Attendance::factory()->create([
            'student_id' => $otherStudent->id,
            'course_id' => $otherCourse->id,
            'instructor_id' => $otherInstructor->id,
            'date' => today(),
            'status' => 'present',
        ]);

        $response = $this->actingAs($instructor->user)->get(route('instructor.dashboard'));

        $response->assertOk();
        $response->assertSee('My Student');
        $response->assertDontSee('Other Student');
    }

    public function test_an_instructor_only_sees_absent_students_from_their_own_courses(): void
    {
        $this->travelTo(Carbon::parse('next Monday')->setTime(10, 0));

        $instructor = $this->instructorWithAccess();
        $myCourse = Course::factory()->create(['schedule' => 'weekday']);
        $myCourse->instructors()->attach($instructor->id);

        $otherInstructor = Instructor::factory()->create();
        $otherCourse = Course::factory()->create(['schedule' => 'weekday']);
        $otherCourse->instructors()->attach($otherInstructor->id);

        $myStudent = Student::factory()->create(['name' => 'Absent Mine']);
        $myStudent->courses()->attach($myCourse->id, ['enrolled_at' => now(), 'status' => 'active']);

        $otherStudent = Student::factory()->create(['name' => 'Absent Other']);
        $otherStudent->courses()->attach($otherCourse->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($instructor->user)->get(route('instructor.dashboard'));

        $response->assertOk();
        $response->assertSee('Absent Mine');
        $response->assertDontSee('Absent Other');
    }

    public function test_no_practical_training_is_expected_on_sunday(): void
    {
        $this->travelTo(Carbon::parse('next Sunday')->setTime(10, 0));

        $instructor = $this->instructorWithAccess();
        $course = Course::factory()->create(['schedule' => 'weekday']);
        $course->instructors()->attach($instructor->id);
        $student = Student::factory()->create(['name' => 'Sunday Student']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($instructor->user)->get(route('instructor.dashboard'));

        $response->assertOk();
        $response->assertDontSee('Sunday Student');
        $response->assertSee('closed on Sundays');
    }

    public function test_a_theory_class_assigned_to_this_instructor_today_is_shown(): void
    {
        $instructor = $this->instructorWithAccess();
        TheoryClass::factory()->create([
            'instructor_id' => $instructor->id,
            'class_date' => today(),
            'topic' => 'Road Signs 101',
        ]);

        $response = $this->actingAs($instructor->user)->get(route('instructor.dashboard'));

        $response->assertOk();
        $response->assertSee('Road Signs 101');
    }

    public function test_a_theory_class_assigned_to_another_instructor_today_is_not_shown(): void
    {
        $instructor = $this->instructorWithAccess();
        $otherInstructor = Instructor::factory()->create();
        TheoryClass::factory()->create([
            'instructor_id' => $otherInstructor->id,
            'class_date' => today(),
            'topic' => 'Not My Class',
        ]);

        $response = $this->actingAs($instructor->user)->get(route('instructor.dashboard'));

        $response->assertOk();
        $response->assertDontSee('Not My Class');
        $response->assertSee('No theory class assigned to you today');
    }
}
