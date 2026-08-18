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

class AdditionalOfferRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function registrationData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'phone' => '555-0100',
            'date_of_birth' => '2000-01-15',
            'course_type' => 'manual',
            'enrollment_date' => now()->toDateString(),
            'status' => 'active',
        ], $overrides);
    }

    public function test_the_registration_page_lists_active_catalog_services_as_additional_offers(): void
    {
        $user = User::factory()->create();
        Service::factory()->create(['name' => "Driver's License Processing", 'price' => 50000, 'is_active' => true]);
        Service::factory()->create(['name' => 'Retired Offer', 'is_active' => false]);

        $response = $this->actingAs($user)->get('/students/create');

        $response->assertOk();
        $response->assertSee("Driver's License Processing");
        $response->assertDontSee('Retired Offer');
    }

    public function test_the_enroll_existing_student_page_does_not_show_additional_offers(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        Service::factory()->create(['name' => "Driver's License Processing", 'is_active' => true]);

        $response = $this->actingAs($user)->get("/students/{$student->id}/enroll");

        $response->assertOk();
        $response->assertDontSee('Additional Offers');
        $response->assertDontSee("Driver's License Processing");
    }

    public function test_ticking_additional_offers_creates_a_separate_charge_for_each_one(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 95000]);
        $license = Service::factory()->create(['name' => "Driver's License Processing", 'price' => 50000]);
        $permit = Service::factory()->create(['name' => "Learner's Permit", 'price' => 6000]);

        $response = $this->actingAs($user)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'service_ids' => [$license->id, $permit->id],
        ]));

        $response->assertSessionHasNoErrors();
        $student = Student::where('email', 'jane.doe@example.com')->firstOrFail();

        $this->assertDatabaseHas('student_services', [
            'student_id' => $student->id,
            'service_id' => $license->id,
            'price' => 50000,
        ]);
        $this->assertDatabaseHas('student_services', [
            'student_id' => $student->id,
            'service_id' => $permit->id,
            'price' => 6000,
        ]);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_per_item_allocation_records_a_separate_amount_for_training_and_each_offer(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 95000]);
        $license = Service::factory()->create(['name' => "Driver's License Processing", 'price' => 50000]);
        $permit = Service::factory()->create(['name' => "Learner's Permit", 'price' => 6000]);

        $response = $this->actingAs($user)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'service_ids' => [$license->id, $permit->id],
            'training_amount' => 40000,
            'service_amounts' => [
                $license->id => 20000,
                $permit->id => 6000,
            ],
            'payment_method' => 'cash',
        ]));

        $response->assertSessionHasNoErrors();
        $student = Student::where('email', 'jane.doe@example.com')->firstOrFail();
        $enrollment = $student->courses->first()->pivot;

        $payment = Payment::where('student_id', $student->id)->firstOrFail();
        $this->assertNull($payment->course_id);
        $this->assertSame(66000.0, (float) $payment->amount);

        $this->assertSame(40000.0, $enrollment->amountPaid());
        $this->assertSame(55000.0, $enrollment->balance());

        $licenseCharge = $student->studentServices()->where('service_id', $license->id)->firstOrFail();
        $permitCharge = $student->studentServices()->where('service_id', $permit->id)->firstOrFail();

        $this->assertSame(20000.0, $licenseCharge->amountPaid());
        $this->assertSame(30000.0, $licenseCharge->balance());
        $this->assertSame('part_payment', $licenseCharge->status());

        $this->assertSame(6000.0, $permitCharge->amountPaid());
        $this->assertSame(0.0, $permitCharge->balance());
        $this->assertSame('paid', $permitCharge->status());

        $this->assertDatabaseHas('payment_allocations', [
            'payment_id' => $payment->id,
            'allocation_type' => 'training',
            'enrollment_id' => $enrollment->id,
            'amount' => 40000,
        ]);
        $this->assertDatabaseHas('payment_allocations', [
            'payment_id' => $payment->id,
            'allocation_type' => 'service',
            'student_service_id' => $licenseCharge->id,
            'amount' => 20000,
        ]);
        $this->assertDatabaseHas('payment_allocations', [
            'payment_id' => $payment->id,
            'allocation_type' => 'service',
            'student_service_id' => $permitCharge->id,
            'amount' => 6000,
        ]);
    }

    public function test_paying_an_offer_in_full_at_registration_starts_its_processing_clock(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 95000]);
        $license = Service::factory()->create(['name' => "Driver's License Processing", 'price' => 50000, 'processing_days' => 30]);

        $this->actingAs($user)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'service_ids' => [$license->id],
            'service_amounts' => [$license->id => 50000],
            'payment_method' => 'cash',
        ]))->assertSessionHasNoErrors();

        $student = Student::where('email', 'jane.doe@example.com')->firstOrFail();
        $licenseCharge = $student->studentServices()->where('service_id', $license->id)->firstOrFail();

        $this->assertSame('processing', $licenseCharge->processing_status);
        $this->assertNotNull($licenseCharge->processing_started_at);
    }

    public function test_registering_without_any_offers_falls_back_to_the_simple_amount_paid_path(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $response = $this->actingAs($user)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'amount_paid' => 30000,
            'payment_method' => 'cash',
        ]));

        $response->assertSessionHasNoErrors();
        $student = Student::where('email', 'jane.doe@example.com')->firstOrFail();
        $enrollment = $student->courses->first()->pivot;

        $this->assertSame(30000.0, $enrollment->amountPaid());
        $this->assertDatabaseHas('payments', ['student_id' => $student->id, 'course_id' => $course->id, 'amount' => 30000]);
        $this->assertDatabaseCount('student_services', 0);

        // The legacy single-course flow still goes through Payment's own
        // saved() hook, which auto-syncs one training PaymentAllocation -
        // this is the pre-existing behavior this feature must not disturb.
        $this->assertDatabaseCount('payment_allocations', 1);
        $this->assertDatabaseHas('payment_allocations', [
            'allocation_type' => 'training',
            'enrollment_id' => $enrollment->id,
            'amount' => 30000,
        ]);
    }

    public function test_a_deactivated_service_is_rejected_server_side_even_if_still_ticked(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 95000]);
        $service = Service::factory()->create(['is_active' => false]);

        $response = $this->actingAs($user)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'service_ids' => [$service->id],
        ]));

        $response->assertSessionHasErrors('service_ids.0');
        $this->assertDatabaseCount('students', 0);
    }

    public function test_training_amount_cannot_exceed_the_course_fee(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 95000]);
        $service = Service::factory()->create(['price' => 6000]);

        $response = $this->actingAs($user)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'service_ids' => [$service->id],
            'training_amount' => 100000,
            'payment_method' => 'cash',
        ]));

        $response->assertSessionHasErrors('training_amount');
        $this->assertDatabaseCount('students', 0);
    }

    public function test_a_service_amount_cannot_exceed_its_catalog_price(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 95000]);
        $service = Service::factory()->create(['price' => 6000]);

        $response = $this->actingAs($user)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'service_ids' => [$service->id],
            'service_amounts' => [$service->id => 10000],
            'payment_method' => 'cash',
        ]));

        $response->assertSessionHasErrors("service_amounts.{$service->id}");
        $this->assertDatabaseCount('students', 0);
    }

    public function test_payment_method_is_required_once_any_allocation_amount_is_entered(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 95000]);
        $service = Service::factory()->create(['price' => 6000]);

        $response = $this->actingAs($user)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'service_ids' => [$service->id],
            'service_amounts' => [$service->id => 6000],
        ]));

        $response->assertSessionHasErrors('payment_method');
        $this->assertDatabaseCount('students', 0);
    }

    public function test_a_stray_amount_for_an_untucked_offer_is_ignored_rather_than_validated(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 95000]);
        $ticked = Service::factory()->create(['price' => 6000]);
        $notTicked = Service::factory()->create(['price' => 1000]);

        $response = $this->actingAs($user)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'service_ids' => [$ticked->id],
            'service_amounts' => [
                $ticked->id => 6000,
                $notTicked->id => 999999,
            ],
            'payment_method' => 'cash',
        ]));

        $response->assertSessionHasNoErrors();
        $student = Student::where('email', 'jane.doe@example.com')->firstOrFail();
        $this->assertDatabaseCount('student_services', 1);
        $this->assertDatabaseHas('student_services', ['student_id' => $student->id, 'service_id' => $ticked->id]);
    }

    public function test_a_directors_own_registration_with_offers_charges_and_allocates_the_same_way(): void
    {
        $director = User::factory()->director()->create();
        $course = Course::factory()->create(['fee' => 95000]);
        $service = Service::factory()->create(['name' => "Learner's Permit", 'price' => 6000]);

        $response = $this->actingAs($director)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'service_ids' => [$service->id],
            'training_amount' => 20000,
            'service_amounts' => [$service->id => 6000],
            'payment_method' => 'cash',
        ]));

        $response->assertSessionHasNoErrors();
        $student = Student::where('email', 'jane.doe@example.com')->firstOrFail();
        $enrollment = $student->courses->first()->pivot;
        $charge = $student->studentServices()->where('service_id', $service->id)->firstOrFail();

        $this->assertSame(20000.0, $enrollment->amountPaid());
        $this->assertSame(6000.0, $charge->amountPaid());

        $payment = Payment::where('student_id', $student->id)->firstOrFail();
        $this->assertSame(2, PaymentAllocation::where('payment_id', $payment->id)->count());
    }

    public function test_registering_without_offers_or_payment_creates_no_payment_at_all(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $response = $this->actingAs($user)->post('/students', $this->registrationData([
            'course_id' => $course->id,
        ]));

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('payment_allocations', 0);
    }
}
