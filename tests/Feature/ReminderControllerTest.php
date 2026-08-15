<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Lead;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReminderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.termii.api_key' => 'fake-key']);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->post('/reminders/balance_reminder/send')->assertRedirect('/login');
    }

    public function test_only_a_director_can_trigger_a_reminder(): void
    {
        $admin = User::factory()->admin()->create();
        $secretary = User::factory()->secretary()->create();
        $director = User::factory()->director()->create();

        $this->actingAs($admin)->post('/reminders/balance_reminder/send')->assertForbidden();
        $this->actingAs($secretary)->post('/reminders/balance_reminder/send')->assertForbidden();
        $this->actingAs($director)->post('/reminders/balance_reminder/send')->assertRedirect(route('message-log.index'));
    }

    public function test_an_unknown_reminder_type_is_rejected(): void
    {
        $director = User::factory()->director()->create();

        $this->actingAs($director)->post('/reminders/not-a-real-type/send')->assertNotFound();
    }

    public function test_sending_a_balance_reminder_now_texts_students_and_logs_the_result(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);
        $director = User::factory()->director()->create();
        $course = Course::factory()->create(['fee' => 95000]);
        $student = Student::factory()->create(['phone' => '08031234567']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => 95000]);

        $response = $this->actingAs($director)->post('/reminders/balance_reminder/send');

        $response->assertRedirect(route('message-log.index'));
        $response->assertSessionHas('status', 'Balance reminder sent to 1 of 1 student(s).');

        Http::assertSent(fn ($request) => $request['to'] === '2348031234567');

        $this->assertDatabaseHas('message_logs', [
            'recipient_type' => 'student',
            'recipient_id' => $student->id,
            'purpose' => 'balance_reminder',
            'status' => 'sent',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $director->id,
            'description' => 'Manually triggered the Balance Reminder reminder',
        ]);
    }

    public function test_sending_a_theory_class_reminder_now_texts_active_students(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);
        $director = User::factory()->director()->create();
        $course = Course::factory()->create();
        $student = Student::factory()->create(['phone' => '08031234567']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($director)->post('/reminders/theory_class_reminder/send');

        $response->assertRedirect(route('message-log.index'));
        Http::assertSent(fn ($request) => $request['to'] === '2348031234567');
        $this->assertDatabaseHas('message_logs', ['purpose' => 'theory_class_reminder', 'status' => 'sent']);
    }

    public function test_sending_a_lead_follow_up_now_texts_due_leads(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);
        $director = User::factory()->director()->create();
        Lead::factory()->create([
            'phone' => '08031234567',
            'status' => 'new',
            'created_at' => now()->subDays(5),
            'last_reminded_at' => null,
        ]);

        $response = $this->actingAs($director)->post('/reminders/lead_follow_up/send');

        $response->assertRedirect(route('message-log.index'));
        Http::assertSent(fn ($request) => $request['to'] === '2348031234567');
        $this->assertDatabaseHas('message_logs', ['purpose' => 'lead_follow_up', 'status' => 'sent']);
    }

    public function test_sending_an_absence_check_in_now_texts_absent_students(): void
    {
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '1'], 200)]);
        $director = User::factory()->director()->create();
        $course = Course::factory()->create();
        $student = Student::factory()->create(['phone' => '08031234567', 'enrollment_date' => now()->subDays(10)]);
        $student->courses()->attach($course->id, ['enrolled_at' => now()->subDays(10), 'status' => 'active']);

        $response = $this->actingAs($director)->post('/reminders/absence_check_in/send');

        $response->assertRedirect(route('message-log.index'));
        Http::assertSent(fn ($request) => $request['to'] === '2348031234567');
        $this->assertDatabaseHas('message_logs', ['purpose' => 'absence_check_in', 'status' => 'sent']);
    }
}
