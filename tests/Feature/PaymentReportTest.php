<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/payment-reports')->assertRedirect('/login');
    }

    public function test_a_secretary_cannot_view_financial_reports(): void
    {
        $secretary = User::factory()->secretary()->create();

        $this->actingAs($secretary)->get('/payment-reports')->assertForbidden();
    }

    public function test_the_daily_report_breaks_down_payments_by_method(): void
    {
        $director = User::factory()->create();
        Payment::factory()->create(['amount' => 1000, 'status' => 'paid', 'payment_method' => 'cash', 'payment_date' => now()]);
        Payment::factory()->create(['amount' => 2000, 'status' => 'paid', 'payment_method' => 'bank_transfer', 'payment_date' => now()]);
        Payment::factory()->create(['amount' => 3000, 'status' => 'paid', 'payment_method' => 'card', 'payment_date' => now()]);
        Payment::factory()->create(['amount' => 4000, 'status' => 'paid', 'payment_method' => 'mobile_money', 'payment_date' => now()]);
        // Not counted: yesterday's payment, and a pending payment today.
        Payment::factory()->create(['amount' => 9999, 'status' => 'paid', 'payment_method' => 'cash', 'payment_date' => now()->subDay()]);
        Payment::factory()->create(['amount' => 9999, 'status' => 'pending', 'payment_method' => 'cash', 'payment_date' => now()]);

        $response = $this->actingAs($director)->get('/payment-reports');

        $response->assertOk();
        $response->assertSee('1,000.00');
        $response->assertSee('2,000.00');
        $response->assertSee('3,000.00');
        $response->assertSee('4,000.00');
        $response->assertDontSee('9,999.00');
    }

    public function test_the_daily_report_lists_the_services_paid_for(): void
    {
        $director = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['name' => 'Beginner Course']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);

        $this->actingAs($director)->post('/payments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 20000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'status' => 'paid',
        ])->assertSessionHasNoErrors();

        $response = $this->actingAs($director)->get('/payment-reports');

        $response->assertOk();
        $response->assertSee('Training — Beginner Course');
    }

    public function test_the_service_revenue_report_groups_by_service(): void
    {
        $director = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;
        $service = Service::factory()->create(['name' => "Learner's Permit", 'price' => 6000]);
        $studentService = $student->studentServices()->create(['service_id' => $service->id, 'price' => 6000]);

        $this->actingAs($director)->post('/payments/record', [
            'student_id' => $student->id,
            'amount' => 26000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'allocations' => [
                ['type' => 'training', 'id' => $enrollment->id, 'amount' => 20000],
                ['type' => 'service', 'id' => $studentService->id, 'amount' => 6000],
            ],
        ])->assertSessionHasNoErrors();

        $response = $this->actingAs($director)->get('/payment-reports');

        $response->assertOk();
        $response->assertSee('Training');
        $response->assertSee("Learner's Permit");
        $response->assertSee('20,000.00');
        $response->assertSee('6,000.00');
    }

    public function test_a_reversed_payment_is_excluded_from_service_revenue(): void
    {
        $director = User::factory()->create();
        $payment = Payment::factory()->create(['amount' => 15000, 'status' => 'paid']);

        $this->actingAs($director)->post("/payments/{$payment->id}/reverse", [
            'reason' => 'Payment duplicated.',
        ])->assertSessionHasNoErrors();

        $response = $this->actingAs($director)->get('/payment-reports');

        $response->assertOk();
        $response->assertDontSee('15,000.00');
    }

    public function test_the_outstanding_report_lists_students_with_open_balances(): void
    {
        $director = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Owing Student']);
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);

        $paidStudent = Student::factory()->create(['name' => 'Paid Up Student']);
        $paidCourse = Course::factory()->create();
        $paidStudent->courses()->attach($paidCourse->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 50000]);
        Payment::factory()->create(['student_id' => $paidStudent->id, 'course_id' => $paidCourse->id, 'amount' => 50000, 'status' => 'paid']);

        $response = $this->actingAs($director)->get('/payment-reports');

        $response->assertOk();
        $response->assertSee('Owing Student');
        $response->assertDontSee('Paid Up Student');
    }

    public function test_the_outstanding_report_excludes_services_the_student_was_never_charged_for(): void
    {
        $director = User::factory()->create();
        $service = Service::factory()->create(['name' => "Driver's License", 'price' => 50000, 'is_active' => true]);

        $paidStudent = Student::factory()->create(['name' => 'Fully Paid Student']);
        $paidCourse = Course::factory()->create();
        $paidStudent->courses()->attach($paidCourse->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 50000]);
        Payment::factory()->create(['student_id' => $paidStudent->id, 'course_id' => $paidCourse->id, 'amount' => 50000, 'status' => 'paid']);

        $response = $this->actingAs($director)->get('/payment-reports');

        $response->assertOk();
        // The student is fully paid up on training and was never charged
        // for the Driver's License service - it being in the catalog and
        // "available to bill" doesn't make it a debt.
        $response->assertDontSee('Fully Paid Student');
        $response->assertDontSee($service->name);
    }

    public function test_an_invalid_date_falls_back_to_today(): void
    {
        $director = User::factory()->create();
        Payment::factory()->create(['amount' => 5000, 'status' => 'paid', 'payment_method' => 'cash', 'payment_date' => now()]);

        $response = $this->actingAs($director)->get('/payment-reports?date=not-a-real-date');

        $response->assertOk();
        $response->assertSee('5,000.00');
    }

    public function test_the_outstanding_csv_export_downloads(): void
    {
        $director = User::factory()->create();
        $student = Student::factory()->create(['name' => 'CSV Owing Student']);
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);

        $response = $this->actingAs($director)->get('/payment-reports/export');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('CSV Owing Student', $response->streamedContent());
    }

    public function test_the_pdf_export_downloads(): void
    {
        $director = User::factory()->create();

        $response = $this->actingAs($director)->get('/payment-reports/export-pdf');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }
}
