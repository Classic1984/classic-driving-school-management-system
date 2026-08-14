<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_payment_routes(): void
    {
        $payment = Payment::factory()->create();

        $this->get('/payments')->assertRedirect('/login');
        $this->get('/payments/create')->assertRedirect('/login');
        $this->get("/payments/{$payment->id}")->assertRedirect('/login');
        $this->get("/payments/{$payment->id}/edit")->assertRedirect('/login');
        $this->post('/payments', [])->assertRedirect('/login');
        $this->put("/payments/{$payment->id}", [])->assertRedirect('/login');
        $this->delete("/payments/{$payment->id}")->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_payment_index(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Paying Student']);
        Payment::factory()->create(['student_id' => $student->id]);

        $response = $this->actingAs($user)->get('/payments');

        $response->assertOk();
        $response->assertSee('Paying Student');
    }

    public function test_payment_index_shows_the_automatically_calculated_total_for_today(): void
    {
        $user = User::factory()->create();
        Payment::factory()->create(['amount' => 500, 'status' => 'paid', 'payment_date' => now()->toDateString()]);
        Payment::factory()->create(['amount' => 300, 'status' => 'paid', 'payment_date' => now()->toDateString()]);
        // Not counted: a pending payment today, and a paid payment from yesterday.
        Payment::factory()->create(['amount' => 999, 'status' => 'pending', 'payment_date' => now()->toDateString()]);
        Payment::factory()->create(['amount' => 999, 'status' => 'paid', 'payment_date' => now()->subDay()->toDateString()]);

        $response = $this->actingAs($user)->get('/payments');

        $response->assertOk();
        $response->assertSee('800.00');
    }

    public function test_a_secretary_does_not_see_the_week_month_or_all_time_totals(): void
    {
        $secretary = User::factory()->secretary()->create();
        Payment::factory()->create(['amount' => 500, 'status' => 'paid', 'payment_date' => now()->toDateString()]);

        $response = $this->actingAs($secretary)->get('/payments');

        $response->assertOk();
        $response->assertSee("Today's Total");
        $response->assertDontSee('This Week');
        $response->assertDontSee('This Month');
        $response->assertDontSee('All Time');
    }

    public function test_a_director_sees_the_week_month_and_all_time_totals(): void
    {
        $director = User::factory()->director()->create();
        Payment::factory()->create(['amount' => 500, 'status' => 'paid', 'payment_date' => now()->toDateString()]);

        $response = $this->actingAs($director)->get('/payments');

        $response->assertOk();
        $response->assertSee("Today's Total");
        $response->assertSee('This Week');
        $response->assertSee('This Month');
        $response->assertSee('All Time');
    }

    public function test_the_weekly_period_filters_payments_to_this_week_only(): void
    {
        $user = User::factory()->create();
        $thisWeek = Student::factory()->create(['name' => 'This Week Payer']);
        $lastMonth = Student::factory()->create(['name' => 'Last Month Payer']);
        Payment::factory()->create(['student_id' => $thisWeek->id, 'amount' => 400, 'status' => 'paid', 'payment_date' => now()->startOfWeek()->toDateString()]);
        Payment::factory()->create(['student_id' => $lastMonth->id, 'amount' => 999, 'status' => 'paid', 'payment_date' => now()->subMonth()->toDateString()]);

        $response = $this->actingAs($user)->get('/payments?period=week');

        $response->assertOk();
        $response->assertSee('This Week Payer');
        $response->assertDontSee('Last Month Payer');
        $response->assertSee('400.00');
    }

    public function test_the_monthly_period_filters_payments_to_this_month_only(): void
    {
        $user = User::factory()->create();
        $thisMonth = Student::factory()->create(['name' => 'This Month Payer']);
        $lastYear = Student::factory()->create(['name' => 'Last Year Payer']);
        Payment::factory()->create(['student_id' => $thisMonth->id, 'status' => 'paid', 'payment_date' => now()->startOfMonth()->toDateString()]);
        Payment::factory()->create(['student_id' => $lastYear->id, 'status' => 'paid', 'payment_date' => now()->subYear()->toDateString()]);

        $response = $this->actingAs($user)->get('/payments?period=month');

        $response->assertOk();
        $response->assertSee('This Month Payer');
        $response->assertDontSee('Last Year Payer');
    }

    public function test_the_all_time_period_shows_every_payment_regardless_of_date(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Old Payer']);
        Payment::factory()->create(['student_id' => $student->id, 'status' => 'paid', 'payment_date' => now()->subYears(2)->toDateString()]);

        $response = $this->actingAs($user)->get('/payments?period=all_time');

        $response->assertOk();
        $response->assertSee('Old Payer');
    }

    public function test_an_invalid_payment_period_falls_back_to_all_time(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Fallback Payer']);
        Payment::factory()->create(['student_id' => $student->id, 'status' => 'paid', 'payment_date' => now()->subYears(2)->toDateString()]);

        $response = $this->actingAs($user)->get('/payments?period=not-a-real-period');

        $response->assertOk();
        $response->assertSee('Fallback Payer');
    }

    public function test_authenticated_user_can_view_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/payments/create');

        $response->assertOk();
    }

    public function test_authenticated_user_can_store_a_payment(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $data = [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 150.50,
            'payment_date' => '2026-01-15',
            'payment_method' => 'card',
            'status' => 'paid',
            'reference_number' => 'PAY-00001',
            'notes' => 'First installment.',
        ];

        $response = $this->actingAs($user)->post('/payments', $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/payments');

        $this->assertDatabaseHas('payments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 150.50,
            'reference_number' => 'PAY-00001',
        ]);
    }

    public function test_storing_a_payment_requires_valid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/payments', [
            'student_id' => '',
            'course_id' => '',
            'amount' => -5,
            'payment_date' => '',
            'payment_method' => 'invalid-method',
            'status' => 'invalid-status',
        ]);

        $response->assertSessionHasErrors([
            'student_id', 'course_id', 'amount', 'payment_date', 'payment_method', 'status',
        ]);

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_storing_a_payment_requires_unique_reference_number(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);
        Payment::factory()->create(['reference_number' => 'DUPLICATE-REF']);

        $response = $this->actingAs($user)->post('/payments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 100,
            'payment_date' => '2026-01-15',
            'payment_method' => 'cash',
            'status' => 'paid',
            'reference_number' => 'DUPLICATE-REF',
        ]);

        $response->assertSessionHasErrors('reference_number');
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_storing_a_payment_rejects_a_future_payment_date(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($user)->post('/payments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 100,
            'payment_date' => now()->addDay()->toDateString(),
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);

        $response->assertSessionHasErrors('payment_date');
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_storing_a_payment_requires_the_student_to_be_enrolled_in_the_course(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();

        $response = $this->actingAs($user)->post('/payments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 100,
            'payment_date' => '2026-01-15',
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);

        $response->assertSessionHasErrors('student_id');
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_updating_a_payment_requires_the_student_to_be_enrolled_in_the_course(): void
    {
        $user = User::factory()->create();
        $payment = Payment::factory()->create(['status' => 'pending']);
        $otherCourse = Course::factory()->create();

        $response = $this->actingAs($user)->put("/payments/{$payment->id}", [
            'student_id' => $payment->student_id,
            'course_id' => $otherCourse->id,
            'amount' => $payment->amount,
            'payment_date' => $payment->payment_date->format('Y-m-d'),
            'payment_method' => $payment->payment_method,
            'status' => 'paid',
        ]);

        $response->assertSessionHasErrors('student_id');
        $this->assertSame('pending', $payment->fresh()->status);
    }

    public function test_updating_a_payment_rejects_a_future_payment_date(): void
    {
        $user = User::factory()->create();
        $payment = Payment::factory()->create(['status' => 'pending']);
        $payment->student->courses()->attach($payment->course_id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($user)->put("/payments/{$payment->id}", [
            'student_id' => $payment->student_id,
            'course_id' => $payment->course_id,
            'amount' => $payment->amount,
            'payment_date' => now()->addDay()->toDateString(),
            'payment_method' => $payment->payment_method,
            'status' => 'paid',
        ]);

        $response->assertSessionHasErrors('payment_date');
        $this->assertSame('pending', $payment->fresh()->status);
    }

    public function test_updating_a_payment_can_correct_the_date_to_an_earlier_day(): void
    {
        $user = User::factory()->create();
        $payment = Payment::factory()->create(['status' => 'paid', 'payment_date' => now()]);
        $payment->student->courses()->attach($payment->course_id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($user)->put("/payments/{$payment->id}", [
            'student_id' => $payment->student_id,
            'course_id' => $payment->course_id,
            'amount' => $payment->amount,
            'payment_date' => now()->subDay()->toDateString(),
            'payment_method' => $payment->payment_method,
            'status' => 'paid',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(now()->subDay()->toDateString(), $payment->fresh()->payment_date->toDateString());
    }

    public function test_authenticated_user_can_view_a_payment(): void
    {
        $user = User::factory()->create();
        $payment = Payment::factory()->create();

        $response = $this->actingAs($user)->get("/payments/{$payment->id}");

        $response->assertOk();
        $response->assertSee($payment->student->name);
    }

    public function test_authenticated_user_can_update_a_payment(): void
    {
        $user = User::factory()->create();
        $payment = Payment::factory()->create(['status' => 'pending']);
        $payment->student->courses()->attach($payment->course_id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($user)->put("/payments/{$payment->id}", [
            'student_id' => $payment->student_id,
            'course_id' => $payment->course_id,
            'amount' => $payment->amount,
            'payment_date' => $payment->payment_date->format('Y-m-d'),
            'payment_method' => $payment->payment_method,
            'status' => 'paid',
            'reference_number' => $payment->reference_number,
            'notes' => 'Confirmed.',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/payments');

        $this->assertSame('paid', $payment->fresh()->status);
    }

    public function test_authenticated_user_can_delete_a_payment(): void
    {
        $user = User::factory()->create();
        $payment = Payment::factory()->create();

        $response = $this->actingAs($user)->delete("/payments/{$payment->id}");

        $response->assertRedirect('/payments');
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }

    public function test_authenticated_user_can_export_payments_as_csv(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'CSV Student']);
        $course = Course::factory()->create(['name' => 'CSV Course']);
        Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 250,
            'status' => 'paid',
            'reference_number' => 'PAY-CSV-1',
        ]);

        $response = $this->actingAs($user)->get('/payments/export');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $content = $response->streamedContent();

        $this->assertStringContainsString('CSV Student', $content);
        $this->assertStringContainsString('CSV Course', $content);
        $this->assertStringContainsString('250.00', $content);
        $this->assertStringContainsString('PAY-CSV-1', $content);
    }

    public function test_student_page_shows_payments_and_total_paid(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        Payment::factory()->create(['student_id' => $student->id, 'amount' => 100, 'status' => 'paid']);
        Payment::factory()->create(['student_id' => $student->id, 'amount' => 50, 'status' => 'paid']);
        Payment::factory()->create(['student_id' => $student->id, 'amount' => 999, 'status' => 'pending']);

        $response = $this->actingAs($user)->get("/students/{$student->id}");

        $response->assertOk();
        $response->assertSee('150.00');
    }
}
