<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_certificates_qr_summary_includes_name_id_program_and_instructor(): void
    {
        $student = Student::factory()->create(['name' => 'Jane Doe']);
        $course = Course::factory()->create(['name' => 'Beginner Training']);
        $instructor = Instructor::factory()->create(['name' => 'John Smith']);
        $certificate = Certificate::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
        ]);

        $summary = $certificate->qrCodeSummary();

        $this->assertStringContainsString('Jane Doe', $summary);
        $this->assertStringContainsString($student->student_id_number, $summary);
        $this->assertStringContainsString('Beginner Training', $summary);
        $this->assertStringContainsString('John Smith', $summary);
    }

    public function test_a_certificates_qr_summary_omits_the_instructor_line_when_there_is_none(): void
    {
        $certificate = Certificate::factory()->create(['instructor_id' => null]);

        $this->assertStringNotContainsString('Instructor:', $certificate->qrCodeSummary());
    }

    public function test_the_certificate_page_renders_a_qr_code(): void
    {
        $user = User::factory()->create();
        $certificate = Certificate::factory()->create();

        $response = $this->actingAs($user)->get("/certificates/{$certificate->id}");

        $response->assertOk();
        $response->assertSee('<svg', false);
    }

    public function test_a_payments_qr_summary_uses_its_own_course_when_set(): void
    {
        $student = Student::factory()->create(['name' => 'Jane Doe']);
        $course = Course::factory()->create(['name' => 'Beginner Training']);
        $instructor = Instructor::factory()->create(['name' => 'John Smith']);
        $course->instructors()->attach($instructor->id);
        $payment = Payment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id]);

        $summary = $payment->qrCodeSummary();

        $this->assertStringContainsString('Jane Doe', $summary);
        $this->assertStringContainsString($student->student_id_number, $summary);
        $this->assertStringContainsString('Beginner Training', $summary);
        $this->assertStringContainsString('John Smith', $summary);
    }

    public function test_a_multi_service_payments_qr_summary_falls_back_to_the_students_enrolled_course(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['name' => 'Refresher Course']);
        $instructor = Instructor::factory()->create(['name' => 'Ada Lovelace']);
        $course->instructors()->attach($instructor->id);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 65000]);
        $service = Service::factory()->create(['name' => "Learner's Permit", 'price' => 6000]);
        $studentService = $student->studentServices()->create(['service_id' => $service->id, 'price' => 6000]);

        $this->actingAs($user)->post('/payments/record', [
            'student_id' => $student->id,
            'amount' => 6000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'allocations' => [
                ['type' => 'service', 'id' => $studentService->id, 'amount' => 6000],
            ],
        ])->assertSessionHasNoErrors();

        $payment = Payment::whereNull('course_id')->firstOrFail();
        $payment->load('student.courses', 'allocations.enrollment.course.instructors');

        $summary = $payment->qrCodeSummary();

        $this->assertStringContainsString('Refresher Course', $summary);
        $this->assertStringContainsString('Ada Lovelace', $summary);
    }

    public function test_a_payments_qr_summary_omits_program_when_the_student_has_no_enrollment(): void
    {
        $student = Student::factory()->create();
        $payment = Payment::factory()->create(['student_id' => $student->id, 'course_id' => null]);
        $payment->load('student.courses', 'course.instructors', 'allocations');

        $this->assertStringNotContainsString('Program:', $payment->qrCodeSummary());
        $this->assertStringNotContainsString('Instructor:', $payment->qrCodeSummary());
    }

    public function test_the_receipt_page_renders_a_qr_code(): void
    {
        $user = User::factory()->create();
        $payment = Payment::factory()->create();

        $response = $this->actingAs($user)->get("/payments/{$payment->id}/receipt");

        $response->assertOk();
        $response->assertSee('<svg', false);
    }
}
