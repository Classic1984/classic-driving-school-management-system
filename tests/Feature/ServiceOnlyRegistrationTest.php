<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Service;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A walk-in client registering only for a flat catalog service (a
 * Learner's Permit, Driver's Licence Processing, a Certificate) - with no
 * course enrollment at all, unlike every other registration in
 * AdditionalOfferRegistrationTest, which always enrolls in a course
 * alongside any ticked offers.
 */
class ServiceOnlyRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function registrationData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Bola Ade',
            'email' => 'bola.ade@example.com',
            'phone' => '555-0177',
            'date_of_birth' => '1995-03-10',
            'enrollment_date' => now()->toDateString(),
            'status' => 'active',
        ], $overrides);
    }

    public function test_the_create_form_offers_a_registration_type_toggle(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/students/create');

        $response->assertOk();
        $response->assertSee('Registration Type');
        $response->assertSee('Enroll in a Course');
        $response->assertSee('Register for a Service Only');
    }

    public function test_registering_for_services_only_creates_no_enrollment(): void
    {
        $user = User::factory()->create();
        $permit = Service::factory()->create(['name' => "Learner's Permit", 'price' => 6000]);
        $license = Service::factory()->create(['name' => "Driver's License Processing", 'price' => 50000]);

        $response = $this->actingAs($user)->post('/students', $this->registrationData([
            'service_ids' => [$permit->id, $license->id],
        ]));

        $response->assertSessionHasNoErrors();
        $student = Student::where('email', 'bola.ade@example.com')->firstOrFail();

        $this->assertTrue($student->courses->isEmpty());
        $this->assertDatabaseHas('student_services', [
            'student_id' => $student->id,
            'service_id' => $permit->id,
            'price' => 6000,
        ]);
        $this->assertDatabaseHas('student_services', [
            'student_id' => $student->id,
            'service_id' => $license->id,
            'price' => 50000,
        ]);
    }

    public function test_registering_for_services_only_does_not_require_course_type(): void
    {
        $user = User::factory()->create();
        $permit = Service::factory()->create(['price' => 6000]);

        $response = $this->actingAs($user)->post('/students', $this->registrationData([
            'service_ids' => [$permit->id],
        ]));

        $response->assertSessionHasNoErrors();
        $student = Student::where('email', 'bola.ade@example.com')->firstOrFail();
        $this->assertNull($student->course_type);
    }

    public function test_paying_for_services_at_a_service_only_registration_allocates_no_training_charge(): void
    {
        $user = User::factory()->create();
        $permit = Service::factory()->create(['name' => "Learner's Permit", 'price' => 6000]);
        $license = Service::factory()->create(['name' => "Driver's License Processing", 'price' => 50000]);

        $response = $this->actingAs($user)->post('/students', $this->registrationData([
            'service_ids' => [$permit->id, $license->id],
            'service_amounts' => [
                $permit->id => 6000,
                $license->id => 20000,
            ],
            'payment_method' => 'cash',
        ]));

        $response->assertSessionHasNoErrors();
        $student = Student::where('email', 'bola.ade@example.com')->firstOrFail();

        $payment = Payment::where('student_id', $student->id)->firstOrFail();
        $this->assertNull($payment->course_id);
        $this->assertSame(26000.0, (float) $payment->amount);

        $this->assertSame(0, PaymentAllocation::where('payment_id', $payment->id)->where('allocation_type', 'training')->count());
        $this->assertSame(2, PaymentAllocation::where('payment_id', $payment->id)->where('allocation_type', 'service')->count());

        $permitCharge = $student->studentServices()->where('service_id', $permit->id)->firstOrFail();
        $licenseCharge = $student->studentServices()->where('service_id', $license->id)->firstOrFail();

        $this->assertSame(6000.0, $permitCharge->amountPaid());
        $this->assertSame('paid', $permitCharge->status());
        $this->assertSame(20000.0, $licenseCharge->amountPaid());
        $this->assertSame(30000.0, $licenseCharge->balance());
    }

    public function test_registering_without_a_course_or_any_service_fails_validation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/students', $this->registrationData());

        $response->assertSessionHasErrors('course_id');
        $this->assertDatabaseCount('students', 0);
    }

    public function test_registering_into_a_course_still_requires_course_type(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $response = $this->actingAs($user)->post('/students', $this->registrationData([
            'course_id' => $course->id,
        ]));

        $response->assertSessionHasErrors('course_type');
        $this->assertDatabaseCount('students', 0);
    }
}
