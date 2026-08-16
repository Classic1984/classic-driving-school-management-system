<?php

namespace App\Console\Commands;

use App\Models\MessageLog;
use App\Models\Student;
use App\Services\StudentChargeResolver;
use App\Services\TermiiSmsService;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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

            try {
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
            } catch (\Throwable $e) {
                // One student's reminder failing outright (an SMS/WhatsApp
                // API timeout, etc.) must not stop every student after them
                // in this run from being reminded.
                Log::error("Failed to send balance reminder to student #{$student->id}: {$e->getMessage()}", ['exception' => $e]);

                return false;
            }
        })->count();

        $this->info("Balance reminder sent to {$sent} of {$students->count()} student(s).");

        return self::SUCCESS;
    }
}
