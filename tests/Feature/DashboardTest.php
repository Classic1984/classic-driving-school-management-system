<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        // Lean, operational columns: Student, Programme, Required, Completed, Remaining, %, Status.
        $response->assertSee('Programme');
        $response->assertSee('Required');
        $response->assertSee('Remaining');
        $response->assertSee('Tobi Fashola');
        $response->assertSee('Two Week Program');
        // 2-week program = 10 required days, 3 completed, 7 remaining, 30%.
        $response->assertSee('30%');
        $response->assertSee('Active');
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
        $overdueCourse = Course::factory()->create();
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
        $response->assertSee('Expired Student');
        $response->assertSee('Training Period Expired');
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
}
