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
