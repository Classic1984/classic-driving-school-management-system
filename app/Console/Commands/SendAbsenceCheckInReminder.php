<?php

namespace App\Console\Commands;

use App\Models\MessageLog;
use App\Models\Student;
use App\Services\TermiiSmsService;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class SendAbsenceCheckInReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-absence-check-in-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Text every actively enrolled student who has gone 4+ days without a training session, checking in on them';

    /**
     * How many days without a training login before a student is
     * considered absent and gets a check-in message.
     */
    protected const ABSENCE_THRESHOLD_DAYS = 4;

    public function __construct(protected TermiiSmsService $sms, protected WhatsAppService $whatsapp)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cutoff = now()->subDays(self::ABSENCE_THRESHOLD_DAYS);

        $students = Student::whereHas('courses', fn ($query) => $query->where('course_student.status', 'active'))
            ->with(['attendances' => fn ($query) => $query->where('status', 'present')->latest('date')])
            ->get();

        $due = $students->filter(function (Student $student) use ($cutoff) {
            // A student's last training login, or - if they've never
            // trained yet - the day they enrolled. Whichever it is, going
            // 4+ days past it without a fresh training login is what
            // "absent" means here.
            $referenceDate = $student->attendances->first()?->date ?? $student->enrollment_date;

            if ($referenceDate === null || $referenceDate->gt($cutoff)) {
                return false;
            }

            // Only remind once per absence spell: a reminder already sent
            // after this reference date means nothing has changed since,
            // so skip it until a fresh training login moves the reference
            // date forward (or the student goes quiet again after that).
            return $student->last_absence_reminder_sent_at === null
                || $student->last_absence_reminder_sent_at->lt($referenceDate);
        });

        if ($due->isEmpty()) {
            $this->warn('No students are due for an absence check-in.');

            return self::SUCCESS;
        }

        $sent = $due->filter(function (Student $student) {
            $message = "Hi {$student->name}, we noticed you haven't been to training in a few days. Is everything okay? Let us know if you'd like to reschedule your next session.";

            $channel = match (true) {
                $this->sms->send($student->phone, $message) => 'sms',
                $this->whatsapp->send($student->phone, config('services.twilio.whatsapp_templates.absence_check_in'), ['1' => $student->name]) => 'whatsapp',
                default => null,
            };

            MessageLog::create([
                'recipient_type' => 'student',
                'recipient_id' => $student->id,
                'recipient_name' => $student->name,
                'recipient_phone' => $student->phone,
                'purpose' => 'absence_check_in',
                'channel' => $channel,
                'status' => $channel ? 'sent' : 'failed',
                'message' => $message,
            ]);

            if ($channel !== null) {
                $student->update(['last_absence_reminder_sent_at' => now()]);
            }

            return $channel !== null;
        })->count();

        $this->info("Absence check-in sent to {$sent} of {$due->count()} student(s).");

        return self::SUCCESS;
    }
}
