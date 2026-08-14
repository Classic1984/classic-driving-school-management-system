<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Service;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BalanceReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.termii.api_key' => 'fake-key']);
    }

    public function test_it_texts_a_student_with_an_outstanding_training_balance(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);

        $course = Course::factory()->create(['fee' => 95000]);
        $student = Student::factory()->create(['phone' => '08031234567']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);

        $this->artisan('app:send-balance-reminder')->assertExitCode(0);

        Http::assertSent(fn ($request) => $request['to'] === '2348031234567' && str_contains($request['sms'], '95,000.00'));

        $this->assertDatabaseHas('message_logs', [
            'recipient_type' => 'student',
            'recipient_id' => $student->id,
            'purpose' => 'balance_reminder',
            'channel' => 'sms',
            'status' => 'sent',
        ]);
    }

    public function test_it_does_not_text_a_fully_paid_student(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);

        $course = Course::factory()->create(['fee' => 95000]);
        $student = Student::factory()->create(['phone' => '08039876543']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);
        $enrollment = $student->courses()->first()->pivot;

        $payment = Payment::factory()->create(['status' => 'paid']);
        PaymentAllocation::factory()->create([
            'payment_id' => $payment->id,
            'allocation_type' => 'training',
            'enrollment_id' => $enrollment->id,
            'amount' => 95000,
        ]);

        $this->artisan('app:send-balance-reminder')->assertExitCode(0);

        Http::assertNothingSent();
    }

    public function test_it_texts_a_student_with_an_outstanding_service_balance(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);

        $student = Student::factory()->create(['phone' => '08035555555']);
        $service = Service::factory()->create(['price' => 50000]);
        $student->studentServices()->create(['service_id' => $service->id, 'price' => 50000]);

        $this->artisan('app:send-balance-reminder')->assertExitCode(0);

        Http::assertSent(fn ($request) => $request['to'] === '2348035555555');
    }

    public function test_it_does_not_text_a_student_about_a_service_they_have_never_been_charged_for(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);

        Student::factory()->create(['phone' => '08037777777']);
        Service::factory()->create(['is_active' => true]);

        $this->artisan('app:send-balance-reminder')->assertExitCode(0);

        Http::assertNothingSent();
    }

    public function test_it_does_nothing_when_no_student_has_an_outstanding_balance(): void
    {
        Http::fake();

        Student::factory()->create();

        $this->artisan('app:send-balance-reminder')->assertExitCode(0);

        Http::assertNothingSent();
    }

    public function test_it_falls_back_to_whatsapp_when_sms_fails(): void
    {
        config([
            'services.twilio.account_sid' => 'AC-fake',
            'services.twilio.auth_token' => 'fake-token',
            'services.twilio.whatsapp_from' => '+15550001111',
            'services.twilio.whatsapp_templates.balance_reminder' => 'HXbalance',
        ]);
        Http::fake([
            'api.ng.termii.com/*' => Http::response(['message' => 'blocked'], 401),
            'api.twilio.com/*' => Http::response(['sid' => 'SM1'], 201),
        ]);

        $course = Course::factory()->create(['fee' => 95000]);
        $student = Student::factory()->create(['phone' => '08031234567']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);

        $this->artisan('app:send-balance-reminder')->assertExitCode(0);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'api.twilio.com')
            && $request['To'] === 'whatsapp:+2348031234567'
            && $request['ContentSid'] === 'HXbalance'
            && $request['ContentVariables'] === json_encode(['1' => '95,000.00']));

        $this->assertDatabaseHas('message_logs', [
            'recipient_type' => 'student',
            'recipient_id' => $student->id,
            'purpose' => 'balance_reminder',
            'channel' => 'whatsapp',
            'status' => 'sent',
        ]);
    }

    public function test_it_logs_a_failed_delivery_when_no_channel_is_configured(): void
    {
        config(['services.termii.api_key' => null]);
        Http::fake();

        $course = Course::factory()->create(['fee' => 95000]);
        $student = Student::factory()->create(['phone' => '08031234567']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);

        $this->artisan('app:send-balance-reminder')->assertExitCode(0);

        $this->assertDatabaseHas('message_logs', [
            'recipient_type' => 'student',
            'recipient_id' => $student->id,
            'purpose' => 'balance_reminder',
            'channel' => null,
            'status' => 'failed',
        ]);
    }
}
