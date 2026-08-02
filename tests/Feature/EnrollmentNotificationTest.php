<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use App\Notifications\EnrollmentLockedNotification;
use App\Notifications\GracePeriodEndingSoonNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EnrollmentNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_are_notified_when_an_enrollment_locks_for_an_overdue_balance(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $secretary = User::factory()->create(['role' => 'secretary']);
        $course = Course::factory()->create(['fee' => 100, 'duration_weeks' => 4]);
        $student = Student::factory()->create();
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->subDays(10)->toDateString(),
            'due_date' => now()->subDays(6)->toDateString(),
            'status' => 'active',
        ]);

        $enrollment = Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();
        $enrollment->refreshStatus();

        Notification::assertSentTo($admin, EnrollmentLockedNotification::class);
        Notification::assertNotSentTo($secretary, EnrollmentLockedNotification::class);
    }

    public function test_locking_notification_is_not_resent_on_every_refresh(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::factory()->create(['fee' => 100, 'duration_weeks' => 4]);
        $student = Student::factory()->create();
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->subDays(10)->toDateString(),
            'due_date' => now()->subDays(6)->toDateString(),
            'status' => 'active',
        ]);

        $enrollment = Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();
        $enrollment->refreshStatus();
        $enrollment->refreshStatus();
        $enrollment->refreshStatus();

        Notification::assertSentToTimes($admin, EnrollmentLockedNotification::class, 1);
    }

    public function test_admins_are_notified_the_day_before_the_grace_period_ends(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::factory()->create(['fee' => 100, 'duration_weeks' => 4]);
        $student = Student::factory()->create();
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->toDateString(),
            'due_date' => now()->addDay()->toDateString(),
            'status' => 'active',
        ]);

        $this->artisan('app:refresh-enrollment-locks')->assertExitCode(0);

        Notification::assertSentTo($admin, GracePeriodEndingSoonNotification::class);
    }

    public function test_no_reminder_is_sent_when_the_grace_period_is_not_ending_tomorrow(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::factory()->create(['fee' => 100, 'duration_weeks' => 4]);
        $student = Student::factory()->create();
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->toDateString(),
            'due_date' => now()->addDays(4)->toDateString(),
            'status' => 'active',
        ]);

        $this->artisan('app:refresh-enrollment-locks')->assertExitCode(0);

        Notification::assertNotSentTo($admin, GracePeriodEndingSoonNotification::class);
    }
}
