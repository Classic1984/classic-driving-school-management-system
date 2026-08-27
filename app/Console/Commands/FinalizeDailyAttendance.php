<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Enrollment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FinalizeDailyAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:finalize-daily-attendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Mark every actively-enrolled student expected in today, who never checked in, as absent for today's training day";

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = now();

        // The school is closed Sundays - nobody is expected, so there's
        // nothing to finalize.
        if ($today->isSunday()) {
            $this->info('School is closed on Sundays - nothing to finalize.');

            return self::SUCCESS;
        }

        // Courses only carry a coarse weekday/weekend schedule, not
        // specific days - weekend-schedule courses meet Saturdays only,
        // everything else is a weekday-schedule course meeting Mon-Fri.
        $scheduleToday = $today->isSaturday() ? 'weekend' : 'weekday';

        $expectedEnrollments = Enrollment::where('status', 'active')
            ->whereHas('course', fn ($query) => $query->where('schedule', $scheduleToday))
            ->with(['student', 'course'])
            ->get();

        $created = 0;
        $failures = 0;

        foreach ($expectedEnrollments as $enrollment) {
            try {
                $alreadyLogged = Attendance::where('student_id', $enrollment->student_id)
                    ->where('course_id', $enrollment->course_id)
                    ->whereDate('date', $today)
                    ->exists();

                if ($alreadyLogged) {
                    continue;
                }

                Attendance::create([
                    'student_id' => $enrollment->student_id,
                    'course_id' => $enrollment->course_id,
                    'date' => $today->toDateString(),
                    'status' => 'absent',
                ]);

                $created++;
            } catch (\Throwable $e) {
                // One bad enrollment record must not stop the rest of
                // today's roster from being finalized.
                $failures++;
                Log::error("Failed to finalize today's attendance for enrollment #{$enrollment->id}: {$e->getMessage()}", ['exception' => $e]);
            }
        }

        $this->info("Marked {$created} student(s) absent for today.".($failures > 0 ? " ({$failures} failed, see logs)" : ''));

        return self::SUCCESS;
    }
}
