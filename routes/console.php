<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Ticks every minute purely so the app can tell whether the background
// scheduler process is actually alive in production (surfaced on the
// Activity Log page) - the same "schedule:work silently never started"
// failure mode that once broke backups could just as easily break every
// other scheduled job below without anything visibly erroring.
Schedule::command('app:scheduler-heartbeat')->everyMinute();

Schedule::command('app:refresh-enrollment-locks')->daily();
Schedule::command('backup:database')->twiceDaily(2, 14);
Schedule::command('app:send-theory-class-reminder')->weeklyOn(4, '08:00');
Schedule::command('app:send-balance-reminder')->weeklyOn(1, '09:00');
// Checked daily; SendLeadFollowUpReminder itself only texts a lead once at
// least 4 days have passed since they were logged (or last reminded).
Schedule::command('app:send-lead-follow-up-reminder')->dailyAt('10:00');
// Checked daily; SendAbsenceCheckInReminder itself only texts a student
// once they've gone 4+ days without a training login, and only once per
// absence spell.
Schedule::command('app:send-absence-check-in-reminder')->dailyAt('11:00');
