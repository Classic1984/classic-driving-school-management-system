<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use Illuminate\Console\Command;

class RefreshEnrollmentLocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh-enrollment-locks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lock or unlock student course enrollments based on overdue balances and the training-period deadline';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $enrollments = Enrollment::where('status', '!=', 'completed')->get();

        foreach ($enrollments as $enrollment) {
            $enrollment->refreshStatus();
        }

        $this->info("Refreshed {$enrollments->count()} enrollment(s).");

        return self::SUCCESS;
    }
}
