<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_genuine_certificate_number_is_verified_with_no_authentication_required(): void
    {
        $student = Student::factory()->create(['name' => 'Jane Doe']);
        $course = Course::factory()->create(['name' => 'Beginner Training']);
        $instructor = Instructor::factory()->create(['name' => 'John Smith']);
        $certificate = Certificate::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
        ]);

        $response = $this->get(route('certificates.verify', $certificate->certificate_number));

        $response->assertOk();
        $response->assertSee('Certificate Verified');
        $response->assertSee($certificate->certificate_number);
        $response->assertSee('Jane Doe');
        $response->assertSee($student->student_id_number);
        $response->assertSee('Beginner Training');
        $response->assertSee('John Smith');
    }

    public function test_an_unknown_certificate_number_is_reported_as_not_verified(): void
    {
        $response = $this->get(route('certificates.verify', 'CDS-CERT-2026-99999'));

        $response->assertOk();
        $response->assertSee('Not Verified');
    }

    public function test_a_certificates_verification_url_points_to_the_verify_route(): void
    {
        $certificate = Certificate::factory()->create();

        $this->assertSame(route('certificates.verify', $certificate->certificate_number), $certificate->verificationUrl());
    }

    public function test_the_certificate_page_qr_code_encodes_the_verification_url(): void
    {
        $user = User::factory()->create();
        $certificate = Certificate::factory()->create();

        $response = $this->actingAs($user)->get("/certificates/{$certificate->id}");

        $response->assertOk();
        $response->assertViewHas('certificate', fn (Certificate $viewed) => $viewed->verificationUrl() === route('certificates.verify', $certificate->certificate_number)
        );
    }
}
