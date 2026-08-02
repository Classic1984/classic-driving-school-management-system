<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Instructor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_course_routes(): void
    {
        $course = Course::factory()->create();

        $this->get('/courses')->assertRedirect('/login');
        $this->get('/courses/create')->assertRedirect('/login');
        $this->get("/courses/{$course->id}")->assertRedirect('/login');
        $this->get("/courses/{$course->id}/edit")->assertRedirect('/login');
        $this->post('/courses', [])->assertRedirect('/login');
        $this->put("/courses/{$course->id}", [])->assertRedirect('/login');
        $this->delete("/courses/{$course->id}")->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_course_index(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['name' => 'Highway Mastery']);

        $response = $this->actingAs($user)->get('/courses');

        $response->assertOk();
        $response->assertSee('Highway Mastery');
    }

    public function test_authenticated_user_can_view_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/courses/create');

        $response->assertOk();
    }

    public function test_authenticated_user_can_store_a_course_with_instructors(): void
    {
        $user = User::factory()->create();
        $instructor = Instructor::factory()->create();

        $data = [
            'name' => 'Beginner Driving',
            'description' => 'An introductory course.',
            'course_type' => 'manual',
            'duration_hours' => 20,
            'fee' => 199.99,
            'status' => 'active',
            'instructors' => [$instructor->id],
        ];

        $response = $this->actingAs($user)->post('/courses', $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/courses');

        $this->assertDatabaseHas('courses', ['name' => 'Beginner Driving']);

        $course = Course::where('name', 'Beginner Driving')->firstOrFail();
        $this->assertTrue($course->instructors->contains($instructor));
    }

    public function test_storing_a_course_requires_valid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/courses', [
            'name' => '',
            'course_type' => 'invalid-type',
            'duration_hours' => 0,
            'fee' => -10,
            'status' => 'invalid-status',
        ]);

        $response->assertSessionHasErrors([
            'name', 'course_type', 'duration_hours', 'fee', 'status',
        ]);

        $this->assertDatabaseCount('courses', 0);
    }

    public function test_authenticated_user_can_view_a_course(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $response = $this->actingAs($user)->get("/courses/{$course->id}");

        $response->assertOk();
        $response->assertSee($course->name);
    }

    public function test_authenticated_user_can_update_a_course_and_its_instructors(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['name' => 'Old Course Name']);
        $oldInstructor = Instructor::factory()->create();
        $newInstructor = Instructor::factory()->create();
        $course->instructors()->attach($oldInstructor);

        $response = $this->actingAs($user)->put("/courses/{$course->id}", [
            'name' => 'New Course Name',
            'description' => $course->description,
            'course_type' => $course->course_type,
            'duration_hours' => $course->duration_hours,
            'fee' => $course->fee,
            'status' => 'inactive',
            'instructors' => [$newInstructor->id],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/courses');

        $course->refresh();
        $this->assertSame('New Course Name', $course->name);
        $this->assertSame('inactive', $course->status);
        $this->assertTrue($course->instructors->contains($newInstructor));
        $this->assertFalse($course->instructors->contains($oldInstructor));
    }

    public function test_authenticated_user_can_delete_a_course(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $response = $this->actingAs($user)->delete("/courses/{$course->id}");

        $response->assertRedirect('/courses');
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }
}
