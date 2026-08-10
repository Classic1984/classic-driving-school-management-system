<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Notifications\EnrollmentLockedNotification;
use App\Notifications\GracePeriodEndingSoonNotification;
use App\Notifications\PaymentReminderNotification;
use App\Notifications\TrainingCompletedNotification;
use App\Notifications\TrainingDaysRemainingNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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

    public function test_student_is_reminded_three_days_before_their_payment_is_due(): void
    {
        Notification::fake();
        config(['services.termii.api_key' => 'fake-key']);
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);

        $course = Course::factory()->create(['fee' => 100, 'duration_weeks' => 4]);
        $student = Student::factory()->create(['phone' => '08031234567']);
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->toDateString(),
            'due_date' => now()->addDays(3)->toDateString(),
            'status' => 'active',
        ]);

        $this->artisan('app:refresh-enrollment-locks')->assertExitCode(0);

        Notification::assertSentTo($student, PaymentReminderNotification::class, function ($notification) {
            return $notification->stage === 'upcoming';
        });
        Http::assertSent(fn ($request) => $request['to'] === '2348031234567' && str_contains($request['sms'], 'is due on'));
    }

    public function test_student_is_reminded_on_the_day_their_payment_is_due(): void
    {
        Notification::fake();
        config(['services.termii.api_key' => 'fake-key']);
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);

        $course = Course::factory()->create(['fee' => 100, 'duration_weeks' => 4]);
        $student = Student::factory()->create(['phone' => '08031234567']);
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->subDays(4)->toDateString(),
            'due_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        $this->artisan('app:refresh-enrollment-locks')->assertExitCode(0);

        Notification::assertSentTo($student, PaymentReminderNotification::class, function ($notification) {
            return $notification->stage === 'due_today';
        });
        Http::assertSent(fn ($request) => $request['to'] === '2348031234567' && str_contains($request['sms'], 'is due TODAY'));
    }

    public function test_student_is_still_reminded_about_a_balance_after_their_enrollment_completes(): void
    {
        Notification::fake();

        $course = Course::factory()->create(['fee' => 100, 'duration_weeks' => 4]);
        $student = Student::factory()->create();
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->toDateString(),
            'due_date' => now()->toDateString(),
            'status' => 'completed',
        ]);

        $this->artisan('app:refresh-enrollment-locks')->assertExitCode(0);

        Notification::assertSentTo($student, PaymentReminderNotification::class, function ($notification) {
            return $notification->stage === 'due_today';
        });
    }

    public function test_student_is_not_reminded_once_the_balance_is_fully_paid(): void
    {
        Notification::fake();

        $course = Course::factory()->create(['fee' => 100, 'duration_weeks' => 4]);
        $student = Student::factory()->create();
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->toDateString(),
            'due_date' => now()->addDays(3)->toDateString(),
            'status' => 'active',
        ]);
        Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 100,
            'status' => 'paid',
        ]);

        $this->artisan('app:refresh-enrollment-locks')->assertExitCode(0);

        Notification::assertNotSentTo($student, PaymentReminderNotification::class);
    }

    public function test_student_is_not_reminded_when_due_date_is_not_three_days_out_or_today(): void
    {
        Notification::fake();

        $course = Course::factory()->create(['fee' => 100, 'duration_weeks' => 4]);
        $student = Student::factory()->create();
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->toDateString(),
            'due_date' => now()->addDays(5)->toDateString(),
            'status' => 'active',
        ]);

        $this->artisan('app:refresh-enrollment-locks')->assertExitCode(0);

        Notification::assertNotSentTo($student, PaymentReminderNotification::class);
    }

    public function test_admins_are_notified_once_training_days_remaining_drop_to_the_threshold(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::factory()->create(['duration_weeks' => 1]);
        $student = Student::factory()->create();
        $course->students()->attach($student->id, ['enrolled_at' => now()->toDateString(), 'status' => 'active']);
        $enrollment = Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();

        // A 1-week course requires 5 days; 2 attended leaves 3 remaining.
        for ($day = 1; $day <= 2; $day++) {
            Attendance::factory()->create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'date' => now()->addDays($day)->toDateString(),
                'status' => 'present',
                'duration' => 1,
            ]);
        }
        $enrollment->refreshStatus();

        Notification::assertSentTo($admin, TrainingDaysRemainingNotification::class);
    }

    public function test_the_days_remaining_reminder_is_not_resent_on_every_refresh(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::factory()->create(['duration_weeks' => 1]);
        $student = Student::factory()->create();
        $course->students()->attach($student->id, ['enrolled_at' => now()->toDateString(), 'status' => 'active']);
        $enrollment = Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();

        for ($day = 1; $day <= 2; $day++) {
            Attendance::factory()->create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'date' => now()->addDays($day)->toDateString(),
                'status' => 'present',
                'duration' => 1,
            ]);
        }
        $enrollment->refreshStatus();
        $enrollment->fresh()->refreshStatus();
        $enrollment->fresh()->refreshStatus();

        Notification::assertSentToTimes($admin, TrainingDaysRemainingNotification::class, 1);
    }

    public function test_no_days_remaining_reminder_is_sent_while_still_far_from_completion(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::factory()->create(['duration_weeks' => 4]);
        $student = Student::factory()->create();
        $course->students()->attach($student->id, ['enrolled_at' => now()->toDateString(), 'status' => 'active']);
        $enrollment = Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();

        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
            'status' => 'present',
            'duration' => 1,
        ]);
        $enrollment->refreshStatus();

        Notification::assertNotSentTo($admin, TrainingDaysRemainingNotification::class);
    }

    public function test_admins_are_notified_when_an_enrollments_training_completes(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $secretary = User::factory()->create(['role' => 'secretary']);
        $course = Course::factory()->create(['fee' => 100, 'duration_weeks' => 1]);
        $student = Student::factory()->create();
        $course->students()->attach($student->id, ['enrolled_at' => now()->toDateString(), 'status' => 'active']);
        Payment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id, 'amount' => 100, 'status' => 'paid']);
        $enrollment = Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();

        for ($day = 1; $day <= 5; $day++) {
            Attendance::factory()->create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'date' => now()->addDays($day)->toDateString(),
                'status' => 'present',
                'duration' => 1,
            ]);
        }
        $enrollment->refreshStatus();

        Notification::assertSentTo($admin, TrainingCompletedNotification::class);
        Notification::assertNotSentTo($secretary, TrainingCompletedNotification::class);
    }

    public function test_the_training_completed_notification_is_not_resent_once_already_completed(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::factory()->create(['fee' => 100, 'duration_weeks' => 1]);
        $student = Student::factory()->create();
        $course->students()->attach($student->id, ['enrolled_at' => now()->toDateString(), 'status' => 'active']);
        Payment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id, 'amount' => 100, 'status' => 'paid']);
        $enrollment = Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();

        for ($day = 1; $day <= 5; $day++) {
            Attendance::factory()->create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'date' => now()->addDays($day)->toDateString(),
                'status' => 'present',
                'duration' => 1,
            ]);
        }
        $enrollment->refreshStatus();
        $enrollment->fresh()->refreshStatus();
        $enrollment->fresh()->refreshStatus();

        Notification::assertSentToTimes($admin, TrainingCompletedNotification::class, 1);
    }
}
