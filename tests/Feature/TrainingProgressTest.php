<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/training-progress')->assertRedirect('/login');
    }

    public function test_it_shows_every_enrollments_progress_not_just_the_dashboards_capped_fifteen(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['name' => 'Beginner Program', 'duration_weeks' => 2]);

        // More than the Dashboard's 15-row cap.
        $students = Student::factory()->count(18)->create();
        foreach ($students as $student) {
            $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);
        }

        $response = $this->actingAs($user)->get('/training-progress');

        $response->assertOk();
        $response->assertSee('Beginner Program');
        $response->assertSee('Total Days');
        $response->assertSee('Days Remaining');
    }

    public function test_the_dashboard_links_to_the_full_training_progress_list(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee(route('training-progress.index'), false);
    }

    public function test_it_shows_the_locked_reason_for_locked_enrollments(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['fee' => 100]);
        $student->courses()->attach($course->id, [
            'enrolled_at' => now()->subDays(10),
            'due_date' => now()->subDays(6),
            'status' => 'locked',
            'locked_reason' => 'overdue_balance',
        ]);

        $response = $this->actingAs($user)->get('/training-progress');

        $response->assertOk();
        $response->assertSee('Overdue Balance');
    }
}
