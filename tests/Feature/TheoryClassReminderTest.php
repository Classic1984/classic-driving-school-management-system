<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Student;
use App\Models\TheoryClassCancellation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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
    }

    public function test_it_does_nothing_when_there_are_no_actively_enrolled_students(): void
    {
        Http::fake();

        $this->artisan('app:send-theory-class-reminder')->assertExitCode(0);

        Http::assertNothingSent();
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
}
