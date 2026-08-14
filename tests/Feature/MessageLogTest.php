<?php

namespace Tests\Feature;

use App\Models\MessageLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_the_message_log(): void
    {
        $this->get('/message-log')->assertRedirect('/login');
    }

    public function test_only_a_director_can_view_the_message_log(): void
    {
        $admin = User::factory()->admin()->create();
        $secretary = User::factory()->secretary()->create();
        $director = User::factory()->director()->create();

        $this->actingAs($admin)->get('/message-log')->assertForbidden();
        $this->actingAs($secretary)->get('/message-log')->assertForbidden();
        $this->actingAs($director)->get('/message-log')->assertOk();
    }

    public function test_it_lists_logged_messages(): void
    {
        $director = User::factory()->director()->create();
        MessageLog::factory()->create([
            'recipient_name' => 'Jane Learner',
            'purpose' => 'balance_reminder',
            'channel' => 'sms',
            'status' => 'sent',
        ]);

        $response = $this->actingAs($director)->get('/message-log');

        $response->assertOk();
        $response->assertSee('Jane Learner');
        $response->assertSee('Balance Reminder');
    }

    public function test_it_can_be_filtered_by_purpose(): void
    {
        $director = User::factory()->director()->create();
        MessageLog::factory()->create(['recipient_name' => 'Balance Student', 'purpose' => 'balance_reminder']);
        MessageLog::factory()->create(['recipient_name' => 'Absence Student', 'purpose' => 'absence_check_in']);

        $response = $this->actingAs($director)->get('/message-log?purpose=absence_check_in');

        $response->assertOk();
        $response->assertSee('Absence Student');
        $response->assertDontSee('Balance Student');
    }

    public function test_it_can_be_filtered_by_status(): void
    {
        $director = User::factory()->director()->create();
        MessageLog::factory()->create(['recipient_name' => 'Sent Student', 'status' => 'sent', 'channel' => 'sms']);
        MessageLog::factory()->create(['recipient_name' => 'Failed Student', 'status' => 'failed', 'channel' => null]);

        $response = $this->actingAs($director)->get('/message-log?status=failed');

        $response->assertOk();
        $response->assertSee('Failed Student');
        $response->assertDontSee('Sent Student');
    }

    public function test_it_can_be_searched_by_recipient_name(): void
    {
        $director = User::factory()->director()->create();
        MessageLog::factory()->create(['recipient_name' => 'John Doe']);
        MessageLog::factory()->create(['recipient_name' => 'Mary Jane']);

        $response = $this->actingAs($director)->get('/message-log?search=John');

        $response->assertOk();
        $response->assertSee('John Doe');
        $response->assertDontSee('Mary Jane');
    }
}
