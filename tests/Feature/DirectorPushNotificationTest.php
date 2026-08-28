<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\User;
use App\Services\WebPushService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectorPushNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function registrationData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'phone' => '555-0100',
            'date_of_birth' => '2000-01-15',
            'course_type' => 'manual',
            'enrollment_date' => now()->toDateString(),
            'status' => 'active',
        ], $overrides);
    }

    public function test_a_pending_discount_request_pushes_every_director(): void
    {
        $secretary = User::factory()->secretary()->create();
        $director = User::factory()->director()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $this->mock(WebPushService::class, function ($mock) {
            $mock->shouldReceive('sendToDirectors')
                ->once()
                ->withArgs(fn ($title, $body, $url) => $title === 'Discount Request'
                    && $url === route('approvals.index'));
        });

        $this->actingAs($secretary)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'discount_choice' => '5000',
            'discount_reason' => 'promotional_offer',
        ]))->assertSessionHasNoErrors();
    }

    public function test_a_directors_own_discount_does_not_push_anyone(): void
    {
        $director = User::factory()->director()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $this->mock(WebPushService::class, function ($mock) {
            $mock->shouldNotReceive('sendToDirectors');
        });

        $this->actingAs($director)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'discount_choice' => '5000',
            'discount_reason' => 'promotional_offer',
        ]))->assertSessionHasNoErrors();
    }

    public function test_a_correction_request_pushes_every_director(): void
    {
        $secretary = User::factory()->secretary()->create();
        $student = Student::factory()->create(['name' => 'Ola Correction']);

        $this->mock(WebPushService::class, function ($mock) {
            $mock->shouldReceive('sendToDirectors')
                ->once()
                ->withArgs(fn ($title, $body, $url) => $title === 'Correction Request'
                    && str_contains($body, 'Ola Correction')
                    && $url === route('approvals.index'));
        });

        $this->actingAs($secretary)->post(route('student-correction-requests.store', $student), [
            'field' => 'phone',
            'requested_value' => '08099998888',
        ]);
    }

    public function test_an_assessment_recommendation_pushes_every_director(): void
    {
        $instructorUser = User::factory()->create(['role' => 'instructor']);
        $instructor = Instructor::factory()->create();
        $instructor->forceFill(['user_id' => $instructorUser->id])->save();
        $course = Course::factory()->create();
        $course->instructors()->attach($instructor->id);
        $student = Student::factory()->create(['name' => 'Ready Student']);
        $course->students()->attach($student->id, [
            'enrolled_at' => now()->toDateString(),
            'due_date' => now()->addDays($course->gracePeriodDays())->toDateString(),
            'status' => 'completed',
        ]);
        $enrollment = Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();

        $this->mock(WebPushService::class, function ($mock) {
            $mock->shouldReceive('sendToDirectors')
                ->once()
                ->withArgs(fn ($title, $body, $url) => $title === 'Assessment Recommendation'
                    && str_contains($body, 'Ready Student')
                    && $url === route('approvals.index'));
        });

        $this->actingAs($instructorUser)->post(route('instructor.assessment-request.store', $enrollment), [
            'result' => 'pass',
        ]);
    }
}
