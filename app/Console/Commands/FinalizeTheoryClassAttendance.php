<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\TheoryClass;
use App\Models\TheoryClassAttendance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FinalizeTheoryClassAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:finalize-theory-class-attendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Mark every actively-enrolled student who never checked in to today's theory class as absent";

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $theoryClass = TheoryClass::whereDate('class_date', today())->first();

        // No class was created for today (cancelled, or the reminder
        // command never ran) - nothing to finalize.
        if ($theoryClass === null) {
            $this->info('No theory class scheduled for today - nothing to finalize.');

            return self::SUCCESS;
        }

        $expectedStudents = Student::whereHas('courses', fn ($query) => $query->where('course_student.status', 'active'))
            ->get(['id']);

        $created = 0;
        $failures = 0;

        foreach ($expectedStudents as $student) {
            try {
                $alreadyLogged = TheoryClassAttendance::where('theory_class_id', $theoryClass->id)
                    ->where('student_id', $student->id)
                    ->exists();

                if ($alreadyLogged) {
                    continue;
                }

                TheoryClassAttendance::create([
                    'theory_class_id' => $theoryClass->id,
                    'student_id' => $student->id,
                    'status' => 'absent',
                ]);

                $created++;
            } catch (\Throwable $e) {
                // One bad student record must not stop the rest of today's
                // roster from being finalized.
                $failures++;
                Log::error("Failed to finalize today's theory class attendance for student #{$student->id}: {$e->getMessage()}", ['exception' => $e]);
            }
        }

        $this->info("Marked {$created} student(s) absent for today's theory class.".($failures > 0 ? " ({$failures} failed, see logs)" : ''));

        return self::SUCCESS;
    }
}
