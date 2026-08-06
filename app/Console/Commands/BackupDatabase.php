<?php

namespace App\Console\Commands;

use App\Mail\DatabaseBackupMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Export every table's data and email it to each Director as a backup";

    /**
     * Tables that hold framework/session bookkeeping rather than school
     * data, so they're excluded from the backup.
     */
    protected const EXCLUDED_TABLES = [
        'migrations', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $recipients = User::where('role', 'director')->pluck('email');

        if ($recipients->isEmpty()) {
            $this->warn('No director accounts to send the backup to.');

            return self::SUCCESS;
        }

        $path = $this->createDump();

        Mail::to($recipients->all())->send(new DatabaseBackupMail($path));

        unlink($path);

        $this->info('Backup emailed to: '.$recipients->implode(', '));

        return self::SUCCESS;
    }

    /**
     * Dump every table's rows into a single gzip-compressed JSON file and
     * return its path. A plain data export (rather than a driver-specific
     * SQL dump) so it works the same way regardless of whether the app is
     * running on SQLite or Postgres, without depending on an external
     * dump binary being installed on the server.
     */
    public function createDump(): string
    {
        $tables = collect(Schema::getTables())
            ->pluck('name')
            ->reject(fn (string $table) => in_array($table, self::EXCLUDED_TABLES))
            ->values();

        $data = $tables->mapWithKeys(fn (string $table) => [
            $table => DB::table($table)->get(),
        ]);

        $path = storage_path('app/backup-'.now()->format('Y-m-d_His').'.json.gz');

        file_put_contents($path, gzencode($data->toJson(), 9));

        return $path;
    }
}
