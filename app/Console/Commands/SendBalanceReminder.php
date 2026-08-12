<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Services\StudentChargeResolver;
use App\Services\TermiiSmsService;
use Illuminate\Console\Command;

class SendBalanceReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-balance-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Text every student with an outstanding balance a payment reminder';

    public function __construct(protected TermiiSmsService $sms)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $students = Student::all(['id', 'name', 'phone'])
            ->map(fn (Student $student) => [
                'student' => $student,
                'balance' => StudentChargeResolver::allCharges($student)->sum('balance'),
            ])
            ->filter(fn (array $entry) => $entry['balance'] > 0);

        if ($students->isEmpty()) {
            $this->warn('No students with an outstanding balance to remind.');

            return self::SUCCESS;
        }

        $sent = $students->filter(function (array $entry) {
            $message = 'Classic Driving School: Reminder - you have an outstanding balance of ₦'.number_format($entry['balance'], 2).'. Kindly make payment at your earliest convenience.';

            return $this->sms->send($entry['student']->phone, $message);
        })->count();

        $this->info("Balance reminder sent to {$sent} of {$students->count()} student(s).");

        return self::SUCCESS;
    }
}
