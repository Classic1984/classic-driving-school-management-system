<?php

namespace Tests\Feature;

use App\Models\CorporateCompany;
use App\Models\CorporateCompanyDriver;
use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorporateDriverEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_director_can_quick_enroll_a_driver_as_a_student(): void
    {
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create(['name' => 'Arco Worldwide']);
        $driver = CorporateCompanyDriver::factory()->create([
            'corporate_company_id' => $company->id,
            'name' => 'Musa Ibrahim',
            'phone' => '08012345678',
            'license_number' => 'DL-88213',
        ]);
        $course = Course::factory()->create(['course_type' => 'manual', 'fee' => 75000]);

        $response = $this->actingAs($director)->post("/corporate-company-drivers/{$driver->id}/enroll", [
            'email' => 'musa@arco.example.com',
            'phone' => '08012345678',
            'date_of_birth' => '1990-01-01',
            'course_id' => $course->id,
        ]);

        $student = Student::firstWhere('email', 'musa@arco.example.com');
        $response->assertRedirect(route('students.show', $student));

        $this->assertDatabaseHas('students', [
            'name' => 'Musa Ibrahim',
            'email' => 'musa@arco.example.com',
            'license_number' => 'DL-88213',
            'corporate_company_id' => $company->id,
            'course_type' => 'manual',
        ]);
        $this->assertDatabaseHas('course_student', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);
        $this->assertSame($student->id, $driver->fresh()->student_id);
    }

    public function test_quick_enrolling_a_driver_does_not_create_a_payment_record(): void
    {
        $director = User::factory()->director()->create();
        $driver = CorporateCompanyDriver::factory()->create();
        $course = Course::factory()->create(['fee' => 75000]);

        $this->actingAs($director)->post("/corporate-company-drivers/{$driver->id}/enroll", [
            'email' => 'nopay@example.com',
            'phone' => '08099999999',
            'date_of_birth' => '1990-01-01',
            'course_id' => $course->id,
        ]);

        $student = Student::firstWhere('email', 'nopay@example.com');

        $this->assertDatabaseMissing('payments', ['student_id' => $student->id]);
        $enrollment = $student->courses()->where('course_id', $course->id)->first()->pivot;
        $this->assertSame(0.0, $enrollment->balance());
    }

    public function test_a_secretary_cannot_quick_enroll_a_driver(): void
    {
        $secretary = User::factory()->secretary()->create();
        $driver = CorporateCompanyDriver::factory()->create();
        $course = Course::factory()->create();

        $this->actingAs($secretary)
            ->post("/corporate-company-drivers/{$driver->id}/enroll", [
                'email' => 'blocked@example.com',
                'phone' => '08099999999',
                'date_of_birth' => '1990-01-01',
                'course_id' => $course->id,
            ])
            ->assertForbidden();
    }

    public function test_quick_enroll_requires_email_phone_date_of_birth_and_course(): void
    {
        $director = User::factory()->director()->create();
        $driver = CorporateCompanyDriver::factory()->create();

        $this->actingAs($director)
            ->post("/corporate-company-drivers/{$driver->id}/enroll", [])
            ->assertSessionHasErrors(['email', 'phone', 'date_of_birth', 'course_id']);
    }

    public function test_an_already_enrolled_driver_redirects_to_their_existing_student_record(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        $driver = CorporateCompanyDriver::factory()->create();
        $driver->forceFill(['student_id' => $student->id])->save();

        $response = $this->actingAs($director)->get("/corporate-company-drivers/{$driver->id}/enroll");

        $response->assertRedirect(route('students.show', $student));
    }

    public function test_the_company_page_offers_a_quick_enroll_link_for_an_unenrolled_driver(): void
    {
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create();
        $driver = CorporateCompanyDriver::factory()->create(['corporate_company_id' => $company->id]);

        $response = $this->actingAs($director)->get("/corporate-companies/{$company->id}");

        $response->assertOk();
        $response->assertSee(route('corporate-company-drivers.enroll-create', $driver), false);
    }

    public function test_the_company_page_links_to_the_student_record_once_a_driver_is_enrolled(): void
    {
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create();
        $student = Student::factory()->create();
        $driver = CorporateCompanyDriver::factory()->create(['corporate_company_id' => $company->id]);
        $driver->forceFill(['student_id' => $student->id])->save();

        $response = $this->actingAs($director)->get("/corporate-companies/{$company->id}");

        $response->assertOk();
        $response->assertSee(route('students.show', $student), false);
        $response->assertDontSee(route('corporate-company-drivers.enroll-create', $driver), false);
    }
}
