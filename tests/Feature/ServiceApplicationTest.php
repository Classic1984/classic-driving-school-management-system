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

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/driver-license')->assertRedirect('/login');
        $this->get('/driver-license/register')->assertRedirect('/login');
        $this->get('/learners-permit')->assertRedirect('/login');
        $this->get('/learners-permit/register')->assertRedirect('/login');
    }

    public function test_the_index_page_separates_walk_in_customers_from_existing_students(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => "Driver's License Processing"]);

        $walkIn = Student::factory()->create(['name' => 'Amaka Walk-in']);
        $this->chargeFor($walkIn, $service);

        $existingStudent = Student::factory()->create(['name' => 'Daniel Trainee']);
        $existingStudent->courses()->attach(Course::factory()->create()->id, ['enrolled_at' => now(), 'status' => 'active']);
        $this->chargeFor($existingStudent, $service);

        $response = $this->actingAs($user)->get('/driver-license');

        $response->assertOk();
        $response->assertSee("Driver's License");
        $response->assertSee('Amaka Walk-in');
        $response->assertSee('Daniel Trainee');
        $response->assertViewHas('stats', fn (array $stats) => $stats['total'] === 2
            && $stats['walk_in'] === 1
            && $stats['existing_student'] === 1);
    }

    public function test_a_service_with_no_charges_yet_shows_an_empty_index(): void
    {
        $user = User::factory()->create();
        Service::factory()->create(['name' => "Learner's Permit"]);

        $response = $this->actingAs($user)->get('/learners-permit');

        $response->assertOk();
        $response->assertSee('No applicants yet.');
        $response->assertViewHas('stats', fn (array $stats) => $stats['total'] === 0);
    }

    public function test_the_index_page_filters_by_source(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => "Driver's License Processing"]);

        $walkIn = Student::factory()->create(['name' => 'Amaka Walk-in']);
        $this->chargeFor($walkIn, $service);

        $existingStudent = Student::factory()->create(['name' => 'Daniel Trainee']);
        $existingStudent->courses()->attach(Course::factory()->create()->id, ['enrolled_at' => now(), 'status' => 'active']);
        $this->chargeFor($existingStudent, $service);

        $response = $this->actingAs($user)->get('/driver-license?source=walk_in');

        $response->assertOk();
        $response->assertSee('Amaka Walk-in');
        $response->assertDontSee('Daniel Trainee');
    }

    public function test_the_index_page_filters_by_processing_status(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => "Driver's License Processing"]);

        $notStarted = Student::factory()->create(['name' => 'Pending Applicant']);
        $this->chargeFor($notStarted, $service, ['processing_status' => 'not_started']);

        $completed = Student::factory()->create(['name' => 'Finished Applicant']);
        $this->chargeFor($completed, $service, ['processing_status' => 'completed']);

        $response = $this->actingAs($user)->get('/driver-license?status=completed');

        $response->assertOk();
        $response->assertSee('Finished Applicant');
        $response->assertDontSee('Pending Applicant');
    }

    public function test_the_index_page_filters_by_payment_status(): void
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

        $response = $this->actingAs($user)->get('/driver-license?status=paid');

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

        $response = $this->actingAs($user)->get('/driver-license?period=week');

        $response->assertOk();
        $response->assertSee('Monday Applicant');
    }

    public function test_the_register_page_lists_students_for_the_existing_student_tab(): void
    {
        $user = User::factory()->create();
        Service::factory()->create(['name' => "Driver's License Processing"]);
        Student::factory()->create(['name' => 'Existing Chisom']);

        $response = $this->actingAs($user)->get('/driver-license/register');

        $response->assertOk();
        $response->assertSee('Existing Chisom');
        $response->assertSee('New Walk-in Applicant');
    }

    public function test_registering_a_new_walk_in_applicant_creates_a_student_and_redirects_to_record_payment(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['name' => "Driver's License Processing", 'price' => 50000]);

        $response = $this->actingAs($user)->post('/driver-license/register', [
            'name' => 'Okoro Emeka',
            'email' => 'okoro.emeka@example.com',
            'phone' => '08011112222',
            'date_of_birth' => '1990-01-01',
        ]);

        $response->assertSessionHasNoErrors();
        $student = Student::where('email', 'okoro.emeka@example.com')->firstOrFail();

        $this->assertTrue($student->courses->isEmpty());
        $response->assertRedirect(route('payments.record.create', [
            'student_id' => $student->id,
            'charge_type' => 'new_service',
            'charge_id' => $service->id,
        ]));
    }

    public function test_registering_a_walk_in_applicant_requires_the_core_fields(): void
    {
        $user = User::factory()->create();
        Service::factory()->create(['name' => "Learner's Permit"]);

        $response = $this->actingAs($user)->post('/learners-permit/register', []);

        $response->assertSessionHasErrors(['name', 'email', 'phone', 'date_of_birth']);
        $this->assertDatabaseCount('students', 0);
    }

    public function test_registering_a_walk_in_applicant_rejects_a_duplicate_email(): void
    {
        $user = User::factory()->create();
        Service::factory()->create(['name' => "Learner's Permit"]);
        Student::factory()->create(['email' => 'taken@example.com']);

        $response = $this->actingAs($user)->post('/learners-permit/register', [
            'name' => 'Second Person',
            'email' => 'taken@example.com',
            'phone' => '08033334444',
            'date_of_birth' => '1992-05-05',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('students', 1);
    }

    public function test_the_index_page_shows_a_friendly_message_when_the_catalog_service_is_missing(): void
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

    public function test_the_register_page_shows_a_friendly_message_when_the_catalog_service_is_missing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/learners-permit/register');

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

        $response = $this->actingAs($user)->post('/driver-license/register', [
            'name' => 'No Catalog Yet',
            'email' => 'no.catalog@example.com',
            'phone' => '08055556666',
            'date_of_birth' => '1995-01-01',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('students', 0);
    }
}
