<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_dashboard_shows_new_students_today_this_week_and_this_month(): void
    {
        $user = User::factory()->create();

        // Registered today: counted in today, this week, and this month.
        Student::factory()->create(['enrollment_date' => now()]);
        // Earlier this month but outside the current week.
        Student::factory()->create(['enrollment_date' => now()->startOfMonth()]);
        // A previous month, not counted in today/week/month.
        Student::factory()->create(['enrollment_date' => now()->subYear()]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('New Students');
        $response->assertSee('Today');
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
            ]);
        }

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Student Training Progress');
        $response->assertSee('Tobi Fashola');
        $response->assertSee($student->student_id_number);
        $response->assertSee('Two Week Program');
        // 2-week program = 10 total days, 3 attended, 7 remaining, 30% complete.
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
}
