<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\DiscountRequest;
use App\Models\Instructor;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Service;
use App\Models\Student;
use App\Models\StudentCorrectionRequest;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_training_statistics_linking_to_the_training_report(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $today1 = Student::factory()->create();
        $today2 = Student::factory()->create();
        Attendance::factory()->create(['student_id' => $today1->id, 'course_id' => $course->id, 'date' => now(), 'status' => 'present']);
        Attendance::factory()->create(['student_id' => $today2->id, 'course_id' => $course->id, 'date' => now(), 'status' => 'present']);
        // Not counted: absent status, and a login outside today.
        $absent = Student::factory()->create();
        Attendance::factory()->create(['student_id' => $absent->id, 'course_id' => $course->id, 'date' => now(), 'status' => 'absent']);
        $old = Student::factory()->create();
        Attendance::factory()->create(['student_id' => $old->id, 'course_id' => $course->id, 'date' => now()->subYear(), 'status' => 'present']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Training Statistics');
        $response->assertSee(route('training-report.index', ['period' => 'today']), false);
        $response->assertSee(route('training-report.index', ['period' => 'week']), false);
        $response->assertSee(route('training-report.index', ['period' => 'month']), false);
        $response->assertSee(route('training-report.index', ['period' => 'year']), false);
    }

    public function test_dashboard_shows_absences_linking_to_the_absence_report(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $absent1 = Student::factory()->create();
        $absent2 = Student::factory()->create();
        Attendance::factory()->create(['student_id' => $absent1->id, 'course_id' => $course->id, 'date' => now(), 'status' => 'absent']);
        Attendance::factory()->create(['student_id' => $absent2->id, 'course_id' => $course->id, 'date' => now(), 'status' => 'absent']);
        // Not counted: present status, and an absence outside today.
        $present = Student::factory()->create();
        Attendance::factory()->create(['student_id' => $present->id, 'course_id' => $course->id, 'date' => now(), 'status' => 'present']);
        $old = Student::factory()->create();
        Attendance::factory()->create(['student_id' => $old->id, 'course_id' => $course->id, 'date' => now()->subYear(), 'status' => 'absent']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Absences');
        $response->assertSee(route('absence-report.index', ['period' => 'today']), false);
        $response->assertSee(route('absence-report.index', ['period' => 'week']), false);
        $response->assertSee(route('absence-report.index', ['period' => 'month']), false);
        $response->assertSee(route('absence-report.index', ['period' => 'year']), false);
    }

    public function test_dashboard_shows_live_counts_and_totals(): void
    {
        $user = User::factory()->create();

        Student::factory()->count(3)->create();
        Instructor::factory()->count(2)->create();
        Certificate::factory()->count(4)->create();

        Payment::factory()->create(['amount' => 500, 'status' => 'paid', 'payment_date' => now()]);
        Payment::factory()->create(['amount' => 300, 'status' => 'paid', 'payment_date' => now()]);
        // Not counted towards today's payments total.
        Payment::factory()->create(['amount' => 999, 'status' => 'pending', 'payment_date' => now()]);
        Payment::factory()->create(['amount' => 700, 'status' => 'paid', 'payment_date' => now()->subDays(2)]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('3');
        $response->assertSee('800.00');
        $response->assertSee('2');
        $response->assertSee('4');
    }

    public function test_dashboard_shows_a_new_leads_count_linking_to_the_filtered_lead_list(): void
    {
        $user = User::factory()->create();

        Lead::factory()->count(2)->create(['status' => 'new']);
        Lead::factory()->create(['status' => 'contacted']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('New Leads');
        $response->assertSee(route('leads.index', ['status' => 'new']), false);
    }

    public function test_dashboard_shows_new_students_today_this_week_this_month_and_this_year(): void
    {
        $user = User::factory()->create();

        // Registered today: counted in today, this week, this month, and this year.
        Student::factory()->create(['enrollment_date' => now()]);
        // Earlier this month but outside the current week.
        Student::factory()->create(['enrollment_date' => now()->startOfMonth()]);
        // A previous year, not counted in today/week/month/year.
        Student::factory()->create(['enrollment_date' => now()->subYear()]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('New Students');
        $response->assertSee('Today');
        $response->assertSee('This Year');
    }

    public function test_the_new_students_cards_link_to_the_student_registration_report(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee(route('student-registration-report.index', ['period' => 'today']), false);
        $response->assertSee(route('student-registration-report.index', ['period' => 'year']), false);
    }

    public function test_director_sees_this_week_this_month_and_all_time_payment_totals(): void
    {
        $director = User::factory()->director()->create();

        // In the current week and month.
        Payment::factory()->create(['amount' => 500, 'status' => 'paid', 'payment_date' => now()]);
        // Earlier this month but outside the current week.
        Payment::factory()->create(['amount' => 300, 'status' => 'paid', 'payment_date' => now()->startOfMonth()]);
        // A previous month, still counted in the all-time total.
        Payment::factory()->create(['amount' => 200, 'status' => 'paid', 'payment_date' => now()->subYear()]);

        $response = $this->actingAs($director)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('This Week');
        $response->assertSee('This Month');
        $response->assertSee('All Time');
        $response->assertSee('1,000.00');
    }

    public function test_the_total_payments_cards_link_to_the_payments_index_by_period(): void
    {
        $director = User::factory()->director()->create();

        $response = $this->actingAs($director)->get('/dashboard');

        $response->assertOk();
        $response->assertSee(route('payments.index', ['period' => 'week']), false);
        $response->assertSee(route('payments.index', ['period' => 'month']), false);
        $response->assertSee(route('payments.index', ['period' => 'all_time']), false);
    }

    public function test_non_director_does_not_see_the_payment_totals_breakdown(): void
    {
        $user = User::factory()->admin()->create();

        Payment::factory()->create(['amount' => 500, 'status' => 'paid', 'payment_date' => now()]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('Total Payments');
        $response->assertDontSee('All Time');
    }

    public function test_dashboard_shows_zeroes_when_there_is_no_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('₦0.00', false);
    }

    public function test_dashboard_has_a_student_search_bar(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee(route('students.index'), false);
        $response->assertSee('name="search"', false);
    }

    public function test_dashboard_lists_students_with_outstanding_balances(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Chidinma Eze']);
        $course = Course::factory()->create(['fee' => 1000]);
        $student->courses()->attach($course->id, [
            'enrolled_at' => now(),
            'due_date' => now()->addDays(3),
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Outstanding Payments');
        $response->assertSee('Chidinma Eze');
        $response->assertSee('1,000.00');
    }

    public function test_dashboard_lists_completed_enrollments_with_an_outstanding_balance(): void
    {
        // Training completion no longer implies payment is cleared, so a
        // completed-but-unpaid enrollment must still surface here.
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Completed But Owing']);
        $course = Course::factory()->create(['fee' => 500]);
        $student->courses()->attach($course->id, [
            'enrolled_at' => now(),
            'due_date' => now()->addDays(3),
            'status' => 'completed',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Outstanding Payments');
        $response->assertSee('Completed But Owing');
        $response->assertSee('500.00');
    }

    public function test_dashboard_does_not_list_fully_paid_enrollments_as_outstanding(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Fully Paid Student']);
        $course = Course::factory()->create(['fee' => 500]);
        $student->courses()->attach($course->id, [
            'enrolled_at' => now(),
            'due_date' => now()->addDays(3),
            'status' => 'active',
        ]);
        Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 500,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('Outstanding Payments');
    }

    public function test_dashboard_shows_outstanding_payments_as_upcoming_and_locked_count_boxes(): void
    {
        // Once an enrollment actually goes overdue the system locks it on
        // its own (Enrollment::applyLockingRules()), so there's no
        // separate "overdue" state to show here - just still-active
        // students who owe money (Upcoming) and the ones already locked
        // over it (Locked), combined in one widget instead of two.
        $user = User::factory()->create();
        $upcoming = Student::factory()->create(['name' => 'Upcoming Payer']);
        $upcomingCourse = Course::factory()->create(['fee' => 2000]);
        $upcoming->courses()->attach($upcomingCourse->id, [
            'enrolled_at' => now(),
            'due_date' => now()->addDays(3),
            'status' => 'active',
        ]);

        $locked = Student::factory()->create(['name' => 'Locked Payer']);
        $lockedCourse = Course::factory()->create(['fee' => 1000]);
        $locked->courses()->attach($lockedCourse->id, [
            'enrolled_at' => now(),
            'due_date' => now()->subDays(2),
            'status' => 'locked',
            'locked_reason' => 'overdue_balance',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Outstanding Payments');
        $response->assertSeeInOrder(['Upcoming', '1']);
        $response->assertSeeInOrder(['Locked', '1']);
        $response->assertSee('Upcoming Payer');
        $response->assertSee('Locked Payer');
    }

    public function test_dashboard_does_not_double_count_a_locked_enrollment_as_upcoming(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Only Locked']);
        $course = Course::factory()->create(['fee' => 1000]);
        $student->courses()->attach($course->id, [
            'enrolled_at' => now(),
            'due_date' => now()->subDays(2),
            'status' => 'locked',
            'locked_reason' => 'overdue_balance',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSeeInOrder(['Locked', '1']);
        $response->assertDontSee('Upcoming');
    }

    public function test_dashboard_shows_student_training_progress(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Tobi Fashola']);
        $course = Course::factory()->create(['name' => 'Two Week Program', 'duration_weeks' => 2]);
        $student->courses()->attach($course->id, [
            'enrolled_at' => now(),
            'due_date' => now()->addDays(3),
            'status' => 'active',
        ]);
        for ($day = 1; $day <= 3; $day++) {
            Attendance::factory()->create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'date' => now()->subDays($day)->toDateString(),
                'status' => 'present',
                'duration' => 1,
            ]);
        }

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Student Training Progress');
        // Lean, operational stats on each card: Required, Completed, Remaining.
        $response->assertSee('Required');
        $response->assertSee('Remaining');
        $response->assertSee('Tobi Fashola');
        $response->assertSee('Two Week Program');
        // 2-week program = 10 required days, 3 completed, 7 remaining, 30%.
        $response->assertSee('30%');
        $response->assertSee('Active');
    }

    public function test_dashboard_shows_a_student_who_has_not_checked_in_today_as_absent(): void
    {
        $this->travelTo(Carbon::parse('next Monday')->setTime(10, 0));

        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Ngozi Chukwu']);
        $course = Course::factory()->create(['name' => 'Weekday Program', 'schedule' => 'weekday']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Click to view names');
        $response->assertSee('Ngozi Chukwu');
        $response->assertSee('Weekday Program');
    }

    public function test_the_present_tile_still_shows_as_zero_when_nobody_has_checked_in_yet(): void
    {
        $this->travelTo(Carbon::parse('next Monday')->setTime(10, 0));

        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['schedule' => 'weekday']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSeeInOrder(['Present', '0']);
        $response->assertSee('No one has checked in yet today.');
    }

    public function test_checking_in_moves_a_student_from_absent_to_present(): void
    {
        $this->travelTo(Carbon::parse('next Monday')->setTime(10, 0));

        $user = User::factory()->create();
        $instructor = Instructor::factory()->create(['name' => 'Instructor A']);
        $student = Student::factory()->create(['name' => 'Ajayi Sulaiman']);
        $course = Course::factory()->create(['name' => 'Weekday Program', 'schedule' => 'weekday']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'date' => today()->toDateString(),
            'status' => 'present',
            'type' => 'practical',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Ajayi Sulaiman');
        $response->assertSee('Practical Training');
        $response->assertSee('Instructor A');
        $response->assertSee('Checked in');
    }

    public function test_the_absent_tile_shows_zero_once_everyone_expected_has_checked_in(): void
    {
        $this->travelTo(Carbon::parse('next Monday')->setTime(10, 0));

        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['schedule' => 'weekday']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => today()->toDateString(),
            'status' => 'present',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSeeInOrder(['Absent', '0']);
        $response->assertSee('Everyone expected today has checked in.');
    }

    public function test_the_absent_tile_ignores_students_whose_course_does_not_meet_today(): void
    {
        $this->travelTo(Carbon::parse('next Monday')->setTime(10, 0));

        $user = User::factory()->create();

        // Expected today, so the widget actually renders and this test
        // can check who does and doesn't end up in its Absent count.
        $weekdayStudent = Student::factory()->create(['name' => 'Weekday Student']);
        $weekdayCourse = Course::factory()->create(['schedule' => 'weekday']);
        $weekdayStudent->courses()->attach($weekdayCourse->id, ['enrolled_at' => now(), 'status' => 'active']);

        $weekendStudent = Student::factory()->create(['name' => 'Weekend Only Student']);
        $weekendCourse = Course::factory()->create(['name' => 'Weekend Program', 'schedule' => 'weekend']);
        $weekendStudent->courses()->attach($weekendCourse->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        // Monday isn't a weekend-schedule training day, so only the
        // weekday student is expected, and only they show up as absent -
        // the count of 1 (not 2) is what actually proves the exclusion,
        // since the weekend student's name can still legitimately appear
        // elsewhere on the dashboard (e.g. Student Training Progress).
        $response->assertSeeInOrder(['Absent', '1', 'Weekday Student']);
    }

    public function test_the_absent_tile_shows_a_count_of_distinct_students(): void
    {
        $this->travelTo(Carbon::parse('next Monday')->setTime(10, 0));

        $user = User::factory()->create();
        $course = Course::factory()->create(['schedule' => 'weekday']);

        $first = Student::factory()->create();
        $first->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $second = Student::factory()->create();
        $second->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSeeInOrder(['Absent', '2']);
    }

    public function test_nobody_is_expected_on_sunday(): void
    {
        $this->travelTo(Carbon::parse('next Sunday')->setTime(10, 0));

        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['schedule' => 'weekday']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        // The school is closed Sundays, so even an otherwise-expected
        // weekday student isn't counted as absent - but the widget itself
        // still shows, same as any other day.
        $response->assertSeeInOrder(["Today's Attendance", 'Present', '0', 'Absent', '0']);
    }

    public function test_dashboard_always_shows_the_attendance_widget_even_with_nothing_to_show(): void
    {
        $this->travelTo(Carbon::parse('next Monday')->setTime(10, 0));

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        // Staff shouldn't have to wonder whether the dashboard broke - the
        // widget is always there, it just shows 0/0 when there's genuinely
        // nothing to report.
        $response->assertSeeInOrder(["Today's Attendance", 'Present', '0', 'Absent', '0']);
    }

    public function test_dashboard_shows_completed_training_status(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['duration_weeks' => 1]);
        $student->courses()->attach($course->id, [
            'enrolled_at' => now(),
            'due_date' => now()->addDays(2),
            'status' => 'completed',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Completed');
    }

    public function test_dashboard_shows_expired_training_status_for_locked_enrollments(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['duration_weeks' => 1]);
        $student->courses()->attach($course->id, [
            'enrolled_at' => now(),
            'due_date' => now()->subDays(2),
            'status' => 'locked',
            'locked_reason' => 'overdue_balance',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Expired');
    }

    public function test_dashboard_lists_locked_students_with_their_lock_reason(): void
    {
        $user = User::factory()->create();
        $overdueStudent = Student::factory()->create(['name' => 'Overdue Student']);
        $overdueCourse = Course::factory()->create(['fee' => 15000]);
        $overdueStudent->courses()->attach($overdueCourse->id, [
            'enrolled_at' => now(),
            'due_date' => now()->subDays(2),
            'status' => 'locked',
            'locked_reason' => 'overdue_balance',
        ]);
        $expiredStudent = Student::factory()->create(['name' => 'Expired Student']);
        $expiredCourse = Course::factory()->create();
        $expiredStudent->courses()->attach($expiredCourse->id, [
            'enrolled_at' => now()->subMonths(3),
            'due_date' => now()->addDays(30),
            'status' => 'locked',
            'locked_reason' => 'training_period_expired',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Locked Students');
        $response->assertSee('Overdue Student');
        $response->assertSee('Overdue Balance');
        $response->assertSee('15,000.00');
        $response->assertSee('Expired Student');
        $response->assertSee('Training Period Expired');
        $response->assertSeeInOrder(['Locked', '2']);
    }

    public function test_dashboard_does_not_show_the_locked_students_section_when_nobody_is_locked(): void
    {
        $user = User::factory()->create();
        Student::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('Locked Students');
    }

    public function test_dashboard_only_shows_the_reactivate_link_to_directors_for_expired_training_period_locks(): void
    {
        $director = User::factory()->director()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, [
            'enrolled_at' => now()->subMonths(3),
            'due_date' => now()->addDays(30),
            'status' => 'locked',
            'locked_reason' => 'training_period_expired',
        ]);
        $overdueStudent = Student::factory()->create();
        $overdueCourse = Course::factory()->create();
        $overdueStudent->courses()->attach($overdueCourse->id, [
            'enrolled_at' => now(),
            'due_date' => now()->subDays(2),
            'status' => 'locked',
            'locked_reason' => 'overdue_balance',
        ]);

        $directorResponse = $this->actingAs($director)->get('/dashboard');
        $directorResponse->assertOk();
        $directorResponse->assertSee('Reactivate');

        $adminResponse = $this->actingAs($admin)->get('/dashboard');
        $adminResponse->assertOk();
        $adminResponse->assertDontSee('Reactivate');
    }

    public function test_dashboard_shows_a_service_in_progress_with_a_tracked_turnaround(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Jane Roe']);
        $service = Service::factory()->create(['name' => "Driver's License Processing", 'processing_days' => 30]);
        $student->studentServices()->create([
            'service_id' => $service->id,
            'price' => $service->price,
            'processing_status' => 'processing',
            'processing_started_at' => now()->subDays(15),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Service Processing');
        $response->assertSee('Jane Roe');
        $response->assertSee("Driver's License Processing");
        $response->assertSee('50%');
    }

    public function test_dashboard_does_not_crash_on_a_processing_service_with_no_started_at(): void
    {
        // Data inconsistency (e.g. legacy data predating processing_started_at
        // always being stamped alongside processing_status) must degrade
        // gracefully rather than 500 the whole dashboard.
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'No Start Date']);
        $service = Service::factory()->create(['processing_days' => 30]);
        $student->studentServices()->create([
            'service_id' => $service->id,
            'price' => $service->price,
            'processing_status' => 'processing',
            'processing_started_at' => null,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('No Start Date');
    }

    public function test_dashboard_flags_overdue_service_processing(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $service = Service::factory()->create(['processing_days' => 30]);
        $student->studentServices()->create([
            'service_id' => $service->id,
            'price' => $service->price,
            'processing_status' => 'processing',
            'processing_started_at' => now()->subDays(45),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Overdue');
    }

    public function test_dashboard_does_not_show_a_service_with_no_tracked_turnaround(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $service = Service::factory()->create(['name' => "Learner's Permit", 'processing_days' => null]);
        $student->studentServices()->create([
            'service_id' => $service->id,
            'price' => $service->price,
            'processing_status' => 'processing',
            'processing_started_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('Service Processing');
    }

    public function test_dashboard_does_not_show_completed_or_not_started_service_processing(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $service = Service::factory()->create(['processing_days' => 30]);
        $student->studentServices()->create([
            'service_id' => $service->id,
            'price' => $service->price,
            'processing_status' => 'completed',
            'processing_started_at' => now()->subDays(31),
        ]);
        $otherService = Service::factory()->create(['processing_days' => 30]);
        $student->studentServices()->create([
            'service_id' => $otherService->id,
            'price' => $otherService->price,
            'processing_status' => 'not_started',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('Service Processing');
    }

    protected function attendDays(Student $student, Course $course, int $days): void
    {
        for ($i = 0; $i < $days; $i++) {
            Attendance::factory()->create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'status' => 'present',
                'duration' => 1,
                'date' => now()->subDays($days - $i)->toDateString(),
            ]);
        }
    }

    public function test_dashboard_shows_upgrade_window_stat_boxes_with_correct_counts_and_modal_contents(): void
    {
        $director = User::factory()->director()->create();
        $twoWeek = Course::factory()->create(['name' => 'Two Week Program', 'duration_weeks' => 2, 'course_type' => 'manual', 'schedule' => 'weekday', 'status' => 'active']);
        $threeWeek = Course::factory()->create(['duration_weeks' => 3, 'course_type' => 'manual', 'schedule' => 'weekday', 'status' => 'active']);
        Course::factory()->create(['duration_weeks' => 4, 'course_type' => 'manual', 'schedule' => 'weekday', 'status' => 'active']);

        $eligible = Student::factory()->create(['name' => 'John A']);
        $eligible->courses()->attach($threeWeek->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => $threeWeek->fee]);
        $this->attendDays($eligible, $threeWeek, 2);

        $lastDay = Student::factory()->create(['name' => 'David C']);
        $lastDay->courses()->attach($threeWeek->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => $threeWeek->fee]);
        $this->attendDays($lastDay, $threeWeek, 5);

        $closed = Student::factory()->create(['name' => 'Sarah D']);
        $closed->courses()->attach($twoWeek->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => $twoWeek->fee]);
        $this->attendDays($closed, $twoWeek, 6);

        $response = $this->actingAs($director)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Programme Upgrade Window');

        // Two compact count boxes, not a table of names directly on the page.
        $response->assertSee('Eligible for Upgrade');
        $response->assertSee('Upgrade Window Closed');

        // Eligible modal: both John (still days left) and David (last day).
        $response->assertSee('John A');
        $response->assertSee('David C');
        $response->assertSee('Closes today');

        // Closed modal: Sarah's window ended, but she's still listed (behind
        // the click, not directly on the page) rather than dropped entirely.
        $response->assertSee('Sarah D');
    }

    public function test_dashboard_does_not_list_an_enrollment_with_no_longer_programme_to_upgrade_into(): void
    {
        $user = User::factory()->create();
        $fourWeek = Course::factory()->create(['duration_weeks' => 4, 'status' => 'active']);
        $student = Student::factory()->create(['name' => 'Already Longest']);
        $student->courses()->attach($fourWeek->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => $fourWeek->fee]);
        $this->attendDays($student, $fourWeek, 2);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('Programme Upgrade Window');
    }

    public function test_dashboard_keeps_counting_an_enrollment_long_after_its_upgrade_window_closed(): void
    {
        $user = User::factory()->create();
        $twoWeek = Course::factory()->create(['duration_weeks' => 2, 'course_type' => 'manual', 'schedule' => 'weekday', 'status' => 'active']);
        Course::factory()->create(['duration_weeks' => 4, 'course_type' => 'manual', 'schedule' => 'weekday', 'status' => 'active']);
        $student = Student::factory()->create(['name' => 'Long Since Closed']);
        $student->courses()->attach($twoWeek->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => $twoWeek->fee]);
        $this->attendDays($student, $twoWeek, 10);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Upgrade Window Closed');
        $response->assertSee('Long Since Closed');
        $response->assertDontSee('Eligible for Upgrade');
    }

    public function test_a_director_gets_a_direct_upgrade_link_from_the_dashboard(): void
    {
        $director = User::factory()->director()->create();
        $secretary = User::factory()->secretary()->create();
        $twoWeek = Course::factory()->create(['duration_weeks' => 2, 'course_type' => 'manual', 'schedule' => 'weekday', 'status' => 'active']);
        Course::factory()->create(['duration_weeks' => 4, 'course_type' => 'manual', 'schedule' => 'weekday', 'status' => 'active']);
        $student = Student::factory()->create();
        $student->courses()->attach($twoWeek->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => $twoWeek->fee]);
        $enrollment = $student->courses()->where('course_id', $twoWeek->id)->first()->pivot;
        $this->attendDays($student, $twoWeek, 1);

        $directorResponse = $this->actingAs($director)->get('/dashboard');
        $directorResponse->assertOk();
        $directorResponse->assertSee(route('enrollments.upgrade.create', $enrollment->id), false);

        $secretaryResponse = $this->actingAs($secretary)->get('/dashboard');
        $secretaryResponse->assertOk();
        $secretaryResponse->assertDontSee(route('enrollments.upgrade.create', $enrollment->id), false);
    }

    public function test_dashboard_greets_the_user_by_first_name_and_time_of_day(): void
    {
        $this->travelTo(now()->setTime(9, 0));
        $user = User::factory()->create(['name' => 'Ada Okafor']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSeeInOrder(['Good Morning', 'Ada']);
    }

    public function test_dashboard_greets_with_good_afternoon_and_good_evening_at_the_right_times(): void
    {
        $user = User::factory()->create();

        $this->travelTo(now()->setTime(14, 0));
        $this->actingAs($user)->get('/dashboard')->assertSee('Good Afternoon');

        $this->travelTo(now()->setTime(20, 0));
        $this->actingAs($user)->get('/dashboard')->assertSee('Good Evening');
    }

    public function test_dashboard_shows_the_kpi_cards(): void
    {
        $user = User::factory()->create();
        Student::factory()->create(['status' => 'active']);
        Student::factory()->create(['status' => 'withdrawn']);
        Vehicle::factory()->create(['status' => 'active']);
        Vehicle::factory()->create(['status' => 'inactive']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Active Students');
        $response->assertSee('Training Today');
        $response->assertSee('Pending Payments');
        $response->assertSee('Completed Training');
        $response->assertSee('Active Vehicles');
        $response->assertSee('Certificates Due');
    }

    public function test_the_active_students_kpi_only_counts_active_students(): void
    {
        $user = User::factory()->create();
        Student::factory()->count(3)->create(['status' => 'active']);
        Student::factory()->create(['status' => 'withdrawn']);
        Student::factory()->create(['status' => 'completed']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSeeInOrder(['Active Students', '3']);
    }

    public function test_the_active_vehicles_kpi_only_counts_active_vehicles(): void
    {
        $user = User::factory()->create();
        Vehicle::factory()->count(2)->create(['status' => 'active']);
        Vehicle::factory()->create(['status' => 'inactive']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSeeInOrder(['Active Vehicles', '2']);
    }

    public function test_the_certificates_due_kpi_counts_completed_enrollments_without_a_certificate(): void
    {
        $user = User::factory()->create();

        $withCertificate = Student::factory()->create();
        $courseA = Course::factory()->create();
        $withCertificate->courses()->attach($courseA->id, ['enrolled_at' => now(), 'status' => 'completed', 'fee' => 1000]);
        Certificate::factory()->create(['student_id' => $withCertificate->id, 'course_id' => $courseA->id]);

        $withoutCertificate = Student::factory()->create();
        $courseB = Course::factory()->create();
        $withoutCertificate->courses()->attach($courseB->id, ['enrolled_at' => now(), 'status' => 'completed', 'fee' => 1000]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSeeInOrder(['Certificates Due', '1']);
    }

    public function test_the_pending_payments_kpi_sums_every_outstanding_enrollment_balance(): void
    {
        $user = User::factory()->create();
        $studentA = Student::factory()->create();
        $courseA = Course::factory()->create();
        $studentA->courses()->attach($courseA->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 1000]);
        $studentB = Student::factory()->create();
        $courseB = Course::factory()->create();
        $studentB->courses()->attach($courseB->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 2500]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('3,500.00');
    }

    public function test_todays_operations_panel_shows_todays_activity(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $instructor = Instructor::factory()->create();
        $vehicle = Vehicle::factory()->create();
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'vehicle_id' => $vehicle->id,
            'date' => today(),
            'status' => 'present',
        ]);
        // Not counted: a login from a different day.
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => today()->subDay(),
            'status' => 'present',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee("Today's Operations");
        $response->assertSeeInOrder(['1', 'Student(s) Trained Today']);
        $response->assertSeeInOrder(['1', 'Training Session(s) Logged']);
        $response->assertSeeInOrder(['1', 'Instructor(s) Active Today']);
        $response->assertSeeInOrder(['1', 'Vehicle(s) In Use Today']);
    }

    public function test_todays_operations_flags_students_approaching_completion_and_locked_students(): void
    {
        $user = User::factory()->create();

        $nearlyDone = Student::factory()->create();
        $course = Course::factory()->create(['duration_weeks' => 1]);
        $nearlyDone->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 1000]);
        for ($day = 1; $day <= 3; $day++) {
            Attendance::factory()->create([
                'student_id' => $nearlyDone->id,
                'course_id' => $course->id,
                'date' => now()->subDays($day)->toDateString(),
                'status' => 'present',
                'duration' => 1,
            ]);
        }

        $locked = Student::factory()->create();
        $lockedCourse = Course::factory()->create();
        $locked->courses()->attach($lockedCourse->id, [
            'enrolled_at' => now(),
            'due_date' => now()->subDays(2),
            'status' => 'locked',
            'locked_reason' => 'overdue_balance',
            'fee' => 1000,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSeeInOrder(['1', 'Approaching Completion']);
        $response->assertSeeInOrder(['1', 'Student(s) Locked']);

        // The "Approaching Completion" tile must open a modal listing the
        // flagged student(s) by name, not link off to the full unfiltered
        // Training Progress list.
        $response->assertSee('approaching-completion-modal');
        $response->assertSee($nearlyDone->name);
    }

    public function test_a_director_sees_the_pending_approvals_count_on_the_dashboard(): void
    {
        $director = User::factory()->director()->create();
        DiscountRequest::factory()->create();
        StudentCorrectionRequest::factory()->create();

        $response = $this->actingAs($director)->get('/dashboard');

        $response->assertOk();
        $response->assertSeeInOrder(['2', 'Approval(s) Pending']);
    }

    public function test_a_secretary_does_not_see_the_pending_approvals_link(): void
    {
        $secretary = User::factory()->secretary()->create();
        DiscountRequest::factory()->create();

        $response = $this->actingAs($secretary)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('Approval(s) Pending');
    }

    public function test_dashboard_lists_a_pending_learners_permit_request(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Pending Permit']);
        $service = Service::factory()->create(['name' => "Learner's Permit", 'price' => 6000]);
        $student->studentServices()->create(['service_id' => $service->id, 'price' => 6000, 'processing_status' => 'not_started']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee("Learner's Permit Requests");
        $response->assertSee('Pending Permit');
        $response->assertSee('Mark Obtained');
    }

    public function test_dashboard_does_not_list_an_already_obtained_learners_permit(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Already Obtained']);
        $service = Service::factory()->create(['name' => "Learner's Permit", 'price' => 6000]);
        // Paid in full so this doesn't also surface in the Revenue Leakage widget.
        $student->studentServices()->create(['service_id' => $service->id, 'price' => 0, 'processing_status' => 'completed']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee("Learner's Permit Requests");
        $response->assertDontSee('Already Obtained');
    }

    public function test_dashboard_does_not_list_a_pending_service_that_is_not_a_learners_permit(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Other Service Student']);
        $service = Service::factory()->create(['name' => "Driver's License Processing", 'price' => 50000]);
        $student->studentServices()->create(['service_id' => $service->id, 'price' => 50000, 'processing_status' => 'not_started']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee("Learner's Permit Requests");
    }

    public function test_dashboard_lists_a_pending_online_certificate_request(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Pending Certificate']);
        $service = Service::factory()->create(['name' => 'Online Certificate', 'price' => 20000]);
        $student->studentServices()->create(['service_id' => $service->id, 'price' => 20000, 'processing_status' => 'not_started']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Online Certificate Requests');
        $response->assertSee('Pending Certificate');
        $response->assertSee('Mark Obtained');
    }

    public function test_dashboard_does_not_list_an_already_obtained_online_certificate(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Already Certified']);
        $service = Service::factory()->create(['name' => 'Online Certificate', 'price' => 20000]);
        // Paid in full so this doesn't also surface in the Revenue Leakage widget.
        $student->studentServices()->create(['service_id' => $service->id, 'price' => 0, 'processing_status' => 'completed']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('Online Certificate Requests');
        $response->assertDontSee('Already Certified');
    }

    public function test_dashboard_lists_a_driving_license_charge_that_has_not_started_processing(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Not Started Yet']);
        $service = Service::factory()->create(['name' => "Driver's License Processing", 'price' => 50000, 'processing_days' => 30]);
        $student->studentServices()->create(['service_id' => $service->id, 'price' => 50000, 'processing_status' => 'not_started']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee("Driver's License Requests");
        $response->assertSee('Not Started Yet');
        $response->assertSee('Start Processing');
    }

    public function test_learners_permit_widget_stats_cover_every_charge_not_just_pending_ones(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => "Learner's Permit", 'price' => 6000]);

        // Fully paid, still pending (not_started) - counts as "Paid".
        $fullyPaid = Student::factory()->create()->studentServices()->create(['service_id' => $service->id, 'price' => 6000]);
        $payment = Payment::factory()->create(['status' => 'paid']);
        PaymentAllocation::factory()->create(['payment_id' => $payment->id, 'allocation_type' => 'service', 'student_service_id' => $fullyPaid->id, 'amount' => 6000]);

        // Part paid, still pending - does not count as "Paid".
        $partPaid = Student::factory()->create()->studentServices()->create(['service_id' => $service->id, 'price' => 6000]);
        $partPayment = Payment::factory()->create(['status' => 'paid']);
        PaymentAllocation::factory()->create(['payment_id' => $partPayment->id, 'allocation_type' => 'service', 'student_service_id' => $partPaid->id, 'amount' => 3000]);

        // Unpaid, still pending.
        Student::factory()->create()->studentServices()->create(['service_id' => $service->id, 'price' => 6000]);

        // Already obtained - not part of the pending list below, but still
        // counted in the lifetime "Charged"/"Total Students"/"Permit
        // Obtained" tiles above it.
        Student::factory()->create()->studentServices()->create(['service_id' => $service->id, 'price' => 6000, 'processing_status' => 'completed']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSeeInOrder(['4', 'Total Students']);
        $response->assertSeeInOrder(['4', 'Charged']);
        $response->assertSeeInOrder(['1', 'Paid']);
        $response->assertSeeInOrder(['1', 'Permit Obtained']);
    }

    public function test_dashboard_does_not_duplicate_a_driving_license_already_shown_in_service_processing(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Already Processing']);
        $service = Service::factory()->create(['name' => "Driver's License Processing", 'price' => 50000, 'processing_days' => 30]);
        $student->studentServices()->create([
            'service_id' => $service->id,
            'price' => 50000,
            'processing_status' => 'processing',
            'processing_started_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Service Processing');
        $response->assertDontSee("Driver's License Requests");
    }

    public function test_revenue_leakage_flags_an_unpaid_certificate_fee_on_a_completed_enrollment(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Leaked Certificate']);
        $course = Course::factory()->create(['fee' => 95000, 'online_certificate_fee' => 20000]);
        $student->courses()->attach($course->id, [
            'enrolled_at' => now(),
            'status' => 'completed',
            'fee' => 95000,
            'online_certificate_fee' => 20000,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Revenue Leakage');
        $response->assertSee('Leaked Certificate');
        $response->assertSee('Online Certificate — '.$course->name);
        $response->assertSeeInOrder(['Revenue Leakage', '20,000.00']);
    }

    public function test_revenue_leakage_flags_an_unpaid_completed_service(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Leaked Service']);
        $service = Service::factory()->create(['name' => "Learner's Permit", 'price' => 6000]);
        $student->studentServices()->create(['service_id' => $service->id, 'price' => 6000, 'processing_status' => 'completed']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Leaked Service');
        $response->assertSee("Learner's Permit");
        $response->assertSeeInOrder(['Revenue Leakage', '6,000.00']);
    }

    public function test_revenue_leakage_ignores_a_fully_paid_certificate_fee(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Paid In Full']);
        $course = Course::factory()->create(['fee' => 95000, 'online_certificate_fee' => 20000]);
        $student->courses()->attach($course->id, [
            'enrolled_at' => now(),
            'status' => 'completed',
            'fee' => 95000,
            'online_certificate_fee' => 20000,
        ]);
        $enrollment = $student->courses()->first()->pivot;
        Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 115000,
            'status' => 'paid',
        ])->allocations()->create([
            'enrollment_id' => $enrollment->id,
            'allocation_type' => 'online_certificate',
            'amount' => 20000,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSeeInOrder(['Revenue Leakage', '0.00']);
        $response->assertDontSee('Online Certificate — '.$course->name);
    }

    public function test_revenue_leakage_ignores_a_service_still_processing(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Still Processing']);
        $service = Service::factory()->create(['name' => 'Still Processing Service', 'price' => 6000]);
        $student->studentServices()->create(['service_id' => $service->id, 'price' => 6000, 'processing_status' => 'processing']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSeeInOrder(['Revenue Leakage', '0.00']);
        $response->assertDontSee('Still Processing Service');
    }

    public function test_the_revenue_leakage_kpi_sums_every_leaked_balance(): void
    {
        $user = User::factory()->create();

        $studentA = Student::factory()->create();
        $courseA = Course::factory()->create(['fee' => 95000, 'student_certificate_fee' => 1000]);
        $studentA->courses()->attach($courseA->id, [
            'enrolled_at' => now(),
            'status' => 'completed',
            'fee' => 95000,
            'student_certificate_fee' => 1000,
        ]);

        $studentB = Student::factory()->create();
        $service = Service::factory()->create(['price' => 6000]);
        $studentB->studentServices()->create(['service_id' => $service->id, 'price' => 6000, 'processing_status' => 'completed']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSeeInOrder(['Revenue Leakage', '7,000.00']);
    }

    public function test_dashboard_does_not_flag_a_fully_paid_student_no_matter_how_long_absent(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 50000]);
        $student = Student::factory()->create(['name' => 'Paid And Quiet']);
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->subDays(60)->toDateString(),
            'status' => 'active',
        ]);
        Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 50000,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('Flagged');
    }

    public function test_dashboard_flags_a_student_absent_well_past_the_check_in_reminder_as_at_risk(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 50000]);
        $student = Student::factory()->create(['name' => 'Gone Quiet']);
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->subDays(9)->toDateString(),
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('At-Risk Students');
        $response->assertSee('Gone Quiet');
        $response->assertSee('Medium');
        $response->assertSee('Absent 9 day(s)');
    }

    public function test_dashboard_flags_a_student_with_a_balance_due_soon_as_at_risk(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 50000]);
        $student = Student::factory()->create(['name' => 'Due Soon']);
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
            'status' => 'active',
        ]);
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'present',
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Due Soon');
        $response->assertSee('Payment due in 2 day(s)');
    }

    public function test_dashboard_marks_a_student_with_both_risk_signals_as_high_risk(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 50000]);
        $student = Student::factory()->create(['name' => 'Double Trouble']);
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->subDays(9)->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Double Trouble');
        $response->assertSee('High');
        $response->assertSee('Absent 9 day(s) · Payment due in 2 day(s)');
    }

    public function test_dashboard_does_not_flag_a_student_who_trained_recently_with_no_balance_due(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 50000]);
        $student = Student::factory()->create(['name' => 'On Track']);
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->subDays(2)->toDateString(),
            'status' => 'active',
        ]);
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'present',
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('Flagged');
    }

    public function test_dashboard_does_not_flag_an_already_locked_enrollment_as_at_risk(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 50000]);
        $student = Student::factory()->create(['name' => 'Already Locked']);
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->subDays(20)->toDateString(),
            'due_date' => now()->subDays(6)->toDateString(),
            'status' => 'locked',
            'locked_reason' => 'overdue_balance',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('Flagged');
    }

    public function test_the_at_risk_students_kpi_counts_flagged_enrollments(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 50000]);
        $student = Student::factory()->create();
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->subDays(9)->toDateString(),
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSeeInOrder(['At-Risk Students', '1']);
    }
}
