<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use App\Services\WebPushService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentPushNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function studentWithAppAccess(array $overrides = []): Student
    {
        $user = User::factory()->create(['role' => 'student']);
        $student = Student::factory()->create($overrides);
        $student->forceFill(['user_id' => $user->id])->save();

        return $student->fresh();
    }

    protected function activeEnrollment(Student $student, Course $course): Enrollment
    {
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->toDateString(),
            'due_date' => now()->addDays($course->gracePeriodDays())->toDateString(),
            'status' => 'active',
        ]);

        return Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();
    }

    public function test_completing_training_pushes_a_student_with_app_access(): void
    {
        $student = $this->studentWithAppAccess();
        $course = Course::factory()->create(['name' => 'Beginner Driving']);
        $enrollment = $this->activeEnrollment($student, $course);

        $this->mock(WebPushService::class, function ($mock) use ($student) {
            $mock->shouldReceive('sendToUser')
                ->once()
                ->withArgs(fn ($user, $title, $body, $url) => $user->is($student->user)
                    && $title === 'Training Completed'
                    && str_contains($body, 'Beginner Driving')
                    && $url === route('student.dashboard'));
        });

        $enrollment->markCompleted();
    }

    public function test_completing_training_does_not_push_a_student_without_app_access(): void
    {
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $enrollment = $this->activeEnrollment($student, $course);

        $this->mock(WebPushService::class, function ($mock) {
            $mock->shouldNotReceive('sendToUser');
        });

        $enrollment->markCompleted();
    }

    public function test_certificate_ready_pushes_the_student(): void
    {
        $student = $this->studentWithAppAccess();
        $course = Course::factory()->create(['name' => 'Advanced Driving']);
        $course->students()->attach($student->id, ['enrolled_at' => now(), 'status' => 'completed']);
        $enrollment = Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();
        Assessment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id, 'result' => 'pass']);

        $this->mock(WebPushService::class, function ($mock) use ($student) {
            $mock->shouldReceive('sendToUser')
                ->once()
                ->withArgs(fn ($user, $title, $body, $url) => $user->is($student->user)
                    && $title === 'Certificate Ready'
                    && str_contains($body, 'Advanced Driving'));
        });

        $enrollment->maybeIssueCertificate();
    }

    public function test_a_failed_sms_send_still_pushes_the_student(): void
    {
        // TermiiSmsService and WhatsAppService both silently return false
        // with no API keys configured in the test environment - push is a
        // separate, independent channel and shouldn't depend on either
        // succeeding.
        $student = $this->studentWithAppAccess();
        $course = Course::factory()->create();
        $enrollment = $this->activeEnrollment($student, $course);

        $this->mock(WebPushService::class, function ($mock) {
            $mock->shouldReceive('sendToUser')->once();
        });

        $enrollment->markCompleted();

        $this->assertDatabaseHas('message_logs', ['status' => 'failed', 'purpose' => 'training_completed']);
    }
}
