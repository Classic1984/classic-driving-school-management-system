<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentReceiptTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $payment = Payment::factory()->create();

        $this->get("/payments/{$payment->id}/receipt")->assertRedirect('/login');
    }

    public function test_a_payment_is_assigned_a_receipt_number_automatically(): void
    {
        $payment = Payment::factory()->create(['payment_date' => '2026-03-15']);

        $this->assertMatchesRegularExpression('/^CDS-RC-2026-\d{5}$/', $payment->receipt_number);
    }

    public function test_receipt_numbers_are_unique_per_payment(): void
    {
        $first = Payment::factory()->create();
        $second = Payment::factory()->create();

        $this->assertNotSame($first->receipt_number, $second->receipt_number);
    }

    public function test_the_receipt_shows_the_payment_breakdown_and_resulting_balances(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Jane Doe']);
        $course = Course::factory()->create(['name' => 'Beginner Course']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;
        $service = Service::factory()->create(['name' => "Learner's Permit", 'price' => 6000]);
        $studentService = $student->studentServices()->create(['service_id' => $service->id, 'price' => 6000]);

        $this->actingAs($user)->post('/payments/record', [
            'student_id' => $student->id,
            'amount' => 26000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'allocations' => [
                ['type' => 'training', 'id' => $enrollment->id, 'amount' => 20000],
                ['type' => 'service', 'id' => $studentService->id, 'amount' => 6000],
            ],
        ])->assertSessionHasNoErrors();

        $payment = Payment::whereNull('course_id')->firstOrFail();

        $response = $this->actingAs($user)->get("/payments/{$payment->id}/receipt");

        $response->assertOk();
        $response->assertSee($payment->receipt_number);
        $response->assertSee('Jane Doe');
        $response->assertSee('Training — Beginner Course');
        $response->assertSee("Learner's Permit");
        $response->assertSee('26,000.00');
        // Training balance after this payment: 95000 - 20000 = 75000.
        $response->assertSee('75,000.00');
        // The permit is now fully paid, so its balance no longer
        // contributes to "Total Outstanding" - only the training balance
        // does.
        $response->assertSee($user->name);
    }

    public function test_recorded_by_is_set_for_both_payment_entry_flows(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;

        $this->actingAs($user)->post('/payments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 100,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'status' => 'paid',
        ])->assertSessionHasNoErrors();

        $singleCoursePayment = Payment::where('course_id', $course->id)->firstOrFail();
        $this->assertSame($user->id, $singleCoursePayment->recorded_by);

        $this->actingAs($user)->post('/payments/record', [
            'student_id' => $student->id,
            'amount' => 200,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'allocations' => [
                ['type' => 'training', 'id' => $enrollment->id, 'amount' => 200],
            ],
        ])->assertSessionHasNoErrors();

        $multiServicePayment = Payment::whereNull('course_id')->firstOrFail();
        $this->assertSame($user->id, $multiServicePayment->recorded_by);
    }

    public function test_a_receipt_still_renders_for_a_legacy_single_course_payment(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 50000]);

        $payment = Payment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 20000,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($user)->get("/payments/{$payment->id}/receipt");

        $response->assertOk();
        $response->assertSee($payment->receipt_number);
        $response->assertSee('Training — '.$course->name);
    }
}
