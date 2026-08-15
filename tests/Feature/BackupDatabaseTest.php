<?php

namespace Tests\Feature;

use App\Console\Commands\BackupDatabase;
use App\Mail\DatabaseBackupMail;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use ZipArchive;

class BackupDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_emails_every_director_a_backup_of_the_database(): void
    {
        Mail::fake();

        $director = User::factory()->director()->create(['email' => 'director@example.com']);
        User::factory()->admin()->create(['email' => 'admin@example.com']);

        $this->artisan('backup:database')->assertExitCode(0);

        Mail::assertSent(DatabaseBackupMail::class, function (DatabaseBackupMail $mail) use ($director) {
            return $mail->hasTo($director->email)
                && ! $mail->hasTo('admin@example.com')
                && str_ends_with($mail->path, '.zip');
        });
    }

    public function test_the_dump_includes_every_students_table_row(): void
    {
        Student::factory()->create(['name' => 'Backed Up Student']);

        $path = (new BackupDatabase)->createDump();

        $zip = new ZipArchive;
        $zip->open($path);
        $contents = json_decode($zip->getFromName($zip->getNameIndex(0)), true);
        $zip->close();

        unlink($path);

        $this->assertTrue(collect($contents['students'])->contains(fn (array $row) => $row['name'] === 'Backed Up Student'));
    }

    public function test_it_deletes_the_dump_file_after_sending(): void
    {
        Mail::fake();

        User::factory()->director()->create();

        $capturedPath = null;

        $this->artisan('backup:database');

        Mail::assertSent(DatabaseBackupMail::class, function (DatabaseBackupMail $mail) use (&$capturedPath) {
            $capturedPath = $mail->path;

            return true;
        });

        $this->assertNotNull($capturedPath);
        $this->assertFileDoesNotExist($capturedPath);
    }

    public function test_it_does_nothing_when_there_are_no_directors(): void
    {
        Mail::fake();

        User::factory()->admin()->create();

        $this->artisan('backup:database')->assertExitCode(0);

        Mail::assertNothingSent();
    }

    public function test_it_reports_a_clear_failure_instead_of_crashing_when_the_mailer_fails(): void
    {
        User::factory()->director()->create();

        Mail::shouldReceive('to')
            ->once()
            ->andReturnSelf();
        Mail::shouldReceive('send')
            ->once()
            ->andThrow(new \RuntimeException('Brevo API key invalid.'));

        $this->artisan('backup:database')
            ->expectsOutputToContain('Backup email failed to send: Brevo API key invalid.')
            ->assertExitCode(1);
    }
}
