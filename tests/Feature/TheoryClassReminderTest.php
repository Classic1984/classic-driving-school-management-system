<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Student;
use App\Models\TheoryClassCancellation;
use App\Models\User;
use App\Services\TermiiSmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class TheoryClassReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.termii.api_key' => 'fake-key']);
    }

    public function test_it_texts_every_actively_enrolled_student(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);

        $course = Course::factory()->create();

        $active = Student::factory()->create(['phone' => '08031234567']);
        $active->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $completed = Student::factory()->create(['phone' => '08039876543']);
        $completed->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'completed']);

        $locked = Student::factory()->create(['phone' => '08035555555']);
        $locked->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'locked']);

        $this->artisan('app:send-theory-class-reminder')->assertExitCode(0);

        Http::assertSent(fn ($request) => $request['to'] === '2348031234567');
        Http::assertNotSent(fn ($request) => $request['to'] === '2348039876543');
        Http::assertNotSent(fn ($request) => $request['to'] === '2348035555555');

        $this->assertDatabaseHas('message_logs', [
            'recipient_type' => 'student',
            'recipient_id' => $active->id,
            'purpose' => 'theory_class_reminder',
            'channel' => 'sms',
            'status' => 'sent',
        ]);
    }

    public function test_it_does_nothing_when_there_are_no_actively_enrolled_students(): void
    {
        Http::fake();

        $this->artisan('app:send-theory-class-reminder')->assertExitCode(0);

        Http::assertNothingSent();
    }

    public function test_it_creates_todays_theory_class_so_the_roster_exists_before_class_starts(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);

        $course = Course::factory()->create();
        $student = Student::factory()->create(['phone' => '08031234567']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $this->artisan('app:send-theory-class-reminder')->assertExitCode(0);

        $this->assertDatabaseHas('theory_classes', ['class_date' => today()->toDateString()]);
    }

    public function test_it_does_not_create_a_theory_class_when_todays_class_is_cancelled(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);

        $course = Course::factory()->create();
        $student = Student::factory()->create(['phone' => '08031234567']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        TheoryClassCancellation::factory()->create([
            'class_date' => today(),
            'cancelled_by' => User::factory()->director()->create()->id,
        ]);

        $this->artisan('app:send-theory-class-reminder')->assertExitCode(0);

        $this->assertDatabaseMissing('theory_classes', ['class_date' => today()->toDateString()]);
    }

    public function test_a_cancellation_for_today_sends_a_cancellation_notice_instead_of_the_reminder(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);

        $course = Course::factory()->create();
        $student = Student::factory()->create(['phone' => '08031234567']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        TheoryClassCancellation::factory()->create([
            'class_date' => today(),
            'reason' => 'Public holiday',
            'cancelled_by' => User::factory()->director()->create()->id,
        ]);

        $this->artisan('app:send-theory-class-reminder')->assertExitCode(0);

        Http::assertSent(fn ($request) => $request['to'] === '2348031234567'
            && str_contains($request['sms'], 'CANCELLED')
            && str_contains($request['sms'], 'Public holiday'));

        $this->assertDatabaseHas('message_logs', [
            'recipient_type' => 'student',
            'recipient_id' => $student->id,
            'purpose' => 'theory_class_cancellation',
            'channel' => 'sms',
            'status' => 'sent',
        ]);
    }

    public function test_a_cancellation_for_a_different_date_does_not_affect_todays_reminder(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);

        $course = Course::factory()->create();
        $student = Student::factory()->create(['phone' => '08031234567']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        TheoryClassCancellation::factory()->create([
            'class_date' => today()->addWeek(),
            'cancelled_by' => User::factory()->director()->create()->id,
        ]);

        $this->artisan('app:send-theory-class-reminder')->assertExitCode(0);

        Http::assertSent(fn ($request) => $request['to'] === '2348031234567'
            && str_contains($request['sms'], 'Please be punctual')
            && ! str_contains($request['sms'], 'CANCELLED'));
    }

    public function test_it_falls_back_to_whatsapp_when_sms_fails(): void
    {
        config([
            'services.twilio.account_sid' => 'AC-fake',
            'services.twilio.auth_token' => 'fake-token',
            'services.twilio.whatsapp_from' => '+15550001111',
            'services.twilio.whatsapp_templates.theory_class_reminder' => 'HXreminder',
        ]);
        Http::fake([
            'api.ng.termii.com/*' => Http::response(['message' => 'blocked'], 401),
            'api.twilio.com/*' => Http::response(['sid' => 'SM1'], 201),
        ]);

        $course = Course::factory()->create();
        $student = Student::factory()->create(['phone' => '08031234567']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $this->artisan('app:send-theory-class-reminder')->assertExitCode(0);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'api.twilio.com')
            && $request['To'] === 'whatsapp:+2348031234567'
            && $request['ContentSid'] === 'HXreminder');
    }

    public function test_a_cancellation_falls_back_to_whatsapp_with_the_reason_as_a_variable(): void
    {
        config([
            'services.twilio.account_sid' => 'AC-fake',
            'services.twilio.auth_token' => 'fake-token',
            'services.twilio.whatsapp_from' => '+15550001111',
            'services.twilio.whatsapp_templates.theory_class_cancellation' => 'HXcancel',
        ]);
        Http::fake([
            'api.ng.termii.com/*' => Http::response(['message' => 'blocked'], 401),
            'api.twilio.com/*' => Http::response(['sid' => 'SM1'], 201),
        ]);

        $course = Course::factory()->create();
        $student = Student::factory()->create(['phone' => '08031234567']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        TheoryClassCancellation::factory()->create([
            'class_date' => today(),
            'reason' => 'Public holiday',
            'cancelled_by' => User::factory()->director()->create()->id,
        ]);

        $this->artisan('app:send-theory-class-reminder')->assertExitCode(0);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'api.twilio.com')
            && $request['ContentSid'] === 'HXcancel'
            && $request['ContentVariables'] === json_encode(['1' => 'Public holiday']));
    }

    public function test_one_students_reminder_throwing_does_not_stop_the_rest_from_being_attempted(): void
    {
        Log::spy();
        $this->mock(TermiiSmsService::class, function ($mock) {
            $mock->shouldReceive('send')->andThrow(new \RuntimeException('Termii is down.'));
        });

        $course = Course::factory()->create();
        $studentA = Student::factory()->create();
        $studentA->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);
        $studentB = Student::factory()->create();
        $studentB->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $this->artisan('app:send-theory-class-reminder')->assertExitCode(0);

        Log::shouldHaveReceived('error')->twice();
    }
}
