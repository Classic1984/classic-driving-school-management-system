<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateTest extends TestCase
{
    use RefreshDatabase;

    protected function enrollAndComplete(Student $student, Course $course): void
    {
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->toDateString(),
            'due_date' => now()->addDays($course->gracePeriodDays())->toDateString(),
            'status' => 'completed',
        ]);
        Assessment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id, 'result' => 'pass']);
    }

    public function test_guests_are_redirected_to_login_from_certificate_routes(): void
    {
        $certificate = Certificate::factory()->create();

        $this->get('/certificates')->assertRedirect('/login');
        $this->get('/certificates/create')->assertRedirect('/login');
        $this->get("/certificates/{$certificate->id}")->assertRedirect('/login');
        $this->get("/certificates/{$certificate->id}/edit")->assertRedirect('/login');
        $this->post('/certificates', [])->assertRedirect('/login');
        $this->put("/certificates/{$certificate->id}", [])->assertRedirect('/login');
        $this->delete("/certificates/{$certificate->id}")->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_certificate_index(): void
    {
        $user = User::factory()->create();
        $certificate = Certificate::factory()->create();

        $response = $this->actingAs($user)->get('/certificates');

        $response->assertOk();
        $response->assertSee($certificate->certificate_number);
    }

    public function test_authenticated_user_can_view_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/certificates/create');

        $response->assertOk();
    }

    public function test_certificate_can_be_issued_for_a_completed_enrollment(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $instructor = Instructor::factory()->create();
        $this->enrollAndComplete($student, $course);

        $response = $this->actingAs($user)->post('/certificates', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'issue_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/certificates');

        $certificate = Certificate::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();
        $this->assertMatchesRegularExpression('/^CDS-CERT-\d{4}-\d{5}$/', $certificate->certificate_number);
    }

    public function test_certificate_numbers_are_derived_from_the_row_id_and_issue_year(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $this->enrollAndComplete($student, $course);

        $this->actingAs($user)->post('/certificates', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'issue_date' => '2026-08-10',
        ])->assertSessionHasNoErrors();

        $certificate = Certificate::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();
        $this->assertSame('CDS-CERT-2026-'.str_pad((string) $certificate->id, 5, '0', STR_PAD_LEFT), $certificate->certificate_number);
    }

    public function test_certificate_cannot_be_issued_when_course_is_not_completed(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->toDateString(),
            'due_date' => now()->addDays($course->gracePeriodDays())->toDateString(),
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post('/certificates', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'issue_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('student_id');
        $this->assertDatabaseCount('certificates', 0);
    }

    public function test_certificate_cannot_be_issued_for_a_student_not_enrolled_in_the_course(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();

        $response = $this->actingAs($user)->post('/certificates', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'issue_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('student_id');
        $this->assertDatabaseCount('certificates', 0);
    }

    public function test_only_one_certificate_per_student_and_course_is_allowed(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $this->enrollAndComplete($student, $course);

        Certificate::factory()->create(['student_id' => $student->id, 'course_id' => $course->id]);

        $response = $this->actingAs($user)->post('/certificates', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'issue_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('student_id');
        $this->assertDatabaseCount('certificates', 1);
    }

    public function test_authenticated_user_can_view_a_certificate(): void
    {
        $user = User::factory()->create();
        $certificate = Certificate::factory()->create();

        $response = $this->actingAs($user)->get("/certificates/{$certificate->id}");

        $response->assertOk();
        $response->assertSee($certificate->student->name);
        $response->assertSee($certificate->student->student_id_number);
        $response->assertSee($certificate->course->name);
        $response->assertSee($certificate->certificate_number);
    }

    public function test_admin_and_secretary_cannot_delete_a_certificate_but_director_can(): void
    {
        $admin = User::factory()->admin()->create();
        $secretary = User::factory()->secretary()->create();
        $director = User::factory()->director()->create();
        $certificate = Certificate::factory()->create();

        $this->actingAs($admin)->delete("/certificates/{$certificate->id}")->assertForbidden();
        $this->assertDatabaseHas('certificates', ['id' => $certificate->id]);

        $this->actingAs($secretary)->delete("/certificates/{$certificate->id}")->assertForbidden();
        $this->assertDatabaseHas('certificates', ['id' => $certificate->id]);

        $this->actingAs($director)->delete("/certificates/{$certificate->id}")->assertRedirect('/certificates');
        $this->assertDatabaseMissing('certificates', ['id' => $certificate->id]);
    }
}
