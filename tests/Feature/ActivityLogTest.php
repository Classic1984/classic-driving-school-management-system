<?php

namespace Tests\Feature;

use App\Console\Commands\RecordSchedulerHeartbeat;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_the_activity_log(): void
    {
        $this->get('/activity-log')->assertRedirect('/login');
    }

    public function test_only_a_director_can_view_the_activity_log(): void
    {
        $admin = User::factory()->admin()->create();
        $secretary = User::factory()->secretary()->create();
        $director = User::factory()->director()->create();

        $this->actingAs($admin)->get('/activity-log')->assertForbidden();
        $this->actingAs($secretary)->get('/activity-log')->assertForbidden();
        $this->actingAs($director)->get('/activity-log')->assertOk();
    }

    public function test_the_activity_log_shows_who_did_what_and_when(): void
    {
        $director = User::factory()->director()->create();
        $secretary = User::factory()->secretary()->create(['name' => 'Secretary Mary']);
        ActivityLog::record("Recorded John Doe's training attendance", $secretary);

        $response = $this->actingAs($director)->get('/activity-log');

        $response->assertOk();
        $response->assertSee('Secretary Mary');
        $response->assertSee("Recorded John Doe's training attendance");
    }

    public function test_registering_a_student_is_logged(): void
    {
        $user = User::factory()->create(['name' => 'Front Desk Staff']);
        $course = Course::factory()->create();

        $this->actingAs($user)->post('/students', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '08012345678',
            'date_of_birth' => '2000-01-01',
            'course_type' => 'manual',
            'enrollment_date' => now()->toDateString(),
            'course_id' => $course->id,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'description' => 'Registered student John Doe',
        ]);
    }

    public function test_deleting_a_student_is_logged(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create(['name' => 'Jane Doe']);

        $this->actingAs($director)->delete("/students/{$student->id}")->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $director->id,
            'description' => 'Deleted student Jane Doe',
        ]);
    }

    public function test_recording_a_payment_is_logged(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Jane Doe']);
        $course = Course::factory()->create(['name' => 'Beginner Program']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $this->actingAs($user)->post('/payments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 500,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'status' => 'paid',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'description' => 'Recorded a payment of ₦500.00 for Jane Doe (Beginner Program)',
        ]);
    }

    public function test_logging_attendance_is_logged(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Jane Doe']);
        $course = Course::factory()->create(['name' => 'Beginner Program']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $this->actingAs($user)->post('/attendances', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
            'status' => 'present',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'description' => 'Logged training attendance for Jane Doe (Beginner Program)',
        ]);
    }

    public function test_issuing_a_certificate_is_logged(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Jane Doe']);
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, [
            'enrolled_at' => now(),
            'due_date' => now()->addDays($course->gracePeriodDays()),
            'status' => 'completed',
        ]);

        $this->actingAs($user)->post('/certificates', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'issue_date' => now()->toDateString(),
        ])->assertSessionHasNoErrors();

        $certificate = Certificate::where('student_id', $student->id)->first();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'description' => "Issued certificate {$certificate->certificate_number} for Jane Doe",
        ]);
    }

    public function test_marking_an_enrollment_complete_is_logged(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Jane Doe']);
        $course = Course::factory()->create(['name' => 'Beginner Program', 'duration_weeks' => 1, 'fee' => 0]);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 0]);
        for ($day = 1; $day <= 5; $day++) {
            Attendance::factory()->create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'date' => now()->addDays($day)->toDateString(),
                'status' => 'present',
                'duration' => 1,
            ]);
        }
        $enrollment = Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();

        $this->actingAs($user)->patch("/enrollments/{$enrollment->id}/complete")->assertSessionHasNoErrors();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'description' => "Marked Jane Doe's enrollment in Beginner Program as completed",
        ]);
    }

    public function test_reactivating_an_enrollment_is_logged(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create(['name' => 'Jane Doe']);
        $course = Course::factory()->create(['fee' => 100, 'name' => 'Beginner Program']);
        $student->courses()->attach($course->id, [
            'enrolled_at' => now()->subMonths(3),
            'due_date' => now()->addDays(30),
            'status' => 'locked',
            'locked_reason' => 'training_period_expired',
        ]);
        $enrollment = Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();

        $this->actingAs($director)->post("/enrollments/{$enrollment->id}/reactivate", [
            'additional_fee' => 0,
            'payment_method' => 'cash',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $director->id,
            'description' => "Reactivated Jane Doe's enrollment in Beginner Program",
        ]);
    }

    public function test_managing_a_course_is_logged(): void
    {
        $user = User::factory()->secretary()->create();

        $this->actingAs($user)->post('/courses', [
            'name' => 'Advanced Driving',
            'description' => 'An advanced course.',
            'course_type' => 'manual',
            'schedule' => 'weekday',
            'duration_hours' => 20,
            'duration_weeks' => 4,
            'fee' => 199.99,
            'status' => 'active',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'description' => 'Created course Advanced Driving',
        ]);
    }

    public function test_managing_an_instructor_is_logged(): void
    {
        $user = User::factory()->secretary()->create();

        $this->actingAs($user)->post('/instructors', [
            'name' => 'Mr. Adebayo',
            'email' => 'adebayo@example.com',
            'phone' => '08012345678',
            'license_number' => 'LIC-123',
            'specialization' => 'manual',
            'hire_date' => now()->toDateString(),
            'status' => 'active',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'description' => 'Added instructor Mr. Adebayo',
        ]);
    }

    public function test_it_shows_the_scheduler_as_running_when_the_heartbeat_is_recent(): void
    {
        $director = User::factory()->director()->create();
        Cache::put(RecordSchedulerHeartbeat::CACHE_KEY, now()->subMinute());

        $response = $this->actingAs($director)->get('/activity-log');

        $response->assertOk();
        $response->assertSee('Running');
    }

    public function test_it_shows_the_scheduler_as_not_running_when_the_heartbeat_is_stale(): void
    {
        $director = User::factory()->director()->create();
        Cache::put(RecordSchedulerHeartbeat::CACHE_KEY, now()->subMinutes(10));

        $response = $this->actingAs($director)->get('/activity-log');

        $response->assertOk();
        $response->assertSee('Not Running');
    }

    public function test_it_shows_the_scheduler_as_never_detected_when_there_is_no_heartbeat(): void
    {
        $director = User::factory()->director()->create();
        Cache::forget(RecordSchedulerHeartbeat::CACHE_KEY);

        $response = $this->actingAs($director)->get('/activity-log');

        $response->assertOk();
        $response->assertSee('Never Detected');
    }

    public function test_recording_an_expense_is_logged(): void
    {
        $director = User::factory()->director()->create();

        $this->actingAs($director)->post('/expenses', [
            'category' => 'electricity',
            'amount' => 5000,
            'expense_date' => now()->toDateString(),
            'description' => 'Electricity bill',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $director->id,
            'description' => 'Recorded an expense of ₦5,000.00 (electricity)',
        ]);
    }
}
