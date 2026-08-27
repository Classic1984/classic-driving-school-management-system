<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\TheoryClass;
use App\Models\TheoryClassAttendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TheoryClassTest extends TestCase
{
    use RefreshDatabase;

    protected function activeStudent(): Student
    {
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        return $student;
    }

    public function test_the_index_page_lists_theory_classes(): void
    {
        $user = User::factory()->create();
        $theoryClass = TheoryClass::factory()->create(['topic' => 'Road Signs']);

        $response = $this->actingAs($user)->get(route('theory-classes.index'));

        $response->assertOk();
        $response->assertSee('Road Signs');
    }

    public function test_the_roster_page_lists_every_actively_enrolled_student(): void
    {
        $user = User::factory()->create();
        $student = $this->activeStudent();
        $theoryClass = TheoryClass::factory()->create();

        $response = $this->actingAs($user)->get(route('theory-classes.show', $theoryClass));

        $response->assertOk();
        $response->assertSee($student->name);
        $response->assertSee('Not yet marked');
    }

    public function test_a_course_manager_can_mark_a_student_present(): void
    {
        $user = User::factory()->create();
        $student = $this->activeStudent();
        $theoryClass = TheoryClass::factory()->create();

        $response = $this->actingAs($user)->post(route('theory-classes.attendances.store', $theoryClass), [
            'student_id' => $student->id,
            'status' => 'present',
            'score' => 85,
            'remarks' => 'Good grasp of the topic.',
        ]);

        $response->assertRedirect(route('theory-classes.show', $theoryClass));
        $this->assertDatabaseHas('theory_class_attendances', [
            'theory_class_id' => $theoryClass->id,
            'student_id' => $student->id,
            'status' => 'present',
            'score' => 85,
            'remarks' => 'Good grasp of the topic.',
            'marked_by' => $user->id,
        ]);
    }

    public function test_marking_a_student_again_updates_the_same_record_instead_of_duplicating(): void
    {
        $user = User::factory()->create();
        $student = $this->activeStudent();
        $theoryClass = TheoryClass::factory()->create();

        $this->actingAs($user)->post(route('theory-classes.attendances.store', $theoryClass), [
            'student_id' => $student->id,
            'status' => 'present',
        ]);
        $this->actingAs($user)->post(route('theory-classes.attendances.store', $theoryClass), [
            'student_id' => $student->id,
            'status' => 'late',
            'score' => 70,
        ]);

        $this->assertDatabaseCount('theory_class_attendances', 1);
        $this->assertDatabaseHas('theory_class_attendances', [
            'theory_class_id' => $theoryClass->id,
            'student_id' => $student->id,
            'status' => 'late',
            'score' => 70,
        ]);
    }

    public function test_a_course_manager_can_update_the_class_details(): void
    {
        $user = User::factory()->create();
        $instructor = Instructor::factory()->create();
        $theoryClass = TheoryClass::factory()->create(['topic' => 'Old Topic']);

        $response = $this->actingAs($user)->patch(route('theory-classes.update', $theoryClass), [
            'topic' => 'Right of Way',
            'instructor_id' => $instructor->id,
            'start_time' => '11:00',
            'notes' => 'Extra Q&A session.',
        ]);

        $response->assertRedirect(route('theory-classes.show', $theoryClass));
        $this->assertDatabaseHas('theory_classes', [
            'id' => $theoryClass->id,
            'topic' => 'Right of Way',
            'instructor_id' => $instructor->id,
            'notes' => 'Extra Q&A session.',
        ]);
    }

    public function test_attendance_percentage_and_counts_reflect_the_roster(): void
    {
        $theoryClass = TheoryClass::factory()->create();
        $present = $this->activeStudent();
        $absent = $this->activeStudent();

        TheoryClassAttendance::factory()->create([
            'theory_class_id' => $theoryClass->id,
            'student_id' => $present->id,
            'status' => 'present',
        ]);
        TheoryClassAttendance::factory()->create([
            'theory_class_id' => $theoryClass->id,
            'student_id' => $absent->id,
            'status' => 'absent',
        ]);
        $theoryClass->load('attendances');

        $this->assertSame(2, $theoryClass->expectedCount());
        $this->assertSame(1, $theoryClass->presentCount());
        $this->assertSame(1, $theoryClass->absentCount());
        $this->assertSame(50, $theoryClass->attendancePercentage());
    }

    public function test_student_theory_progress_summarizes_their_attendance_and_scores(): void
    {
        $student = Student::factory()->create(['enrollment_date' => today()->subWeeks(3)]);

        $attended = TheoryClass::factory()->create(['class_date' => today()->subWeeks(2), 'topic' => 'Road Signs']);
        $missed = TheoryClass::factory()->create(['class_date' => today()->subWeek(), 'topic' => 'Right of Way']);

        TheoryClassAttendance::factory()->create([
            'theory_class_id' => $attended->id,
            'student_id' => $student->id,
            'status' => 'present',
            'score' => 90,
        ]);
        TheoryClassAttendance::factory()->create([
            'theory_class_id' => $missed->id,
            'student_id' => $student->id,
            'status' => 'absent',
        ]);

        $progress = $student->theoryProgress();

        $this->assertSame(1, $progress['classes_attended']);
        $this->assertSame(2, $progress['classes_expected']);
        $this->assertSame(50, $progress['attendance_percentage']);
        $this->assertSame(1, $progress['topics_completed']);
        $this->assertSame(90, $progress['average_score']);
        $this->assertSame(['Right of Way'], $progress['outstanding_topics']->all());
    }
}
