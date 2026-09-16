<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Service;
use App\Models\Student;
use App\Models\StudentService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * The standalone Driver's License and Learner's Permit sections: each is
 * its own list of StudentService charges for one catalog Service, kept
 * separate from Students so a walk-in-only customer never has to be
 * treated as a "driving student" - see ServiceApplicationController.
 *
 * Clicking into a section lands on a combined register-and-pay form
 * (index), not the applicant list (applicants) - staff can charge and
 * pay for the service in one submission, for either an existing student
 * or a brand new walk-in.
 */
class ServiceApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected function chargeFor(Student $student, Service $service, array $overrides = []): StudentService
    {
        return $student->studentServices()->create(array_merge([
            'service_id' => $service->id,
            'price' => $service->price,
        ], $overrides));
    }

    protected function walkInPayload(array $overrides = []): array
    {
        return array_merge([
            'mode' => 'walk_in',
            'name' => 'Okoro Emeka',
            'email' => 'okoro.emeka@example.com',
            'phone' => '08011112222',
            'date_of_birth' => '1990-01-01',
            'amount' => '50000',
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
        ], $overrides);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/driver-license')->assertRedirect('/login');
        $this->get('/driver-license/applicants')->assertRedirect('/login');
        $this->get('/learners-permit')->assertRedirect('/login');
        $this->get('/learners-permit/applicants')->assertRedirect('/login');
    }

    public function test_clicking_into_the_section_lands_on_the_registration_form(): void
    {
        $user = User::factory()->create();
        Service::factory()->create(['name' => "Driver's License Processing", 'price' => 50000]);
        Student::factory()->create(['name' => 'Existing Chisom']);

        $response = $this->actingAs($user)->get('/driver-license');

        $response->assertOk();
        $response->assertSee('Existing Chisom');
        $response->assertSee('New Walk-in Applicant');

        // Regression test: the payment section's fields once silently
        // failed to render at all (an escaped-quote :for/:id binding that
        // Blade couldn't compile, left as an unrecognized custom element
        // the browser just... didn't show, with no error anywhere) - this
        // pins down that the actual <input>s exist and are pre-filled,
        // not just that surrounding static text is present.
        $response->assertSee('id="existing_amount"', false);
        $response->assertSee('value="50000.00"', false);
        $response->assertSee('id="walk_in_amount"', false);
        $response->assertSee('Register & Record Payment');
    }

    public function test_registering_a_new_walk_in_applicant_charges_and_pays_in_one_step(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => "Driver's License Processing", 'price' => 50000]);

        $response = $this->actingAs($user)->post('/driver-license', $this->walkInPayload());

        $response->assertSessionHasNoErrors();
        $student = Student::where('email', 'okoro.emeka@example.com')->firstOrFail();

        $this->assertTrue($student->courses->isEmpty());
        $response->assertRedirect(route('students.show', $student));

        $this->assertDatabaseHas('student_services', [
            'student_id' => $student->id,
            'service_id' => $service->id,
            'price' => 50000,
        ]);

        $studentService = StudentService::where('student_id', $student->id)->where('service_id', $service->id)->firstOrFail();
        $this->assertSame(50000.0, $studentService->amountPaid());
        $this->assertSame('paid', $studentService->status());
    }

    public function test_registering_an_existing_student_charges_and_pays_in_one_step(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => "Learner's Permit", 'price' => 6000]);
        $student = Student::factory()->create(['name' => 'Daniel Trainee']);
        $student->courses()->attach(Course::factory()->create()->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($user)->post('/learners-permit', [
            'mode' => 'existing',
            'student_id' => $student->id,
            'amount' => '6000',
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('students.show', $student));

        $studentService = StudentService::where('student_id', $student->id)->where('service_id', $service->id)->firstOrFail();
        $this->assertSame(6000.0, $studentService->amountPaid());
        $this->assertSame('paid', $studentService->status());
    }

    public function test_a_part_payment_leaves_the_charge_partially_paid(): void
    {
        $user = User::factory()->create();
        Service::factory()->create(['name' => "Driver's License Processing", 'price' => 50000]);

        $response = $this->actingAs($user)->post('/driver-license', $this->walkInPayload(['amount' => '20000']));

        $response->assertSessionHasNoErrors();
        $student = Student::where('email', 'okoro.emeka@example.com')->firstOrFail();

        $studentService = StudentService::where('student_id', $student->id)->firstOrFail();
        $this->assertSame(20000.0, $studentService->amountPaid());
        $this->assertSame('part_payment', $studentService->status());
    }

    public function test_paying_for_an_existing_students_open_charge_adds_to_it_instead_of_duplicating(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => "Driver's License Processing", 'price' => 50000]);
        $student = Student::factory()->create();
        $existingCharge = $this->chargeFor($student, $service);
        Payment::factory()->create(['student_id' => $student->id, 'status' => 'paid'])
            ->allocations()->create(['allocation_type' => 'service', 'student_service_id' => $existingCharge->id, 'amount' => 20000]);

        $response = $this->actingAs($user)->post('/driver-license', [
            'mode' => 'existing',
            'student_id' => $student->id,
            'amount' => '30000',
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('student_services', 1);

        $existingCharge->refresh();
        $this->assertSame(50000.0, $existingCharge->amountPaid());
        $this->assertSame('paid', $existingCharge->status());
    }

    public function test_registering_a_walk_in_applicant_requires_the_core_fields(): void
    {
        $user = User::factory()->create();
        Service::factory()->create(['name' => "Learner's Permit"]);

        $response = $this->actingAs($user)->post('/learners-permit', ['mode' => 'walk_in']);

        $response->assertSessionHasErrors(['name', 'email', 'phone', 'date_of_birth', 'amount', 'payment_method', 'payment_date']);
        $this->assertDatabaseCount('students', 0);
    }

    public function test_registering_a_walk_in_applicant_rejects_a_duplicate_email(): void
    {
        $user = User::factory()->create();
        Service::factory()->create(['name' => "Learner's Permit"]);
        Student::factory()->create(['email' => 'taken@example.com']);

        $response = $this->actingAs($user)->post('/learners-permit', $this->walkInPayload(['email' => 'taken@example.com']));

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('students', 1);
    }

    public function test_selecting_an_existing_student_requires_a_valid_student_id(): void
    {
        $user = User::factory()->create();
        Service::factory()->create(['name' => "Driver's License Processing"]);

        $response = $this->actingAs($user)->post('/driver-license', [
            'mode' => 'existing',
            'amount' => '50000',
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('student_id');
    }

    public function test_the_applicants_page_separates_walk_in_customers_from_existing_students(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => "Driver's License Processing"]);

        $walkIn = Student::factory()->create(['name' => 'Amaka Walk-in']);
        $this->chargeFor($walkIn, $service);

        $existingStudent = Student::factory()->create(['name' => 'Daniel Trainee']);
        $existingStudent->courses()->attach(Course::factory()->create()->id, ['enrolled_at' => now(), 'status' => 'active']);
        $this->chargeFor($existingStudent, $service);

        $response = $this->actingAs($user)->get('/driver-license/applicants');

        $response->assertOk();
        $response->assertSee("Driver's License");
        $response->assertSee('Amaka Walk-in');
        $response->assertSee('Daniel Trainee');
        $response->assertViewHas('stats', fn (array $stats) => $stats['total'] === 2
            && $stats['walk_in'] === 1
            && $stats['existing_student'] === 1);
    }

    public function test_a_service_with_no_charges_yet_shows_an_empty_applicants_page(): void
    {
        $user = User::factory()->create();
        Service::factory()->create(['name' => "Learner's Permit"]);

        $response = $this->actingAs($user)->get('/learners-permit/applicants');

        $response->assertOk();
        $response->assertSee('No applicants yet.');
        $response->assertViewHas('stats', fn (array $stats) => $stats['total'] === 0);
    }

    public function test_the_applicants_page_filters_by_source(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => "Driver's License Processing"]);

        $walkIn = Student::factory()->create(['name' => 'Amaka Walk-in']);
        $this->chargeFor($walkIn, $service);

        $existingStudent = Student::factory()->create(['name' => 'Daniel Trainee']);
        $existingStudent->courses()->attach(Course::factory()->create()->id, ['enrolled_at' => now(), 'status' => 'active']);
        $this->chargeFor($existingStudent, $service);

        $response = $this->actingAs($user)->get('/driver-license/applicants?source=walk_in');

        $response->assertOk();
        $response->assertSee('Amaka Walk-in');
        $response->assertDontSee('Daniel Trainee');
    }

    public function test_the_applicants_page_filters_by_processing_status(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => "Driver's License Processing"]);

        $notStarted = Student::factory()->create(['name' => 'Pending Applicant']);
        $this->chargeFor($notStarted, $service, ['processing_status' => 'not_started']);

        $completed = Student::factory()->create(['name' => 'Finished Applicant']);
        $this->chargeFor($completed, $service, ['processing_status' => 'completed']);

        $response = $this->actingAs($user)->get('/driver-license/applicants?status=completed');

        $response->assertOk();
        $response->assertSee('Finished Applicant');
        $response->assertDontSee('Pending Applicant');
    }

    public function test_the_applicants_page_filters_by_payment_status(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => "Driver's License Processing", 'price' => 10000]);

        $paidApplicant = Student::factory()->create(['name' => 'Paid Applicant']);
        $paidCharge = $this->chargeFor($paidApplicant, $service);
        $payment = Payment::factory()->create(['student_id' => $paidApplicant->id, 'status' => 'paid', 'amount' => 10000]);
        PaymentAllocation::factory()->create([
            'payment_id' => $payment->id,
            'allocation_type' => 'service',
            'student_service_id' => $paidCharge->id,
            'amount' => 10000,
        ]);

        $unpaidApplicant = Student::factory()->create(['name' => 'Unpaid Applicant']);
        $this->chargeFor($unpaidApplicant, $service);

        $response = $this->actingAs($user)->get('/driver-license/applicants?status=paid');

        $response->assertOk();
        $response->assertSee('Paid Applicant');
        $response->assertDontSee('Unpaid Applicant');
    }

    public function test_the_week_filter_includes_a_charge_made_on_the_first_day_of_the_week(): void
    {
        // Regression test: whereBetween() against a timestamp column with
        // an uncast Carbon lower bound stringifies as "Y-m-d 00:00:00",
        // which would sort *after* a charge actually created on the
        // Monday the week starts - the same bug already fixed once in
        // DashboardController, guarded against here via toDateString().
        $this->travelTo(Carbon::parse('next Monday')->setTime(10, 0));

        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => "Driver's License Processing"]);
        $student = Student::factory()->create(['name' => 'Monday Applicant']);
        $this->chargeFor($student, $service);

        $response = $this->actingAs($user)->get('/driver-license/applicants?period=week');

        $response->assertOk();
        $response->assertSee('Monday Applicant');
    }

    public function test_a_specific_date_filters_to_only_that_days_applicants(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => "Driver's License Processing"]);

        $this->travelTo(Carbon::parse('2026-01-01')->setTime(10, 0));
        $oldApplicant = Student::factory()->create(['name' => 'New Year Applicant']);
        $this->chargeFor($oldApplicant, $service);

        $this->travelTo(Carbon::parse('2026-01-05')->setTime(10, 0));
        $recentApplicant = Student::factory()->create(['name' => 'Later Applicant']);
        $this->chargeFor($recentApplicant, $service);

        $response = $this->actingAs($user)->get('/driver-license/applicants?date=2026-01-01');

        $response->assertOk();
        $response->assertSee('New Year Applicant');
        $response->assertDontSee('Later Applicant');
    }

    public function test_a_specific_date_takes_priority_over_the_period_filter(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => "Driver's License Processing"]);

        $this->travelTo(Carbon::parse('2026-01-01')->setTime(10, 0));
        $oldApplicant = Student::factory()->create(['name' => 'New Year Applicant']);
        $this->chargeFor($oldApplicant, $service);

        $this->travelTo(Carbon::parse('2026-01-05')->setTime(10, 0));

        $response = $this->actingAs($user)->get('/driver-license/applicants?period=today&date=2026-01-01');

        $response->assertOk();
        $response->assertSee('New Year Applicant');
    }

    public function test_guests_are_redirected_to_login_from_the_certificate_sections(): void
    {
        $this->get('/online-certificate')->assertRedirect('/login');
        $this->get('/online-certificate/applicants')->assertRedirect('/login');
    }

    public function test_registering_a_walk_in_applicant_for_an_online_certificate_charges_and_pays_in_one_step(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => 'Online Certificate', 'price' => 20000]);

        $response = $this->actingAs($user)->post('/online-certificate', $this->walkInPayload(['amount' => '20000']));

        $response->assertSessionHasNoErrors();
        $student = Student::where('email', 'okoro.emeka@example.com')->firstOrFail();
        $response->assertRedirect(route('students.show', $student));

        $studentService = StudentService::where('student_id', $student->id)->where('service_id', $service->id)->firstOrFail();
        $this->assertSame(20000.0, $studentService->amountPaid());
        $this->assertSame('paid', $studentService->status());
    }

    public function test_the_online_certificate_applicants_page_lists_charges_made_for_it(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => 'Online Certificate']);
        $applicant = Student::factory()->create(['name' => 'Certificate Applicant']);
        $this->chargeFor($applicant, $service);

        $response = $this->actingAs($user)->get('/online-certificate/applicants');

        $response->assertOk();
        $response->assertSee('Certificate Applicant');
    }

    public function test_the_online_certificate_form_shows_a_friendly_message_when_the_catalog_service_is_missing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/online-certificate');

        $response->assertOk();
        $response->assertSee("isn't set up yet");
        $response->assertSee('Online Certificate');
    }

    public function test_the_form_page_shows_a_friendly_message_when_the_catalog_service_is_missing(): void
    {
        // Regression test: production only runs migrations on deploy, not
        // ServicePriceListSeeder - a missing or renamed catalog row must
        // never surface as a raw, unexplained 404 (firstOrFail()'s default).
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/driver-license');

        $response->assertOk();
        $response->assertSee("isn't set up yet");
        $response->assertSee("Driver's License Processing");
    }

    public function test_the_applicants_page_shows_a_friendly_message_when_the_catalog_service_is_missing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/learners-permit/applicants');

        $response->assertOk();
        $response->assertSee("isn't set up yet");
    }

    public function test_a_director_sees_a_link_to_add_the_missing_catalog_service(): void
    {
        $director = User::factory()->director()->create();

        $response = $this->actingAs($director)->get('/driver-license');

        $response->assertOk();
        $response->assertSee(route('services.create'), false);
    }

    public function test_the_add_link_pre_fills_the_exact_name_and_suggested_price(): void
    {
        $director = User::factory()->director()->create();

        $response = $this->actingAs($director)->get('/driver-license');

        $response->assertOk();
        $response->assertSee(route('services.create', [
            'name' => "Driver's License Processing",
            'price' => 50000,
            'processing_days' => 30,
        ]));
    }

    public function test_a_non_director_is_told_to_ask_a_director_instead_of_a_add_link(): void
    {
        $secretary = User::factory()->secretary()->create();

        $response = $this->actingAs($secretary)->get('/driver-license');

        $response->assertOk();
        $response->assertSee('Ask a director to add it');
        $response->assertDontSee(route('services.create'), false);
    }

    public function test_registering_a_walk_in_applicant_fails_gracefully_when_the_catalog_service_is_missing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/driver-license', $this->walkInPayload(['email' => 'no.catalog@example.com']));

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('students', 0);
    }
}
