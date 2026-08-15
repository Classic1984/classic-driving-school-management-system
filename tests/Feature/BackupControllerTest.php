<?php

namespace Tests\Feature;

use App\Mail\DatabaseBackupMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BackupControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->post('/backups/send')->assertRedirect('/login');
    }

    public function test_only_a_director_can_trigger_a_backup(): void
    {
        $admin = User::factory()->admin()->create();
        $secretary = User::factory()->secretary()->create();
        $director = User::factory()->director()->create();

        $this->actingAs($admin)->post('/backups/send')->assertForbidden();
        $this->actingAs($secretary)->post('/backups/send')->assertForbidden();
        $this->actingAs($director)->post('/backups/send')->assertRedirect(route('activity-log.index'));
    }

    public function test_a_successful_backup_flashes_a_success_message(): void
    {
        Mail::fake();
        $director = User::factory()->director()->create();

        $response = $this->actingAs($director)->post('/backups/send');

        $response->assertRedirect(route('activity-log.index'));
        $response->assertSessionHas('status', fn ($status) => str_contains($status, 'Backup emailed to'));
        Mail::assertSent(DatabaseBackupMail::class);
    }

    public function test_a_failed_backup_flashes_the_error_instead_of_crashing(): void
    {
        $director = User::factory()->director()->create();

        Mail::shouldReceive('to')->once()->andReturnSelf();
        Mail::shouldReceive('send')->once()->andThrow(new \RuntimeException('Brevo API key invalid.'));

        $response = $this->actingAs($director)->post('/backups/send');

        $response->assertRedirect(route('activity-log.index'));
        $response->assertSessionHas('status', fn ($status) => str_contains($status, 'Backup email failed to send: Brevo API key invalid.'));
    }
}
