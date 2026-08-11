<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Payment;
use App\Models\PaymentReversal;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentReversalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $payment = Payment::factory()->create();

        $this->get("/payments/{$payment->id}/reverse")->assertRedirect('/login');
        $this->post("/payments/{$payment->id}/reverse", [])->assertRedirect('/login');
    }

    public function test_a_secretary_cannot_reverse_a_payment(): void
    {
        $secretary = User::factory()->secretary()->create();
        $payment = Payment::factory()->create(['status' => 'paid']);

        $this->actingAs($secretary)->get("/payments/{$payment->id}/reverse")->assertForbidden();
        $this->actingAs($secretary)->post("/payments/{$payment->id}/reverse", ['reason' => 'x'])->assertForbidden();
    }

    public function test_an_admin_can_reverse_a_paid_payment(): void
    {
        $admin = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;

        $payment = Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 30000,
            'status' => 'paid',
        ]);
        $this->assertSame(65000.0, $enrollment->fresh()->balance());

        $response = $this->actingAs($admin)->post("/payments/{$payment->id}/reverse", [
            'reason' => 'Payment duplicated.',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect("/payments/{$payment->id}");

        $this->assertSame('refunded', $payment->fresh()->status);
        $this->assertDatabaseHas('payment_reversals', [
            'payment_id' => $payment->id,
            'reversed_by' => $admin->id,
            'amount' => 30000,
            'reason' => 'Payment duplicated.',
        ]);

        // The original payment record itself is never deleted.
        $this->assertDatabaseHas('payments', ['id' => $payment->id]);
    }

    public function test_a_reversal_removes_the_payment_from_the_students_balance(): void
    {
        $admin = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;

        $payment = Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 30000,
            'status' => 'paid',
        ]);
        $this->assertSame(65000.0, $enrollment->fresh()->balance());

        $this->actingAs($admin)->post("/payments/{$payment->id}/reverse", [
            'reason' => 'Payment duplicated.',
        ])->assertSessionHasNoErrors();

        $this->assertSame(95000.0, $enrollment->fresh()->balance());
    }

    public function test_a_reversal_reopens_a_completed_enrollment_if_the_balance_no_longer_covers_it(): void
    {
        $admin = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['duration_hours' => 0, 'duration_weeks' => 0]);
        $student->courses()->attach($course->id, [
            'enrolled_at' => now(),
            'status' => 'completed',
            'fee' => 30000,
        ]);
        $enrollment = $student->courses()->first()->pivot;

        $payment = Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 30000,
            'status' => 'paid',
        ]);

        $this->actingAs($admin)->post("/payments/{$payment->id}/reverse", [
            'reason' => 'Payment duplicated.',
        ])->assertSessionHasNoErrors();

        $this->assertNotSame('completed', $enrollment->fresh()->status);
    }

    public function test_a_pending_payment_cannot_be_reversed(): void
    {
        $admin = User::factory()->create();
        $payment = Payment::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($admin)->post("/payments/{$payment->id}/reverse", [
            'reason' => 'Trying anyway.',
        ]);

        $response->assertSessionHasErrors('reason');
        $this->assertDatabaseCount('payment_reversals', 0);
        $this->assertSame('pending', $payment->fresh()->status);
    }

    public function test_a_payment_cannot_be_reversed_twice(): void
    {
        $admin = User::factory()->create();
        $payment = Payment::factory()->create(['status' => 'paid']);
        PaymentReversal::factory()->create(['payment_id' => $payment->id]);
        $payment->update(['status' => 'refunded']);

        $response = $this->actingAs($admin)->post("/payments/{$payment->id}/reverse", [
            'reason' => 'Trying again.',
        ]);

        $response->assertSessionHasErrors('reason');
        $this->assertDatabaseCount('payment_reversals', 1);
    }

    public function test_a_reason_is_required_to_reverse_a_payment(): void
    {
        $admin = User::factory()->create();
        $payment = Payment::factory()->create(['status' => 'paid']);

        $response = $this->actingAs($admin)->post("/payments/{$payment->id}/reverse", []);

        $response->assertSessionHasErrors('reason');
        $this->assertSame('paid', $payment->fresh()->status);
    }

    public function test_the_payment_page_shows_reversal_details(): void
    {
        $admin = User::factory()->create();
        $payment = Payment::factory()->create(['status' => 'paid']);

        $this->actingAs($admin)->post("/payments/{$payment->id}/reverse", [
            'reason' => 'Student requested a refund.',
        ])->assertSessionHasNoErrors();

        $response = $this->actingAs($admin)->get("/payments/{$payment->id}");

        $response->assertOk();
        $response->assertSee('Student requested a refund.');
        $response->assertSee($admin->name);
        $response->assertDontSee('Reverse Payment');
    }

    public function test_a_reversed_payment_is_excluded_from_the_finance_summary_income(): void
    {
        $admin = User::factory()->create();
        $payment = Payment::factory()->create(['amount' => 40000, 'status' => 'paid', 'payment_date' => now()]);

        $this->actingAs($admin)->post("/payments/{$payment->id}/reverse", [
            'reason' => 'Payment duplicated.',
        ])->assertSessionHasNoErrors();

        $response = $this->actingAs($admin)->get('/finance');

        $response->assertOk();
        $response->assertDontSee('40,000.00');
    }
}
