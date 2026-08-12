<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Services\TermiiSmsService;
use Illuminate\Console\Command;

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
    protected $description = "Text every actively enrolled student a reminder about today's theory class";

    public function __construct(protected TermiiSmsService $sms)
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

        $message = 'Classic Driving School: Reminder - theory class holds today (Thursday) at 10am. Please be punctual.';

        $sent = $students->filter(fn (Student $student) => $this->sms->send($student->phone, $message))->count();

        $this->info("Theory class reminder sent to {$sent} of {$students->count()} student(s).");

        return self::SUCCESS;
    }
}
