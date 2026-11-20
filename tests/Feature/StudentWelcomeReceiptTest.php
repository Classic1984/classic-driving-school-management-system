<?php

namespace Tests\Feature;

use App\Mail\StudentReceiptMail;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class StudentWelcomeReceiptTest extends TestCase
{
    use RefreshDatabase;

    public function test_registering_with_an_initial_payment_emails_a_welcome_receipt(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $this->actingAs($user)->post('/students', [
            'name' => 'John Smith',
            'email' => 'john.smith@example.com',
            'phone' => '555-0100',
            'date_of_birth' => '2000-01-15',
            'course_type' => 'manual',
            'enrollment_date' => now()->toDateString(),
            'course_id' => $course->id,
            'amount_paid' => 50000,
            'payment_method' => 'cash',
        ])->assertSessionHasNoErrors();

        $student = Student::where('email', 'john.smith@example.com')->firstOrFail();
        $payment = Payment::where('student_id', $student->id)->firstOrFail();

        Mail::assertSent(StudentReceiptMail::class, function (StudentReceiptMail $mail) use ($student, $payment) {
            return $mail->hasTo($student->email)
                && $mail->student->is($student)
                && $mail->payment->is($payment)
                && str_contains($mail->pdfContent, '%PDF');
        });
    }

    public function test_the_welcome_email_shows_the_outstanding_balances_due_date(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $this->actingAs($user)->post('/students', [
            'name' => 'Due Date Student',
            'email' => 'due.date.student@example.com',
            'phone' => '555-0100',
            'date_of_birth' => '2000-01-15',
            'course_type' => 'manual',
            'enrollment_date' => now()->toDateString(),
            'course_id' => $course->id,
            'amount_paid' => 50000,
            'payment_method' => 'cash',
        ])->assertSessionHasNoErrors();

        $student = Student::where('email', 'due.date.student@example.com')->firstOrFail();
        $enrollment = $student->courses()->firstOrFail()->pivot;

        Mail::assertSent(StudentReceiptMail::class, function (StudentReceiptMail $mail) use ($enrollment) {
            $html = $mail->render();

            return str_contains($html, '45,000.00')
                && str_contains($html, $enrollment->due_date->format('M j, Y'));
        });
    }

    public function test_registering_without_an_initial_payment_sends_no_email(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $this->actingAs($user)->post('/students', [
            'name' => 'Grace Okoro',
            'email' => 'grace.okoro@example.com',
            'phone' => '555-0100',
            'date_of_birth' => '2000-01-15',
            'course_type' => 'manual',
            'enrollment_date' => now()->toDateString(),
            'course_id' => $course->id,
        ])->assertSessionHasNoErrors();

        Mail::assertNothingSent();
    }

    public function test_registration_still_succeeds_even_if_sending_the_welcome_email_fails(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('Mail provider unavailable'));

        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $response = $this->actingAs($user)->post('/students', [
            'name' => 'Resilient Student',
            'email' => 'resilient.student@example.com',
            'phone' => '555-0100',
            'date_of_birth' => '2000-01-15',
            'course_type' => 'manual',
            'enrollment_date' => now()->toDateString(),
            'course_id' => $course->id,
            'amount_paid' => 20000,
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHasNoErrors();
        $student = Student::where('email', 'resilient.student@example.com')->first();
        $response->assertRedirect("/students/{$student->id}");
        $this->assertDatabaseHas('payments', ['student_id' => $student->id, 'amount' => 20000]);
    }

    public function test_a_director_can_download_a_payment_receipt_as_a_pdf(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        $payment = Payment::factory()->create(['student_id' => $student->id, 'status' => 'paid']);

        $response = $this->actingAs($director)->get(route('payments.receipt.pdf', $payment));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }
}
