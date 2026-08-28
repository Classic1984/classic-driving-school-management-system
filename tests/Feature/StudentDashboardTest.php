<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\TheoryClass;
use App\Models\TheoryClassAttendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function studentWithAccess(array $overrides = []): Student
    {
        $user = User::factory()->create(['role' => 'student']);

        $student = Student::factory()->create($overrides);
        $student->forceFill(['user_id' => $user->id])->save();

        return $student->fresh();
    }

    public function test_a_student_sees_their_own_enrollment_progress(): void
    {
        $student = $this->studentWithAccess();
        $course = Course::factory()->create(['name' => 'Beginner Driving', 'duration_weeks' => 2]);
        $course->students()->attach($student->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 100000]);
        $enrollment = Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();
        Attendance::factory()->create(['student_id' => $student->id, 'course_id' => $course->id, 'status' => 'present', 'duration' => 1]);

        $response = $this->actingAs($student->user)->get(route('student.dashboard'));

        $response->assertOk();
        $response->assertSee('Beginner Driving');
        $response->assertSee('1 of 10 days');
        $response->assertSee('₦100,000.00');
    }

    public function test_a_student_sees_the_locked_reason_for_a_locked_enrollment(): void
    {
        $student = $this->studentWithAccess();
        $course = Course::factory()->create();
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->subMonths(3),
            'status' => 'locked',
            'locked_reason' => 'training_period_expired',
        ]);

        $response = $this->actingAs($student->user)->get(route('student.dashboard'));

        $response->assertOk();
        $response->assertSee('Training Period Expired');
    }

    public function test_a_student_sees_their_certificate_and_verification_link_once_certified(): void
    {
        $student = $this->studentWithAccess();
        $course = Course::factory()->create();
        $course->students()->attach($student->id, ['enrolled_at' => now(), 'status' => 'completed']);
        Assessment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id, 'result' => 'pass']);
        $enrollment = Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();
        $enrollment->maybeIssueCertificate();
        $certificate = $student->certificates()->firstOrFail();

        $response = $this->actingAs($student->user)->get(route('student.dashboard'));

        $response->assertOk();
        $response->assertSee('Certified');
        $response->assertSee($certificate->certificate_number);
        $response->assertSee(route('certificates.verify', $certificate->certificate_number));
    }

    public function test_a_student_with_no_enrollments_sees_an_empty_state(): void
    {
        $student = $this->studentWithAccess();

        $response = $this->actingAs($student->user)->get(route('student.dashboard'));

        $response->assertOk();
        $response->assertSee('No course enrollments on file yet.');
    }

    public function test_a_student_sees_their_theory_class_progress(): void
    {
        $student = $this->studentWithAccess(['enrollment_date' => now()->subWeek()]);
        $theoryClass = TheoryClass::factory()->create(['class_date' => now()->subDays(2)]);
        TheoryClassAttendance::factory()->create([
            'theory_class_id' => $theoryClass->id,
            'student_id' => $student->id,
            'status' => 'present',
            'score' => 88,
        ]);

        $response = $this->actingAs($student->user)->get(route('student.dashboard'));

        $response->assertOk();
        $response->assertSee('Theory Class Progress');
        $response->assertSee('1 / 1');
        $response->assertSee('88');
    }
}
