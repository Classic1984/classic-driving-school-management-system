<?php

namespace App\Console\Commands;

use App\Models\MessageLog;
use App\Models\Student;
use App\Models\TheoryClassCancellation;
use App\Services\TermiiSmsService;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendTheoryClassReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-theory-class-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Text every actively enrolled student a reminder - or cancellation notice - about today's theory class";

    public function __construct(protected TermiiSmsService $sms, protected WhatsAppService $whatsapp)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $students = Student::whereHas('courses', fn ($query) => $query->where('course_student.status', 'active'))
            ->get(['id', 'name', 'phone']);

        if ($students->isEmpty()) {
            $this->warn('No actively enrolled students to remind.');

            return self::SUCCESS;
        }

        $cancellation = TheoryClassCancellation::whereDate('class_date', today())->first();

        if ($cancellation !== null) {
            $message = 'Classic Driving School: Notice - today\'s theory class (Thursday) has been CANCELLED.'
                .($cancellation->reason ? " Reason: {$cancellation->reason}." : '')
                .' We apologize for the inconvenience.';
            $label = 'cancellation notice';
            $templateKey = 'theory_class_cancellation';
            $variables = ['1' => $cancellation->reason ?: 'No reason given'];
        } else {
            $message = 'Classic Driving School: Reminder - theory class holds today (Thursday) at 10am. Please be punctual.';
            $label = 'reminder';
            $templateKey = 'theory_class_reminder';
            $variables = [];
        }

        $sent = $students->filter(function (Student $student) use ($message, $templateKey, $variables) {
            try {
                $channel = match (true) {
                    $this->sms->send($student->phone, $message) => 'sms',
                    $this->whatsapp->send($student->phone, config("services.twilio.whatsapp_templates.{$templateKey}"), $variables) => 'whatsapp',
                    default => null,
                };

                MessageLog::create([
                    'recipient_type' => 'student',
                    'recipient_id' => $student->id,
                    'recipient_name' => $student->name,
                    'recipient_phone' => $student->phone,
                    'purpose' => $templateKey,
                    'channel' => $channel,
                    'status' => $channel ? 'sent' : 'failed',
                    'message' => $message,
                ]);

                return $channel !== null;
            } catch (\Throwable $e) {
                // One student's reminder failing outright must not stop
                // every student after them in this run from being reminded.
                Log::error("Failed to send theory class reminder to student #{$student->id}: {$e->getMessage()}", ['exception' => $e]);

                return false;
            }
        })->count();

        $this->info("Theory class {$label} sent to {$sent} of {$students->count()} student(s).");

        return self::SUCCESS;
    }
}
