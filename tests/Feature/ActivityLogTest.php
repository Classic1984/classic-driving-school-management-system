<?php

namespace Tests\Feature;

use App\Console\Commands\RecordSchedulerHeartbeat;
use App\Models\ActivityLog;
use App\Models\Assessment;
use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Service;
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
        Assessment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id, 'result' => 'pass']);

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

    public function test_it_still_shows_the_scheduler_as_running_between_15_minute_cron_ticks(): void
    {
        $director = User::factory()->director()->create();
        Cache::put(RecordSchedulerHeartbeat::CACHE_KEY, now()->subMinutes(14));

        $response = $this->actingAs($director)->get('/activity-log');

        $response->assertOk();
        $response->assertSee('Running');
    }

    public function test_it_shows_the_scheduler_as_not_running_when_the_heartbeat_is_stale(): void
    {
        $director = User::factory()->director()->create();
        Cache::put(RecordSchedulerHeartbeat::CACHE_KEY, now()->subMinutes(25));

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

    public function test_a_specific_date_filters_to_only_that_days_entries(): void
    {
        $director = User::factory()->director()->create();

        $this->travelTo(now()->subDays(2));
        ActivityLog::record('Registered student Old Entry', $director);

        $this->travelBack();
        ActivityLog::record('Registered student Today Entry', $director);

        $targetDate = now()->subDays(2)->toDateString();
        $response = $this->actingAs($director)->get("/activity-log?date={$targetDate}");

        $response->assertOk();
        $response->assertSee('Old Entry');
        $response->assertDontSee('Today Entry');
    }

    public function test_an_invalid_date_is_ignored_in_favor_of_the_period_filter(): void
    {
        $director = User::factory()->director()->create();
        ActivityLog::record('Registered student Jane Doe', $director);

        $response = $this->actingAs($director)->get('/activity-log?date=not-a-date');

        $response->assertOk();
        $response->assertSee('Jane Doe');
    }

    public function test_filtering_by_category_shows_only_that_categorys_entries(): void
    {
        $director = User::factory()->director()->create();
        ActivityLog::record("Registered Okoro Emeka for Driver's License Processing and recorded a payment of ₦50,000.00", $director);
        ActivityLog::record('Recorded a payment of ₦500.00 for Jane Doe (Beginner Program)', $director);

        $response = $this->actingAs($director)->get('/activity-log?category=drivers_license');

        $response->assertOk();
        $response->assertSee('Okoro Emeka');
        $response->assertDontSee('Jane Doe');
    }

    public function test_a_combined_service_registration_is_categorized_by_service_not_payments(): void
    {
        // Regression: "Registered X for Driver's License Processing and
        // recorded a payment..." contains both a service name and the
        // word "payment" - the service-specific category must win, since
        // it's checked first, or every service registration would show up
        // under the generic Payments tile instead.
        $director = User::factory()->director()->create();
        ActivityLog::record("Registered Okoro Emeka for Driver's License Processing and recorded a payment of ₦50,000.00", $director);

        $response = $this->actingAs($director)->get('/activity-log?category=payments');

        $response->assertOk();
        $response->assertDontSee('Okoro Emeka');
    }

    public function test_an_unknown_category_is_ignored(): void
    {
        $director = User::factory()->director()->create();
        ActivityLog::record('Registered student Jane Doe', $director);

        $response = $this->actingAs($director)->get('/activity-log?category=not-a-real-category');

        $response->assertOk();
        $response->assertSee('Jane Doe');
    }

    public function test_an_uncategorized_entry_is_only_counted_under_other(): void
    {
        $director = User::factory()->director()->create();
        ActivityLog::record('Something entirely unclassifiable happened', $director);

        $response = $this->actingAs($director)->get('/activity-log?category=other');

        $response->assertOk();
        $response->assertSee('Something entirely unclassifiable happened');
    }

    public function test_the_today_tile_counts_todays_activity_regardless_of_the_selected_filters(): void
    {
        $director = User::factory()->director()->create();

        $this->travelTo(now()->subDay());
        ActivityLog::record('Registered student Yesterday Entry', $director);

        $this->travelBack();
        ActivityLog::record('Registered student Today Entry', $director);

        // Even while viewing "This Year" filtered to a category, the Today
        // tile itself always reflects today's total.
        $response = $this->actingAs($director)->get('/activity-log?period=year&category=enrollment');

        $response->assertOk();
        $response->assertSee('Today Activities');
    }

    public function test_the_daily_breakdown_groups_todays_revenue_by_category_with_a_total(): void
    {
        $director = User::factory()->director()->create();
        $course = Course::factory()->create();
        $student = Student::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 40000]);
        $enrollment = $student->courses()->first()->pivot;
        $service = Service::factory()->create(['name' => "Learner's Permit", 'price' => 6000]);
        $studentService = $student->studentServices()->create(['service_id' => $service->id, 'price' => 6000]);

        $this->actingAs($director)->post('/payments/record', [
            'student_id' => $student->id,
            'amount' => 46000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'allocations' => [
                ['type' => 'training', 'id' => $enrollment->id, 'amount' => 40000],
                ['type' => 'service', 'id' => $studentService->id, 'amount' => 6000],
            ],
        ])->assertSessionHasNoErrors();

        $response = $this->actingAs($director)->get('/activity-log');

        $response->assertOk();
        $response->assertSee('Daily Breakdown');
        $response->assertSee('Training');
        $response->assertSee("Learner's Permit");
        $response->assertSee('40,000.00');
        $response->assertSee('6,000.00');
        $response->assertSee('46,000.00');
    }

    public function test_the_daily_breakdown_follows_the_specific_date_filter_instead_of_defaulting_to_today(): void
    {
        $director = User::factory()->director()->create();
        $pastDate = now()->subDays(3)->toDateString();
        Payment::factory()->create(['status' => 'paid', 'payment_date' => $pastDate])
            ->allocations()->create(['allocation_type' => 'training', 'amount' => 12345]);

        // Not shown by default - the breakdown covers today, not this
        // 3-day-old payment.
        $defaultResponse = $this->actingAs($director)->get('/activity-log');
        $defaultResponse->assertOk();
        $defaultResponse->assertDontSee('12,345.00');

        // Shown once the Specific Date filter is pointed at that day.
        $response = $this->actingAs($director)->get("/activity-log?date={$pastDate}");
        $response->assertOk();
        $response->assertSee('12,345.00');
    }

    public function test_the_daily_breakdown_shows_a_message_when_nothing_was_paid_that_day(): void
    {
        $director = User::factory()->director()->create();

        $response = $this->actingAs($director)->get('/activity-log');

        $response->assertOk();
        $response->assertSee('No payments recorded for this day.');
    }

    public function test_the_daily_breakdown_excludes_a_reversed_payment(): void
    {
        $director = User::factory()->director()->create();
        $payment = Payment::factory()->create(['amount' => 15000, 'status' => 'paid']);
        $payment->allocations()->create(['allocation_type' => 'training', 'amount' => 15000]);

        $this->actingAs($director)->post("/payments/{$payment->id}/reverse", [
            'reason' => 'Payment duplicated.',
        ])->assertSessionHasNoErrors();

        $response = $this->actingAs($director)->get('/activity-log');

        // The reversed payment's amount still legitimately appears in the
        // timeline's own "Reversed a payment of ₦15,000.00..." entry below -
        // it's specifically the Daily Breakdown that must exclude it, shown
        // here by the breakdown falling back to its empty-state message.
        $response->assertOk();
        $response->assertSee('No payments recorded for this day.');
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
