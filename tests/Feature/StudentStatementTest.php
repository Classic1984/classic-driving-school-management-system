<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentStatementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_director_can_download_a_students_statement_as_a_pdf(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['fee' => 100000]);
        $student->courses()->attach($course->id, [
            'enrolled_at' => now(),
            'status' => 'active',
            'fee' => 90000,
            'original_fee' => 100000,
            'discount_percentage' => 10,
            'discount_amount' => 10000,
        ]);
        Payment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id, 'amount' => 40000, 'status' => 'paid']);

        $response = $this->actingAs($director)->get(route('students.statement', $student));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());

        $path = tempnam(sys_get_temp_dir(), 'stmt').'.pdf';
        file_put_contents($path, $response->getContent());
        $text = shell_exec('pdftotext '.escapeshellarg($path).' -');
        unlink($path);

        $this->assertStringContainsString('100,000.00', $text);
        $this->assertStringContainsString('90,000.00', $text);
    }

    public function test_a_secretary_cannot_download_a_students_statement(): void
    {
        $secretary = User::factory()->secretary()->create();
        $student = Student::factory()->create();

        $this->actingAs($secretary)->get(route('students.statement', $student))->assertForbidden();
    }

    public function test_a_director_sees_a_download_statement_link_on_the_student_page(): void
    {
        $director = User::factory()->director()->create();
        $student = Student::factory()->create();

        $response = $this->actingAs($director)->get("/students/{$student->id}");

        $response->assertOk();
        $response->assertSee('Download Statement');
        $response->assertSee(route('students.statement', $student), false);
    }

    public function test_a_secretary_does_not_see_a_download_statement_link(): void
    {
        $secretary = User::factory()->secretary()->create();
        $student = Student::factory()->create();

        $response = $this->actingAs($secretary)->get("/students/{$student->id}");

        $response->assertOk();
        $response->assertDontSee('Download Statement');
    }
}
