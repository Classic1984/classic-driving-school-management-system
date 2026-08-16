<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Student;
use App\Services\TermiiSmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AbsenceCheckInReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.termii.api_key' => 'fake-key']);
    }

    public function test_it_texts_a_student_with_no_training_login_in_4_or_more_days(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);

        $course = Course::factory()->create();
        $student = Student::factory()->create(['phone' => '08031234567', 'enrollment_date' => now()->subDays(10)]);
        $student->courses()->attach($course->id, ['enrolled_at' => now()->subDays(10), 'status' => 'active']);
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->subDays(5)->toDateString(),
            'status' => 'present',
        ]);

        $this->artisan('app:send-absence-check-in-reminder')->assertExitCode(0);

        Http::assertSent(fn ($request) => $request['to'] === '2348031234567' && str_contains($request['sms'], 'a few days'));

        $this->assertDatabaseHas('message_logs', [
            'recipient_type' => 'student',
            'recipient_id' => $student->id,
            'purpose' => 'absence_check_in',
            'channel' => 'sms',
            'status' => 'sent',
        ]);
    }

    public function test_it_does_not_text_a_student_who_trained_within_the_last_4_days(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);

        $course = Course::factory()->create();
        $student = Student::factory()->create(['enrollment_date' => now()->subDays(10)]);
        $student->courses()->attach($course->id, ['enrolled_at' => now()->subDays(10), 'status' => 'active']);
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->subDays(2)->toDateString(),
            'status' => 'present',
        ]);

        $this->artisan('app:send-absence-check-in-reminder')->assertExitCode(0);

        Http::assertNothingSent();
    }

    public function test_a_never_trained_student_is_measured_from_their_enrollment_date(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);

        $course = Course::factory()->create();
        $student = Student::factory()->create(['phone' => '08039998888', 'enrollment_date' => now()->subDays(5)]);
        $student->courses()->attach($course->id, ['enrolled_at' => now()->subDays(5), 'status' => 'active']);

        $this->artisan('app:send-absence-check-in-reminder')->assertExitCode(0);

        Http::assertSent(fn ($request) => $request['to'] === '2348039998888');
    }

    public function test_a_recently_enrolled_student_who_has_never_trained_is_not_texted_yet(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);

        $course = Course::factory()->create();
        $student = Student::factory()->create(['enrollment_date' => now()->subDays(2)]);
        $student->courses()->attach($course->id, ['enrolled_at' => now()->subDays(2), 'status' => 'active']);

        $this->artisan('app:send-absence-check-in-reminder')->assertExitCode(0);

        Http::assertNothingSent();
    }

    public function test_it_does_not_text_a_student_already_reminded_for_the_same_absence_spell(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);

        $course = Course::factory()->create();
        $student = Student::factory()->create(['enrollment_date' => now()->subDays(20)]);
        $student->courses()->attach($course->id, ['enrolled_at' => now()->subDays(20), 'status' => 'active']);
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->subDays(10)->toDateString(),
            'status' => 'present',
        ]);
        $student->update(['last_absence_reminder_sent_at' => now()->subDays(5)]);

        $this->artisan('app:send-absence-check-in-reminder')->assertExitCode(0);

        Http::assertNothingSent();
    }

    public function test_it_texts_again_once_the_student_trained_and_then_went_quiet_again(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);

        $course = Course::factory()->create();
        $student = Student::factory()->create(['phone' => '08037778899', 'enrollment_date' => now()->subDays(20)]);
        $student->courses()->attach($course->id, ['enrolled_at' => now()->subDays(20), 'status' => 'active']);

        // Reminded 10 days ago...
        $student->update(['last_absence_reminder_sent_at' => now()->subDays(10)]);

        // ...but then actually came back to train 6 days ago, and has been
        // quiet again ever since - due for a fresh reminder.
        Attendance::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'date' => now()->subDays(6)->toDateString(),
            'status' => 'present',
        ]);

        $this->artisan('app:send-absence-check-in-reminder')->assertExitCode(0);

        Http::assertSent(fn ($request) => $request['to'] === '2348037778899');
    }

    public function test_it_does_not_text_a_student_with_no_active_enrollment(): void
    {
        Http::fake();

        $course = Course::factory()->create();
        $student = Student::factory()->create(['enrollment_date' => now()->subDays(20)]);
        $student->courses()->attach($course->id, ['enrolled_at' => now()->subDays(20), 'status' => 'completed']);

        $this->artisan('app:send-absence-check-in-reminder')->assertExitCode(0);

        Http::assertNothingSent();
    }

    public function test_it_updates_last_absence_reminder_sent_at_after_a_successful_send(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);

        $course = Course::factory()->create();
        $student = Student::factory()->create(['enrollment_date' => now()->subDays(10)]);
        $student->courses()->attach($course->id, ['enrolled_at' => now()->subDays(10), 'status' => 'active']);

        $this->artisan('app:send-absence-check-in-reminder');

        $this->assertNotNull($student->fresh()->last_absence_reminder_sent_at);
    }

    public function test_it_falls_back_to_whatsapp_when_sms_fails(): void
    {
        config([
            'services.twilio.account_sid' => 'AC-fake',
            'services.twilio.auth_token' => 'fake-token',
            'services.twilio.whatsapp_from' => '+15550001111',
            'services.twilio.whatsapp_templates.absence_check_in' => 'HXabsence',
        ]);
        Http::fake([
            'api.ng.termii.com/*' => Http::response(['message' => 'blocked'], 401),
            'api.twilio.com/*' => Http::response(['sid' => 'SM1'], 201),
        ]);

        $course = Course::factory()->create();
        $student = Student::factory()->create(['name' => 'Quiet Student', 'phone' => '08031112222', 'enrollment_date' => now()->subDays(10)]);
        $student->courses()->attach($course->id, ['enrolled_at' => now()->subDays(10), 'status' => 'active']);

        $this->artisan('app:send-absence-check-in-reminder')->assertExitCode(0);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'api.twilio.com')
            && $request['To'] === 'whatsapp:+2348031112222'
            && $request['ContentSid'] === 'HXabsence'
            && $request['ContentVariables'] === json_encode(['1' => 'Quiet Student']));
    }

    public function test_it_does_nothing_when_no_students_are_due(): void
    {
        Http::fake();

        $this->artisan('app:send-absence-check-in-reminder')->assertExitCode(0);

        Http::assertNothingSent();
    }

    public function test_one_students_check_in_throwing_does_not_stop_the_rest_from_being_attempted(): void
    {
        Log::spy();
        $this->mock(TermiiSmsService::class, function ($mock) {
            $mock->shouldReceive('send')->andThrow(new \RuntimeException('Termii is down.'));
        });

        $course = Course::factory()->create();
        $studentA = Student::factory()->create(['enrollment_date' => now()->subDays(10)]);
        $studentA->courses()->attach($course->id, ['enrolled_at' => now()->subDays(10), 'status' => 'active']);
        $studentB = Student::factory()->create(['enrollment_date' => now()->subDays(10)]);
        $studentB->courses()->attach($course->id, ['enrolled_at' => now()->subDays(10), 'status' => 'active']);

        $this->artisan('app:send-absence-check-in-reminder')->assertExitCode(0);

        Log::shouldHaveReceived('error')->twice();
    }
}
