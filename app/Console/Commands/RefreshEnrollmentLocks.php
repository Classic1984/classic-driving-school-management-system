<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use App\Models\User;
use App\Notifications\GracePeriodEndingSoonNotification;
use App\Notifications\PaymentReminderNotification;
use App\Services\TermiiSmsService;
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

    public function __construct(protected TermiiSmsService $sms)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $enrollments = Enrollment::all();

        foreach ($enrollments as $enrollment) {
            // Payment reminders keep firing even once training is completed,
            // since an outstanding balance remains a financial matter that's
            // independent of training status.
            if (in_array($enrollment->status, ['active', 'completed'], true) && $enrollment->balance() > 0) {
                if ($enrollment->due_date?->isTomorrow()) {
                    Notification::send(User::admins()->get(), new GracePeriodEndingSoonNotification($enrollment));
                }

                if ($enrollment->due_date?->isSameDay(now()->addDays(3))) {
                    $enrollment->student->notify(new PaymentReminderNotification($enrollment, 'upcoming'));
                    $this->sms->send($enrollment->student->phone, $this->smsText($enrollment, 'upcoming'));
                } elseif ($enrollment->due_date?->isToday()) {
                    $enrollment->student->notify(new PaymentReminderNotification($enrollment, 'due_today'));
                    $this->sms->send($enrollment->student->phone, $this->smsText($enrollment, 'due_today'));
                }
            }

            $enrollment->refreshStatus();
        }

        $this->info("Refreshed {$enrollments->count()} enrollment(s).");

        return self::SUCCESS;
    }

    /**
     * @param  'upcoming'|'due_today'  $stage
     */
    protected function smsText(Enrollment $enrollment, string $stage): string
    {
        $balance = number_format($enrollment->balance(), 2);
        $when = $stage === 'due_today' ? 'is due TODAY' : 'is due on '.$enrollment->due_date->format('Y-m-d');

        return "Classic Driving School: Your payment for {$enrollment->course->name} {$when}. Balance: NGN{$balance}. Please pay to avoid your training being locked.";
    }
}
