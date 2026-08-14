<?php

namespace App\Console\Commands;

use App\Models\MessageLog;
use App\Models\Student;
use App\Services\StudentChargeResolver;
use App\Services\TermiiSmsService;
use App\Services\WhatsAppService;
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

    public function __construct(protected TermiiSmsService $sms, protected WhatsAppService $whatsapp)
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
            $student = $entry['student'];
            $formattedBalance = number_format($entry['balance'], 2);
            $message = "Classic Driving School: Reminder - you have an outstanding balance of ₦{$formattedBalance}. Kindly make payment at your earliest convenience.";

            $channel = match (true) {
                $this->sms->send($student->phone, $message) => 'sms',
                $this->whatsapp->send($student->phone, config('services.twilio.whatsapp_templates.balance_reminder'), ['1' => $formattedBalance]) => 'whatsapp',
                default => null,
            };

            MessageLog::create([
                'recipient_type' => 'student',
                'recipient_id' => $student->id,
                'recipient_name' => $student->name,
                'recipient_phone' => $student->phone,
                'purpose' => 'balance_reminder',
                'channel' => $channel,
                'status' => $channel ? 'sent' : 'failed',
                'message' => $message,
            ]);

            return $channel !== null;
        })->count();

        $this->info("Balance reminder sent to {$sent} of {$students->count()} student(s).");

        return self::SUCCESS;
    }
}
