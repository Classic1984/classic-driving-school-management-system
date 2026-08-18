<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Service;
use App\Models\Student;
use App\Models\StudentService;
use Database\Seeders\ServicePriceListSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_student_service_charge_with_no_payments_is_unpaid(): void
    {
        $charge = StudentService::factory()->create(['price' => 50000]);

        $this->assertSame(0.0, $charge->amountPaid());
        $this->assertSame(50000.0, $charge->balance());
        $this->assertSame('unpaid', $charge->status());
    }

    public function test_a_partially_paid_student_service_charge_reports_part_payment(): void
    {
        $charge = StudentService::factory()->create(['price' => 50000]);
        $payment = Payment::factory()->create(['status' => 'paid']);

        PaymentAllocation::factory()->create([
            'payment_id' => $payment->id,
            'allocation_type' => 'service',
            'student_service_id' => $charge->id,
            'amount' => 20000,
        ]);

        $this->assertSame(20000.0, $charge->amountPaid());
        $this->assertSame(30000.0, $charge->balance());
        $this->assertSame('part_payment', $charge->status());
    }

    public function test_a_fully_paid_student_service_charge_reports_paid(): void
    {
        $charge = StudentService::factory()->create(['price' => 50000]);
        $payment = Payment::factory()->create(['status' => 'paid']);

        PaymentAllocation::factory()->create([
            'payment_id' => $payment->id,
            'allocation_type' => 'service',
            'student_service_id' => $charge->id,
            'amount' => 50000,
        ]);

        $this->assertSame(0.0, $charge->balance());
        $this->assertSame('paid', $charge->status());
    }

    public function test_allocations_on_a_non_paid_payment_do_not_count_toward_a_charges_balance(): void
    {
        $charge = StudentService::factory()->create(['price' => 50000]);
        $pendingPayment = Payment::factory()->create(['status' => 'pending']);

        PaymentAllocation::factory()->create([
            'payment_id' => $pendingPayment->id,
            'allocation_type' => 'service',
            'student_service_id' => $charge->id,
            'amount' => 50000,
        ]);

        $this->assertSame(0.0, $charge->amountPaid());
        $this->assertSame('unpaid', $charge->status());
    }

    public function test_an_enrollment_without_a_certificate_fee_has_a_null_certificate_balance(): void
    {
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);

        $enrollment = $student->courses()->first()->pivot;

        $this->assertNull($enrollment->onlineCertificateBalance());
        $this->assertNull($enrollment->studentCertificateBalance());
    }

    public function test_an_enrollments_certificate_balance_is_charged_price_minus_paid_allocations(): void
    {
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, [
            'enrolled_at' => now(),
            'status' => 'active',
            'fee' => 95000,
            'online_certificate_fee' => 20000,
            'student_certificate_fee' => 1000,
        ]);

        $enrollment = $student->courses()->first()->pivot;

        $payment = Payment::factory()->create(['status' => 'paid']);
        PaymentAllocation::factory()->create([
            'payment_id' => $payment->id,
            'allocation_type' => 'online_certificate',
            'enrollment_id' => $enrollment->id,
            'amount' => 12000,
        ]);
        PaymentAllocation::factory()->create([
            'payment_id' => $payment->id,
            'allocation_type' => 'student_certificate',
            'enrollment_id' => $enrollment->id,
            'amount' => 1000,
        ]);

        $enrollment->refresh();

        $this->assertSame(8000.0, $enrollment->onlineCertificateBalance());
        $this->assertSame(0.0, $enrollment->studentCertificateBalance());
    }

    public function test_certificate_allocations_never_bleed_into_the_training_balance(): void
    {
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, [
            'enrolled_at' => now(),
            'status' => 'active',
            'fee' => 95000,
            'online_certificate_fee' => 20000,
        ]);

        $enrollment = $student->courses()->first()->pivot;

        $payment = Payment::factory()->create(['status' => 'paid']);
        PaymentAllocation::factory()->create([
            'payment_id' => $payment->id,
            'allocation_type' => 'online_certificate',
            'enrollment_id' => $enrollment->id,
            'amount' => 20000,
        ]);

        $enrollment->refresh();

        // The certificate charge is fully paid, but the training fee itself
        // is untouched - amountPaid()/balance() only ever look at the
        // payments table directly and know nothing about allocations yet
        // (that rewiring is a later phase), so this proves the two ledgers
        // stay independent even once allocations exist.
        $this->assertSame(0.0, $enrollment->onlineCertificateBalance());
        $this->assertSame(95000.0, $enrollment->balance());
    }

    public function test_the_service_price_list_seeder_creates_the_flat_catalog_services(): void
    {
        $this->seed(ServicePriceListSeeder::class);

        $this->assertDatabaseCount('services', 4);
        $this->assertDatabaseHas('services', [
            'name' => "Driver's License Processing",
            'price' => 50000,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('services', [
            'name' => "Learner's Permit",
            'price' => 6000,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('services', [
            'name' => 'Online Certificate',
            'price' => 20000,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('services', [
            'name' => 'Student Certificate',
            'price' => 1000,
            'is_active' => true,
        ]);
    }

    public function test_running_the_service_price_list_seeder_twice_does_not_duplicate_services(): void
    {
        $this->seed(ServicePriceListSeeder::class);
        $this->seed(ServicePriceListSeeder::class);

        $this->assertDatabaseCount('services', 4);
    }

    public function test_a_student_can_only_be_charged_once_for_the_same_service(): void
    {
        $student = Student::factory()->create();
        $service = Service::factory()->create();

        StudentService::factory()->create(['student_id' => $student->id, 'service_id' => $service->id]);

        $this->expectException(QueryException::class);

        StudentService::factory()->create(['student_id' => $student->id, 'service_id' => $service->id]);
    }
}
