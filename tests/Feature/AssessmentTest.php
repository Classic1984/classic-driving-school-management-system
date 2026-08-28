<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentTest extends TestCase
{
    use RefreshDatabase;

    protected function completedEnrollment(Student $student, Course $course): Enrollment
    {
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->toDateString(),
            'due_date' => now()->addDays($course->gracePeriodDays())->toDateString(),
            'status' => 'completed',
        ]);

        return Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();
    }

    public function test_a_course_manager_can_record_a_passing_assessment(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $enrollment = $this->completedEnrollment($student, $course);

        $response = $this->actingAs($user)->post(route('enrollments.assessment.store', $enrollment), [
            'result' => 'pass',
            'score' => 82,
            'remarks' => 'Confident on the road, ready for certification.',
        ]);

        $response->assertRedirect(route('students.show', $student));
        $this->assertDatabaseHas('assessments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'result' => 'pass',
            'score' => 82,
            'remarks' => 'Confident on the road, ready for certification.',
            'assessed_by' => $user->id,
        ]);
    }

    public function test_recording_a_passing_assessment_issues_the_certificate_for_an_already_completed_enrollment(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $enrollment = $this->completedEnrollment($student, $course);

        $this->actingAs($user)->post(route('enrollments.assessment.store', $enrollment), [
            'result' => 'pass',
        ]);

        $this->assertDatabaseHas('certificates', ['student_id' => $student->id, 'course_id' => $course->id]);
        $this->assertSame('Certified', $enrollment->fresh()->statusLabel());
        $this->assertDatabaseHas('message_logs', [
            'recipient_id' => $student->id,
            'purpose' => 'certificate_ready',
        ]);
    }

    public function test_recording_a_failing_assessment_does_not_issue_a_certificate(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $enrollment = $this->completedEnrollment($student, $course);

        $this->actingAs($user)->post(route('enrollments.assessment.store', $enrollment), [
            'result' => 'fail',
            'remarks' => 'Needs more parking practice.',
        ]);

        $this->assertDatabaseCount('certificates', 0);
        $this->assertSame('Completed', $enrollment->fresh()->statusLabel());
    }

    public function test_an_assessment_recorded_before_training_completes_issues_the_certificate_once_it_does(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->toDateString(),
            'due_date' => now()->addDays($course->gracePeriodDays())->toDateString(),
            'status' => 'active',
        ]);
        $enrollment = Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();

        $this->actingAs($user)->post(route('enrollments.assessment.store', $enrollment), [
            'result' => 'pass',
        ]);
        $this->assertDatabaseCount('certificates', 0);

        $enrollment->forceFill(['status' => 'completed'])->save();
        $enrollment->maybeIssueCertificate();

        $this->assertDatabaseHas('certificates', ['student_id' => $student->id, 'course_id' => $course->id]);
    }

    public function test_re_recording_an_assessment_replaces_the_previous_result_instead_of_duplicating(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $enrollment = $this->completedEnrollment($student, $course);

        $this->actingAs($user)->post(route('enrollments.assessment.store', $enrollment), ['result' => 'fail']);
        $this->actingAs($user)->post(route('enrollments.assessment.store', $enrollment), ['result' => 'pass', 'score' => 90]);

        $this->assertDatabaseCount('assessments', 1);
        $this->assertDatabaseHas('assessments', ['student_id' => $student->id, 'course_id' => $course->id, 'result' => 'pass', 'score' => 90]);
        $this->assertDatabaseHas('certificates', ['student_id' => $student->id, 'course_id' => $course->id]);
    }

    public function test_a_non_course_manager_cannot_record_an_assessment(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $enrollment = $this->completedEnrollment($student, $course);

        $response = $this->actingAs($user)->post(route('enrollments.assessment.store', $enrollment), ['result' => 'pass']);

        $response->assertForbidden();
        $this->assertDatabaseCount('assessments', 0);
    }

    public function test_a_manual_certificate_cannot_be_issued_without_a_passing_assessment(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $this->completedEnrollment($student, $course);

        $response = $this->actingAs($user)->post('/certificates', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'issue_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('student_id');
        $this->assertDatabaseCount('certificates', 0);
    }

    public function test_a_manual_certificate_can_be_issued_once_a_passing_assessment_is_on_file(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $enrollment = $this->completedEnrollment($student, $course);
        Assessment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id, 'result' => 'pass']);

        $response = $this->actingAs($user)->post('/certificates', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'issue_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('certificates', ['student_id' => $student->id, 'course_id' => $course->id]);
    }

    public function test_the_student_profile_shows_the_recorded_assessment(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $this->completedEnrollment($student, $course);
        Assessment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'result' => 'pass',
            'score' => 88,
            'remarks' => 'Excellent parallel parking.',
        ]);

        $response = $this->actingAs($user)->get(route('students.show', $student));

        $response->assertOk();
        $response->assertSee('Excellent parallel parking.');
        $response->assertSee('88');
    }
}
