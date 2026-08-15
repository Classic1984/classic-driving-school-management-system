<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RecordSchedulerHeartbeat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scheduler-heartbeat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record that the scheduler ran just now, so staff can tell from the app itself whether scheduled jobs (reminders, backups) are actually running in production';

    /**
     * The cache key the last heartbeat timestamp is stored under.
     */
    public const CACHE_KEY = 'scheduler:last_heartbeat_at';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        Cache::put(self::CACHE_KEY, now());

        return self::SUCCESS;
    }
}
