<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentRequest;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InstructorAssessmentRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function instructorWithAccess(): Instructor
    {
        $user = User::factory()->create(['role' => 'instructor']);

        $instructor = Instructor::factory()->create();
        $instructor->forceFill(['user_id' => $user->id])->save();

        return $instructor->fresh();
    }

    protected function completedEnrollment(Student $student, Course $course): Enrollment
    {
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->toDateString(),
            'due_date' => now()->addDays($course->gracePeriodDays())->toDateString(),
            'status' => 'completed',
        ]);

        return Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();
    }

    public function test_an_instructor_can_submit_an_assessment_recommendation_for_their_own_completed_student(): void
    {
        Notification::fake();

        $instructor = $this->instructorWithAccess();
        $course = Course::factory()->create();
        $course->instructors()->attach($instructor->id);
        $student = Student::factory()->create();
        $enrollment = $this->completedEnrollment($student, $course);

        $response = $this->actingAs($instructor->user)->post(route('instructor.assessment-request.store', $enrollment), [
            'result' => 'pass',
            'score' => 85,
            'remarks' => 'Ready for the road.',
        ]);

        $response->assertRedirect(route('instructor.dashboard'));
        $this->assertDatabaseHas('assessment_requests', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'enrollment_id' => $enrollment->id,
            'requested_by' => $instructor->user->id,
            'result' => 'pass',
            'score' => 85,
            'status' => 'pending',
        ]);
        $this->assertDatabaseCount('assessments', 0);
        $this->assertDatabaseCount('certificates', 0);
    }

    public function test_an_instructor_cannot_submit_a_recommendation_for_a_course_they_do_not_teach(): void
    {
        $instructor = $this->instructorWithAccess();
        $otherCourse = Course::factory()->create();
        $student = Student::factory()->create();
        $enrollment = $this->completedEnrollment($student, $otherCourse);

        $response = $this->actingAs($instructor->user)->post(route('instructor.assessment-request.store', $enrollment), [
            'result' => 'pass',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('assessment_requests', 0);
    }

    public function test_an_instructor_cannot_submit_a_recommendation_before_training_is_complete(): void
    {
        $instructor = $this->instructorWithAccess();
        $course = Course::factory()->create();
        $course->instructors()->attach($instructor->id);
        $student = Student::factory()->create();
        $course->students()->attach($student->id, ['enrolled_at' => now(), 'status' => 'active']);
        $enrollment = Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();

        $response = $this->actingAs($instructor->user)->post(route('instructor.assessment-request.store', $enrollment), [
            'result' => 'pass',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('assessment_requests', 0);
    }

    public function test_resubmitting_a_recommendation_updates_the_existing_pending_request_instead_of_duplicating(): void
    {
        $instructor = $this->instructorWithAccess();
        $course = Course::factory()->create();
        $course->instructors()->attach($instructor->id);
        $student = Student::factory()->create();
        $enrollment = $this->completedEnrollment($student, $course);

        $this->actingAs($instructor->user)->post(route('instructor.assessment-request.store', $enrollment), ['result' => 'fail']);
        $this->actingAs($instructor->user)->post(route('instructor.assessment-request.store', $enrollment), ['result' => 'pass', 'score' => 90]);

        $this->assertDatabaseCount('assessment_requests', 1);
        $this->assertDatabaseHas('assessment_requests', ['enrollment_id' => $enrollment->id, 'result' => 'pass', 'score' => 90]);
    }

    public function test_a_director_approving_a_request_records_the_assessment_and_issues_the_certificate(): void
    {
        $director = User::factory()->director()->create();
        $request = AssessmentRequest::factory()->create(['result' => 'pass', 'score' => 88]);

        $response = $this->actingAs($director)->patch(route('assessment-requests.approve', $request));

        $response->assertRedirect(route('approvals.index'));
        $this->assertDatabaseHas('assessments', [
            'student_id' => $request->student_id,
            'course_id' => $request->course_id,
            'result' => 'pass',
            'score' => 88,
            'assessed_by' => $director->id,
        ]);
        $this->assertDatabaseHas('assessment_requests', ['id' => $request->id, 'status' => 'approved', 'resolved_by' => $director->id]);
        $this->assertDatabaseHas('certificates', ['student_id' => $request->student_id, 'course_id' => $request->course_id]);
    }

    public function test_a_director_rejecting_a_request_does_not_record_an_assessment(): void
    {
        $director = User::factory()->director()->create();
        $request = AssessmentRequest::factory()->create(['result' => 'pass']);

        $response = $this->actingAs($director)->patch(route('assessment-requests.reject', $request));

        $response->assertRedirect(route('approvals.index'));
        $this->assertDatabaseCount('assessments', 0);
        $this->assertDatabaseCount('certificates', 0);
        $this->assertDatabaseHas('assessment_requests', ['id' => $request->id, 'status' => 'rejected', 'resolved_by' => $director->id]);
    }

    public function test_a_non_director_cannot_approve_an_assessment_request(): void
    {
        $secretary = User::factory()->secretary()->create();
        $request = AssessmentRequest::factory()->create();

        $response = $this->actingAs($secretary)->patch(route('assessment-requests.approve', $request));

        $response->assertForbidden();
        $this->assertDatabaseCount('assessments', 0);
    }

    public function test_pending_assessment_requests_appear_in_the_approval_centre(): void
    {
        $director = User::factory()->director()->create();
        $request = AssessmentRequest::factory()->create();

        $response = $this->actingAs($director)->get(route('approvals.index'));

        $response->assertOk();
        $response->assertSee($request->student->name);
        $response->assertSee('Assessment Recommendation');
    }

    public function test_the_instructor_dashboard_shows_a_student_awaiting_assessment(): void
    {
        $instructor = $this->instructorWithAccess();
        $course = Course::factory()->create();
        $course->instructors()->attach($instructor->id);
        $student = Student::factory()->create(['name' => 'Ready Student']);
        $this->completedEnrollment($student, $course);

        $response = $this->actingAs($instructor->user)->get(route('instructor.dashboard'));

        $response->assertOk();
        $response->assertSee('Ready Student');
        $response->assertSee('Awaiting Final Assessment');
    }

    public function test_the_instructor_dashboard_shows_pending_status_instead_of_the_form_after_submitting(): void
    {
        $instructor = $this->instructorWithAccess();
        $course = Course::factory()->create();
        $course->instructors()->attach($instructor->id);
        $student = Student::factory()->create();
        $enrollment = $this->completedEnrollment($student, $course);
        AssessmentRequest::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'enrollment_id' => $enrollment->id,
            'requested_by' => $instructor->user->id,
            'result' => 'pass',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($instructor->user)->get(route('instructor.dashboard'));

        $response->assertOk();
        $response->assertSee('awaiting Director confirmation', false);
    }

    public function test_a_certified_students_enrollment_does_not_appear_as_awaiting_assessment(): void
    {
        $instructor = $this->instructorWithAccess();
        $course = Course::factory()->create();
        $course->instructors()->attach($instructor->id);
        $student = Student::factory()->create(['name' => 'Already Certified']);
        $enrollment = $this->completedEnrollment($student, $course);
        Assessment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id, 'result' => 'pass']);
        $enrollment->maybeIssueCertificate();

        $response = $this->actingAs($instructor->user)->get(route('instructor.dashboard'));

        $response->assertOk();
        $response->assertDontSee('Already Certified');
    }
}
