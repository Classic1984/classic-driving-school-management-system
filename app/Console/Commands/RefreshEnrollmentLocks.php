<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use App\Models\User;
use App\Notifications\GracePeriodEndingSoonNotification;
use App\Notifications\PaymentReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

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
    protected $description = 'Lock or unlock student course enrollments based on overdue balances and the training-period deadline, and send payment reminders';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $enrollments = Enrollment::where('status', '!=', 'completed')->get();

        foreach ($enrollments as $enrollment) {
            if ($enrollment->status === 'active' && $enrollment->balance() > 0) {
                if ($enrollment->due_date?->isTomorrow()) {
                    Notification::send(User::admins()->get(), new GracePeriodEndingSoonNotification($enrollment));
                }

                if ($enrollment->due_date?->isSameDay(now()->addDays(3))) {
                    $enrollment->student->notify(new PaymentReminderNotification($enrollment, 'upcoming'));
                } elseif ($enrollment->due_date?->isToday()) {
                    $enrollment->student->notify(new PaymentReminderNotification($enrollment, 'due_today'));
                }
            }

            $enrollment->refreshStatus();
        }

        $this->info("Refreshed {$enrollments->count()} enrollment(s).");

        return self::SUCCESS;
    }
}
