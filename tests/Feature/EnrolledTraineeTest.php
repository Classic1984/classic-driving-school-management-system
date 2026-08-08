<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrolledTraineeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/enrolled-trainees')->assertRedirect('/login');
    }

    public function test_index_lists_only_enrolled_students(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['name' => 'Defensive Driving Course']);
        $enrolled = Student::factory()->create(['name' => 'Enrolled Student']);
        $enrolled->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);
        $notEnrolled = Student::factory()->create(['name' => 'Not Enrolled Student']);

        $response = $this->actingAs($user)->get('/enrolled-trainees');

        $response->assertOk();
        $response->assertSee('Enrolled Student');
        $response->assertSee('Defensive Driving Course');
        $response->assertDontSee('Not Enrolled Student');
    }

    public function test_index_shows_training_session_count_and_who_last_logged_it(): void
    {
        $user = User::factory()->create(['name' => 'Front Desk Staff']);
        $course = Course::factory()->create();
        $student = Student::factory()->create(['name' => 'Chidinma Eze']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->subDay(),
            'status' => 'present',
            'logged_by' => $user->id,
        ]);
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now(),
            'status' => 'present',
            'logged_by' => $user->id,
        ]);
        // Not counted towards the session count: an absent entry.
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->subDays(2),
            'status' => 'absent',
        ]);

        $response = $this->actingAs($user)->get('/enrolled-trainees');

        $response->assertOk();
        $response->assertSee('Chidinma Eze');
        $response->assertSeeInOrder(['Chidinma Eze', '2']);
        $response->assertSee('Front Desk Staff');
    }

    public function test_index_can_be_filtered_by_search(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $match = Student::factory()->create(['name' => 'Findable Student']);
        $match->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);
        $other = Student::factory()->create(['name' => 'Someone Else']);
        $other->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($user)->get('/enrolled-trainees?search=Findable');

        $response->assertOk();
        $response->assertSee('Findable Student');
        $response->assertDontSee('Someone Else');
    }

    public function test_the_students_name_links_to_their_training_record(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $student = Student::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($user)->get('/enrolled-trainees');

        $response->assertOk();
        $response->assertSee(route('students.training-record', $student), false);
    }
}
