<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_payments_description_summarizes_its_allocations(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['name' => 'Beginner Course']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;
        $service = Service::factory()->create(['name' => "Learner's Permit", 'price' => 6000]);
        $studentService = $student->studentServices()->create(['service_id' => $service->id, 'price' => 6000]);

        $this->actingAs($user)->post('/payments/record', [
            'student_id' => $student->id,
            'amount' => 26000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'allocations' => [
                ['type' => 'training', 'id' => $enrollment->id, 'amount' => 20000],
                ['type' => 'service', 'id' => $studentService->id, 'amount' => 6000],
            ],
        ])->assertSessionHasNoErrors();

        $payment = Payment::whereNull('course_id')->firstOrFail();

        $this->assertSame("Training — Beginner Course, Learner's Permit", $payment->description());
    }

    public function test_a_payment_with_no_allocations_has_a_placeholder_description(): void
    {
        $payment = Payment::factory()->create();

        $this->assertSame('—', $payment->description());
    }

    public function test_the_payments_index_shows_receipt_number_description_and_recorded_by(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);

        $this->actingAs($user)->post('/payments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 100,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'status' => 'paid',
        ])->assertSessionHasNoErrors();

        $payment = Payment::firstOrFail();

        $response = $this->actingAs($user)->get('/payments');

        $response->assertOk();
        $response->assertSee($payment->receipt_number);
        $response->assertSee('Training — '.$course->name);
        $response->assertSee($user->name);
    }

    public function test_the_student_pages_receipt_number_links_to_the_payment_detail_page(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);

        $this->actingAs($user)->post('/payments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 100,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'status' => 'paid',
        ])->assertSessionHasNoErrors();

        $payment = Payment::firstOrFail();

        $response = $this->actingAs($user)->get("/students/{$student->id}");

        $response->assertOk();
        $response->assertSee(route('payments.show', $payment), false);
    }

    public function test_the_payment_detail_page_shows_the_full_allocation_breakdown(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['name' => 'Beginner Course']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;
        $service = Service::factory()->create(['name' => "Learner's Permit", 'price' => 6000]);
        $studentService = $student->studentServices()->create(['service_id' => $service->id, 'price' => 6000]);

        $this->actingAs($user)->post('/payments/record', [
            'student_id' => $student->id,
            'amount' => 26000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'allocations' => [
                ['type' => 'training', 'id' => $enrollment->id, 'amount' => 20000],
                ['type' => 'service', 'id' => $studentService->id, 'amount' => 6000],
            ],
        ])->assertSessionHasNoErrors();

        $payment = Payment::whereNull('course_id')->firstOrFail();

        $response = $this->actingAs($user)->get("/payments/{$payment->id}");

        $response->assertOk();
        $response->assertSee($payment->receipt_number);
        $response->assertSee('Training — Beginner Course');
        $response->assertSee("Learner's Permit");
        $response->assertSee('20,000.00');
        $response->assertSee('6,000.00');
        $response->assertSee($user->name);
    }
}
